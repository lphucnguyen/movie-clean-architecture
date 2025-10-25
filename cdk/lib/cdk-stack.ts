import * as cdk from 'aws-cdk-lib';
import * as iam from 'aws-cdk-lib/aws-iam';
import * as codebuild from 'aws-cdk-lib/aws-codebuild';
import * as codepipeline from 'aws-cdk-lib/aws-codepipeline';
import * as codepipeline_actions from 'aws-cdk-lib/aws-codepipeline-actions';
import * as ecr from 'aws-cdk-lib/aws-ecr';
import * as ec2 from 'aws-cdk-lib/aws-ec2';
import * as ecs from 'aws-cdk-lib/aws-ecs';

import { Construct } from 'constructs';

export class CdkStack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    const ecrRepo = new ecr.Repository(this, 'laravel-ecr-repo');

    const clusterAdminIam = new iam.Role(this, 'adminrole', {
      assumedBy: new iam.AccountRootPrincipal()
    });

    // VPC
    const subnetPublic = {
      cidrMask: 24,
      name: 'public',
      subnetType: ec2.SubnetType.PUBLIC,
    };

    const subnetApplication = {
      cidrMask: 24,
      name: 'application',
      subnetType: ec2.SubnetType.PRIVATE_WITH_EGRESS,
    };

    const subnetDatabase = {
      cidrMask: 24,
      name: 'data',
      subnetType: ec2.SubnetType.PRIVATE_ISOLATED,
    };

    const vpc = new ec2.Vpc(this, 'ecs-cdk-vpc', {
        subnetConfiguration: [
          subnetPublic,
          subnetApplication,
          subnetDatabase,
        ],
        natGateways: 1,
        maxAzs: 3
    });

    // ECS
    const cluster = new ecs.Cluster(this, "ecs-cluster", {
      vpc: vpc
    });

    const logging = new ecs.AwsLogDriver({
        streamPrefix: "ecs-logs"
    });

    const taskRole = new iam.Role(this, `ecs-taskrole-${this.stackName}`, {
      roleName: `ecs-taskrole-${this.stackName}`,
      assumedBy: new iam.ServicePrincipal('ecs-tasks.amazonaws.com')
    });

    const executionRolePolicy = new iam.PolicyStatement({
      effect: iam.Effect.ALLOW,
      resources: ['*'],
      actions: [
        "ecr:GetAuthorizationToken",
        "ecr:BatchCheckLayerAvailability",
        "ecr:GetDownloadUrlForLayer",
        "ecr:BatchGetImage",
        "logs:CreateLogStream",
        "logs:PutLogEvents"
      ]
    });

    const taskDefinition = new ecs.FargateTaskDefinition(this, "ecs-taskdef", {
      taskRole: taskRole
    });

    taskDefinition.addToExecutionRolePolicy(executionRolePolicy);

    const baseImage = 'amazon/amazon-ecs-sample';
    const containerName = 'laravel-app';
    const container = taskDefinition.addContainer(containerName, {
      image: ecs.ContainerImage.fromRegistry(baseImage),
      memoryLimitMiB: 512,
      cpu: 256,
      logging,
    });

    container.addPortMappings({
      containerPort: 80,
      protocol: ecs.Protocol.TCP
    });

    const fargateService = new cdk.aws_ecs_patterns.ApplicationLoadBalancedFargateService(this, "ecs-service", {
      cluster: cluster,
      taskDefinition: taskDefinition,
      publicLoadBalancer: true,
      desiredCount: 1,
      listenerPort: 80,
      minHealthyPercent: 70,
    });

    const scaling = fargateService.service.autoScaleTaskCount({
      maxCapacity: 6,
    });

    scaling.scaleOnCpuUtilization('cpuscaling', {
      targetUtilizationPercent: 10,
      scaleInCooldown: cdk.Duration.seconds(60),
      scaleOutCooldown: cdk.Duration.seconds(60)
    });

    // CI/CD
    const {
      githubUserNameParam,
      githubRepositoryParam,
      githubPersonalTokenSecretNameParam,
    } = this.setupGithub();

    const branch = 'develop';
    const gitHubSource = codebuild.Source.gitHub({
      owner: githubUserNameParam.valueAsString,
      repo: githubRepositoryParam.valueAsString,
      webhook: true,
      webhookFilters: [
        codebuild.FilterGroup.inEventOf(codebuild.EventAction.PULL_REQUEST_MERGED).andBranchIs(branch),
      ],
    });

    const project = new codebuild.Project(this, 'myProject', {
      projectName: `${this.stackName}`,
      source: gitHubSource,
      environment: {
        buildImage: codebuild.LinuxBuildImage.STANDARD_7_0 , // Use Amazon Linux 2023 standard 5 for PHP 8.2 https://docs.amazonaws.cn/en_us/codebuild/latest/userguide/available-runtimes.html
        privileged: true,
      },
      environmentVariables: {
        cluster_name: {
          value: `${cluster.clusterName}`,
        },
        ecr_repo_uri: {
          value: `${ecrRepo.repositoryUri}`,
        },
        containerName: {
          value: `${containerName}`,
        },
        APP_NAME: {
          value: `${this.stackName}`,
          type: codebuild.BuildEnvironmentVariableType.SECRETS_MANAGER,
        }
      },
      badge: true,
      buildSpec: codebuild.BuildSpec.fromObject({
        "version": "0.2",
        "phases": {
          "install": {
            "runtime-versions": {
              "php": "8.2"
            }
          },
          "pre_build": {
            "commands": [
              "echo Logging in to Amazon ECR....",
              "aws --version",
              "aws ecr get-login-password --region ap-southeast-1 | docker login --username AWS --password-stdin $ecr_repo_uri",
              "export REPOSITORY_URI=$ecr_repo_uri",
              "export IMAGE_TAG=latest",
              "export CONTAINER_NAME=$containerName"
            ]
          },
          "build": {
            "commands": [
              "echo Build started on `date`",
              "echo Installing composer...",
              "composer install --no-dev --prefer-dist --no-progress --ignore-platform-req=ext-sodium",
              "echo Creating .env file...",
              "cp .env.example .env",
              "echo Generating app key...",
              "php artisan key:generate",
              "echo Building the Docker image...",
              "docker build -t $REPOSITORY_URI:$IMAGE_TAG .",
              "docker tag $REPOSITORY_URI:$IMAGE_TAG $REPOSITORY_URI:$IMAGE_TAG"
            ]
          },
          "post_build": {
            "commands": [
              "echo Build completed on `date`",
              "echo Pushing the Docker image...",
              "docker image ls -a",
              "docker push $REPOSITORY_URI:$IMAGE_TAG",
              "echo Creating imagedefinitions.json...",
              "printf '[{\"name\":\"%s\",\"imageUri\":\"%s\"}]' $CONTAINER_NAME $REPOSITORY_URI:$IMAGE_TAG > imagedefinitions.json"
            ]
          }
        },
        "artifacts": {
          "files": [
            "imagedefinitions.json"
          ]
        }
      }),
    });

    const sourceOutput = new codepipeline.Artifact();
    const buildOutput = new codepipeline.Artifact();
    const sourceAction = new codepipeline_actions.GitHubSourceAction({
      actionName: 'github_source',
      owner: githubUserNameParam.valueAsString,
      repo: githubRepositoryParam.valueAsString,
      branch: branch,
      oauthToken: cdk.SecretValue.secretsManager(githubPersonalTokenSecretNameParam.valueAsString),
      output: sourceOutput,
    });

    const buildAction = new codepipeline_actions.CodeBuildAction({
      actionName: 'codebuild',
      project: project,
      input: sourceOutput,
      outputs: [buildOutput],
    });

    const manualApprovalAction = new codepipeline_actions.ManualApprovalAction({
      actionName: 'approve',
    });

    const deployAction = new codepipeline_actions.EcsDeployAction({
      actionName: 'deployAction',
      service: fargateService.service,
      imageFile: new codepipeline.ArtifactPath(buildOutput, `imagedefinitions.json`),
    });

    new codepipeline.Pipeline(this, 'myecspipeline', {
      stages: [
        {
          stageName: 'source',
          actions: [sourceAction],
        },
        {
          stageName: 'build',
          actions: [buildAction],
        },
        {
          stageName: 'approve',
          actions: [manualApprovalAction],
        },
        {
          stageName: 'deploy-to-ecs',
          actions: [deployAction],
        },
      ],
    }).addToRolePolicy(new iam.PolicyStatement({
      actions: ["s3:GetObject", "s3:PutObject", "s3:ListBucket"],
      resources: [`arn:aws:s3:::*`, `arn:aws:s3:::*/*`]
    }));

    ecrRepo.grantPullPush(project.role!);
    project.addToRolePolicy(
      new iam.PolicyStatement({
        actions: [
          'ecs:DescribeCluster',
          'ecr:GetAuthorizationToken',
          'ecr:BatchCheckLayerAvailability',
          'ecr:BatchGetImage',
          'ecr:GetDownloadUrlForLayer',
        ],
        resources: [`${cluster.clusterArn}`],
      })
    );

    new cdk.CfnOutput(this, 'image', {
      value: ecrRepo.repositoryUri + ':latest',
    });
    new cdk.CfnOutput(this, 'loadbalancerdns', {
      value: fargateService.loadBalancer.loadBalancerDnsName,
    });
  }

  private setupGithub(): {
    githubUserNameParam: cdk.CfnParameter;
    githubRepositoryParam: cdk.CfnParameter;
    githubPersonalTokenSecretNameParam: cdk.CfnParameter;
  } {
    const githubUserNameParam = new cdk.CfnParameter(this, 'githubUserNameParam', {
      type: 'String',
      description: 'Github username for source code repository',
    });

    const githubRepositoryParam = new cdk.CfnParameter(this, 'githubRepositoryParam', {
      type: 'String',
      description: 'Github source code repository',
      default: 'amazon-ecs-fargate-cdk-v2-cicd',
    });

    const githubPersonalTokenSecretNameParam = new cdk.CfnParameter(
      this,
      'githubPersonalTokenSecretNameParam',
      {
        type: 'String',
        description: 'The name of the AWS Secrets Manager Secret which holds the GitHub Personal Access Token for this project.',
        default: 'github-personal-token',
      }
    );

    return {
      githubUserNameParam,
      githubRepositoryParam,
      githubPersonalTokenSecretNameParam,
    };
  }
}
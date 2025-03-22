#!/bin/bash
echo "AWS_CONTAINER_CREDENTIALS_RELATIVE_URI=$AWS_CONTAINER_CREDENTIALS_RELATIVE_URI" > /var/www/html/.env
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan migrate --seed --force

php artisan storage:link

/usr/local/bin/apache2-foreground
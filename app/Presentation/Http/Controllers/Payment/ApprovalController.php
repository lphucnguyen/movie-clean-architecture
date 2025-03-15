<?php

namespace App\Presentation\Http\Controllers\Payment;

use App\Application\Commands\Payment\ApprovalCommand;
use App\Application\DTOs\Payment\ApprovalPaymentDTO;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\Payment\ApprovalRequest;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Crypt;

class ApprovalController extends Controller
{
    public function __invoke(ApprovalRequest $request)
    {
        $request->validated();

        $decrypted_data = json_decode(Crypt::decryptString($request->encrypt_data), true);
        $decrypted_data = [
            ...$decrypted_data,
            'token' => $request->token,
        ];

        $dto = new ApprovalPaymentDTO($decrypted_data);

        $approvalCommand = new ApprovalCommand($dto);
        return Bus::dispatch($approvalCommand);
    }
}
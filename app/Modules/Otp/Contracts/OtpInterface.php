<?php

namespace App\Modules\Otp\Contracts;

use App\Modules\Otp\DTO\OtpResult;

interface OtpInterface
{
    public function startVerification(string $phoneNumber): OtpResult;

    public function checkVerification(string $id, string $code, string $phoneNumber): OtpResult;

    public function sendSms(string $phoneNumber, string $messageBody): OtpResult;
}

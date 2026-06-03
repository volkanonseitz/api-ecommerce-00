<?php

namespace App\Services\Otp;

interface OtpInterface
{
    public function startVerification(string $phoneNumber): Results;
    public function checkVerification(string $id, string $code, string $phoneNumber): Results;
    public function sendSms(string $phoneNumber, string $messageBody): Results;
}
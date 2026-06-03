<?php

namespace App\Services\Otp\Gateways;

use App\Services\Otp\OtpInterface;
use App\Services\Otp\Results;
use MessageBird\Client;
use MessageBird\Objects\Verify;

class MessagebirdGateway implements OtpInterface
{
    private Client $client;

    public function __construct()
    {
        $apiKey = config('services.messagebird.api_key');
        $this->client = new Client($apiKey);
    }

    public function startVerification(string $phoneNumber): Results
    {
        try {
            $verify = new Verify();
            $verify->originator = config('services.messagebird.originator');
            $verify->recipient = $phoneNumber;
            $result = $this->client->verify->create($verify);
            return new Results($result->getId());
        } catch (\Exception $e) {
            return new Results(["Verification failed: {$e->getMessage()}"]);
        }
    }

    public function checkVerification(string $id, string $code, string $phoneNumber): Results
    {
        try {
            $this->client->verify->verify($id, $code);
            return new Results('success');
        } catch (\Exception $e) {
            return new Results(["Verification check failed: {$e->getMessage()}"]);
        }
    }

    public function sendSms(string $phoneNumber, string $messageBody): Results
    {
        // implementasi sesuai kebutuhan
        return new Results('not_implemented');
    }
}
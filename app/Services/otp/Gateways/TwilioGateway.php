<?php

namespace App\Services\Otp\Gateways;

use App\Services\Otp\OtpInterface;
use App\Services\Otp\Results;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioGateway implements OtpInterface
{
    private Client $client;
    private string $verificationSid;

    public function __construct()
    {
        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $this->client = new Client($sid, $token);
        $this->verificationSid = config('services.twilio.verification_sid');
    }

    public function startVerification(string $phoneNumber): Results
    {
        try {
            $verification = $this->client->verify->v2->services($this->verificationSid)
                ->verifications->create($phoneNumber, 'sms');
            return new Results($verification->sid);
        } catch (TwilioException $e) {
            return new Results(["Verification failed: {$e->getMessage()}"]);
        }
    }

    public function checkVerification(string $id, string $code, string $phoneNumber): Results
    {
        try {
            $check = $this->client->verify->v2->services($this->verificationSid)
                ->verificationChecks->create(['to' => $phoneNumber, 'code' => $code]);
            if ($check->status === 'approved') {
                return new Results($check->sid);
            }
            return new Results(['Invalid code']);
        } catch (TwilioException $e) {
            return new Results(["Verification check failed: {$e->getMessage()}"]);
        }
    }

    public function sendSms(string $phoneNumber, string $messageBody): Results
    {
        try {
            $message = $this->client->messages->create(
                $phoneNumber,
                ['from' => config('services.twilio.from'), 'body' => $messageBody]
            );
            return new Results($message->sid);
        } catch (TwilioException $e) {
            return new Results(["SMS failed: {$e->getMessage()}"]);
        }
    }
}
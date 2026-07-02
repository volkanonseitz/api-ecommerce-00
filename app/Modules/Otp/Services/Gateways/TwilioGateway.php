<?php

// declare(strict_types=1);

// namespace App\Modules\Otp\Services\Gateways;

// use App\Modules\Otp\Contracts\OtpInterface;
// use App\Modules\Otp\DTO\OtpResult;
// use Twilio\Exceptions\TwilioException;
// use Twilio\Rest\Client;

// class TwilioGateway implements OtpInterface
// {
//     private Client $client;

//     private string $verificationSid;

//     public function __construct()
//     {
//         $sid = config('services.twilio.account_sid');
//         $token = config('services.twilio.auth_token');
//         $this->client = new Client($sid, $token);
//         $this->verificationSid = config('services.twilio.verification_sid');
//     }

//     public function startVerification(string $phoneNumber): OtpResult
//     {
//         try {
//             $verification = $this->client->verify->v2->services($this->verificationSid)
//                 ->verifications->create($phoneNumber, 'sms');

//             return new OtpResult($verification->sid);
//         } catch (TwilioException $e) {
//             return new OtpResult(["Verification failed: {$e->getMessage()}"]);
//         }
//     }

//     public function checkVerification(string $id, string $code, string $phoneNumber): OtpResult
//     {
//         try {
//             $check = $this->client->verify->v2->services($this->verificationSid)
//                 ->verificationChecks->create(['to' => $phoneNumber, 'code' => $code]);
//             if ($check->status === 'approved') {
//                 return new OtpResult($check->sid);
//             }

//             return new OtpResult(['Invalid code']);
//         } catch (TwilioException $e) {
//             return new OtpResult(["Verification check failed: {$e->getMessage()}"]);
//         }
//     }

//     public function sendSms(string $phoneNumber, string $messageBody): OtpResult
//     {
//         try {
//             $message = $this->client->messages->create(
//                 $phoneNumber,
//                 ['from' => config('services.twilio.from'), 'body' => $messageBody]
//             );

//             return new OtpResult($message->sid);
//         } catch (TwilioException $e) {
//             return new OtpResult(["SMS failed: {$e->getMessage()}"]);
//         }
//     }
// }

<?php

// declare(strict_types=1);

// namespace App\Modules\Otp\Services\Gateways;

// use App\Modules\Otp\Contracts\OtpInterface;
// use App\Modules\Otp\DTO\OtpResult;
// use MessageBird\Client;
// use MessageBird\Objects\Verify;

// class MessagebirdGateway implements OtpInterface
// {
//     private Client $client;

//     public function __construct()
//     {
//         $apiKey = config('services.messagebird.api_key');
//         $this->client = new Client($apiKey);
//     }

//     public function startVerification(string $phoneNumber): OtpResult
//     {
//         try {
//             $verify = new Verify;
//             $verify->originator = config('services.messagebird.originator');
//             $verify->recipient = $phoneNumber;
//             $result = $this->client->verify->create($verify);

//             return new OtpResult($result->getId());
//         } catch (\Exception $e) {
//             return new OtpResult(["Verification failed: {$e->getMessage()}"]);
//         }
//     }

//     public function checkVerification(string $id, string $code, string $phoneNumber): OtpResult
//     {
//         try {
//             $this->client->verify->verify($id, $code);

//             return new OtpResult('success');
//         } catch (\Exception $e) {
//             return new OtpResult(["Verification check failed: {$e->getMessage()}"]);
//         }
//     }

//     public function sendSms(string $phoneNumber, string $messageBody): OtpResult
//     {
//         // implementasi sesuai kebutuhan
//         return new OtpResult('not_implemented');
//     }
// }

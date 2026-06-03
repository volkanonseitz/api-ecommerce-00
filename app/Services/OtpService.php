<?php

namespace App\Services;

use App\Services\Otp\OtpInterface;
use App\Services\Otp\Results;
use Illuminate\Support\Facades\Config;

class OtpService
{
    // private OtpInterface $gateway; //nonaktifkan sementara untuk tujuan pengecekan
    private ?OtpInterface $gateway = null; // hapus atau nonaktifkan untuk menjalankan kembali gateway

    public function __construct()
    {
        // $gatewayName = Config::get('auth.active_otp_gateway', 'twilio');
        // $gatewayClass = "App\\Services\\Otp\\Gateways\\" . ucfirst($gatewayName) . "Gateway";
        // $this->gateway = new $gatewayClass();

        $gatewayName = Config::get('auth.active_otp_gateway', 'twilio');
        $gatewayClass = 'App\\Services\\Otp\\Gateways\\'.ucfirst($gatewayName).'Gateway';

        if (class_exists($gatewayClass)) {
            $this->gateway = new $gatewayClass;
        }
    }

    // public function startVerification(string $phoneNumber): Results
    // {
    //     return $this->gateway->startVerification($phoneNumber);
    // }

    public function startVerification(string $phoneNumber): Results
    {
        if (! $this->gateway) {
            throw new \Exception('OTP disabled');
        }

        return $this->gateway->startVerification($phoneNumber);
    }

    // public function checkVerification(string $id, string $code, string $phoneNumber): Results
    // {
    //     return $this->gateway->checkVerification($id, $code, $phoneNumber);
    // }

    public function checkVerification(string $id, string $code, string $phoneNumber): Results
    {
        if (! $this->gateway) {
            throw new \Exception('OTP disabled');
        }

        return $this->gateway->checkVerification($id, $code, $phoneNumber);
    }

    // public function sendSms(string $phoneNumber, string $message): Results
    // {
    //     return $this->gateway->sendSms($phoneNumber, $message);
    // }

    public function sendSms(string $phoneNumber, string $message): Results
    {
        if (! $this->gateway) {
            throw new \Exception('OTP disabled');
        }

        return $this->gateway->sendSms($phoneNumber, $message);
    }
}

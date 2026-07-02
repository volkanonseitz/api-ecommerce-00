<?php

namespace App\Modules\Otp\DTO;

use InvalidArgumentException;

final class OtpResult
{
    private bool $valid;

    private array $errors;

    private string $id;

    public function __construct($value)
    {
        if (is_string($value)) {
            $this->id = $value;
            $this->valid = true;
            $this->errors = []; // Initialize errors
        } elseif (is_array($value)) {
            $this->errors = $value;
            $this->valid = false;
            $this->id = ''; // Initialize id
        } else {
            throw new InvalidArgumentException('Invalid argument: Only string or array allowed.');
        }
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

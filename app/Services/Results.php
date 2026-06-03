<?php

namespace App\Services\Otp;

use InvalidArgumentException;

class Results
{
    private bool $valid;
    private array $errors;
    private string $id;

    public function __construct($value)
    {
        if (is_string($value)) {
            $this->id = $value;
            $this->valid = true;
        } elseif (is_array($value)) {
            $this->errors = $value;
            $this->valid = false;
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
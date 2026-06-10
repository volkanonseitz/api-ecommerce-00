<?php

namespace App\DTO;

class CommissionData
{
    public function __construct(
        public readonly float $min_balance,
        public readonly float $max_balance,
        public readonly float $commission,
        public readonly string $level,
        public readonly string $sub_level,
        public readonly ?string $language,
    ) {}

    public static function fromArray(array $data, ?string $language = null): self
    {
        return new self(
            min_balance: $data['min_balance'],
            max_balance: $data['max_balance'],
            commission: $data['commission'],
            level: $data['level'],
            sub_level: $data['sub_level'],
            language: $language,
        );
    }
}
<?php

namespace App\DTO;

class UserData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?array $profile = null,
        public readonly ?array $address = null,
        public readonly ?int $shop_id = null,
        public readonly ?string $permission = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            profile: $data['profile'] ?? null,
            address: $data['address'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            permission: $data['permission'] ?? null,
        );
    }
}
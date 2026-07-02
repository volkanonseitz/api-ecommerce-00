<?php

declare(strict_types=1);

namespace App\Modules\Shop\DTO;

final class StaffData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly int $shop_id,
    ) {}

    /**
     * @param  array<string, mixed>  $data  hasil dari FormRequest::validated()
     */
    public static function fromValidated(array $data, int $shopId): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            shop_id: $shopId,
        );
    }
}

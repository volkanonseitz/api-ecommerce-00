<?php

declare(strict_types=1);

namespace App\Modules\User\DTO;

/**
 * DTO untuk proses UPDATE profil user.
 * `shopId` SENGAJA tidak pernah diisi dari request customer biasa
 * (lihat UserUpdateRequest) — hanya boleh diisi lewat alur admin
 * yang punya kelas DTO/Action sendiri (lihat AssignShopToUserAction
 * jika dibutuhkan), untuk mencegah Mass Assignment shop_id oleh client.
 */
final readonly class UpdateUserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?array $profile = null,
        public ?array $address = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            profile: $validated['profile'] ?? null,
            address: $validated['address'] ?? null,
        );
    }
}

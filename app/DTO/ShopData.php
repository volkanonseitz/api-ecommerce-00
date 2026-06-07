<?php

namespace App\DTO;

class ShopData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?array $cover_image,
        public readonly ?array $logo,
        public readonly ?bool $is_active,
        public readonly ?array $address,
        public readonly ?array $settings,
        public readonly ?array $notifications,
        public readonly ?array $categories,
        public readonly ?array $balance,
        public readonly ?int $owner_id,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            cover_image: $data['cover_image'] ?? null,
            logo: $data['logo'] ?? null,
            is_active: isset($data['is_active']) ? (bool)$data['is_active'] : null,
            address: $data['address'] ?? null,
            settings: $data['settings'] ?? null,
            notifications: $data['notifications'] ?? null,
            categories: $data['categories'] ?? null,
            balance: $data['balance'] ?? null,
            owner_id: $data['owner_id'] ?? null,
        );
    }

    /**
     * Mengembalikan instance baru dengan owner_id yang ditentukan.
     */
    public function withOwnerId(int $ownerId): self
    {
        return new self(
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            cover_image: $this->cover_image,
            logo: $this->logo,
            is_active: $this->is_active,
            address: $this->address,
            settings: $this->settings,
            notifications: $this->notifications,
            categories: $this->categories,
            balance: $this->balance,
            owner_id: $ownerId,
        );
    }
}
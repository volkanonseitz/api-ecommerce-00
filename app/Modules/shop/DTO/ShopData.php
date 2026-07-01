<?php

declare(strict_types=1);

namespace App\Modules\Shop\DTO;

/**
 * SECURITY FIX: DTO lama (App\DTO\ShopData) membawa properti finansial
 * (balance, total_earnings, dst) langsung dari request. DTO baru ini SENGAJA
 * tidak punya properti finansial/administratif sama sekali — field tersebut
 * hanya ada di ApproveShopData yang hanya bisa dipakai lewat jalur SUPER_ADMIN.
 */
final class ShopData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?array $cover_image = null,
        public readonly ?array $logo = null,
        public readonly ?array $address = null,
        public readonly ?array $settings = null,
        public readonly ?array $notifications = null,
        /** @var list<int>|null */
        public readonly ?array $categories = null,
        public readonly ?int $owner_id = null,
    ) {}

    /**
     * @param array<string, mixed> $data hasil dari FormRequest::validated()
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            cover_image: $data['cover_image'] ?? null,
            logo: $data['logo'] ?? null,
            address: $data['address'] ?? null,
            settings: $data['settings'] ?? null,
            notifications: $data['notifications'] ?? null,
            categories: $data['categories'] ?? null,
        );
    }

    public function withOwnerId(int $ownerId): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            cover_image: $this->cover_image,
            logo: $this->logo,
            address: $this->address,
            settings: $this->settings,
            notifications: $this->notifications,
            categories: $this->categories,
            owner_id: $ownerId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'logo' => $this->logo,
            'address' => $this->address,
            'settings' => $this->settings,
            'notifications' => $this->notifications,
            'owner_id' => $this->owner_id,
        ], fn (mixed $v): bool => ! is_null($v));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Tax\DTO;

final class TaxData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $country,
        public readonly ?string $state,
        public readonly ?string $zip,
        public readonly ?string $city,
        public readonly ?float $rate,
        public readonly ?bool $is_global,
        public readonly ?int $priority,
        public readonly ?bool $on_shipping,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            country: $data['country'] ?? null,
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            rate: $data['rate'] ?? null,
            is_global: $data['is_global'] ?? false,
            priority: $data['priority'] ?? null,
            on_shipping: $data['on_shipping'] ?? false,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'country' => $this->country,
            'state' => $this->state,
            'zip' => $this->zip,
            'city' => $this->city,
            'rate' => $this->rate,
            'is_global' => $this->is_global,
            'priority' => $this->priority,
            'on_shipping' => $this->on_shipping,
        ], fn ($v) => ! is_null($v));
    }
}

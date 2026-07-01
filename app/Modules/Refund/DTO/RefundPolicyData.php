<?php

declare(strict_types=1);

namespace App\Modules\Refund\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefundPolicyData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly string $target,
        public readonly string $status,
        public readonly ?string $description,
        public readonly ?int $shopId,
        public readonly string $language,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->input('title'),
            slug: Str::slug($request->input('title')),
            target: $request->input('target'),
            status: $request->input('status'),
            description: $request->input('description'),
            shopId: $request->input('shop_id'),
            language: $request->input('language', config('shop.default_language', 'id')),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'target' => $this->target,
            'status' => $this->status,
            'description' => $this->description,
            'shop_id' => $this->shopId,
            'language' => $this->language,
        ];
    }
}

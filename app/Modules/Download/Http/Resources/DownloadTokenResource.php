<?php

declare(strict_types=1);

namespace App\Modules\Download\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DownloadTokenResource extends JsonResource
{
    /**
     * @param  string  $resource  The URL token string
     */
    public function __construct(string $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->resource,
        ];
    }
}

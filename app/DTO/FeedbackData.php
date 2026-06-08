<?php

namespace App\DTO;

class FeedbackData
{
    public function __construct(
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly ?bool $positive,
        public readonly ?bool $negative,
        public readonly ?int $user_id,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            model_id: $data['model_id'],
            model_type: $data['model_type'],
            positive: $data['positive'] ?? null,
            negative: $data['negative'] ?? null,
            user_id: $userId,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model_id' => $this->model_id,
            'model_type' => $this->model_type,
            'positive' => $this->positive,
            'negative' => $this->negative,
            'user_id' => $this->user_id,
        ], fn($v) => !is_null($v));
    }
}
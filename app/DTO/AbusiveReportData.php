<?php

namespace App\DTO;

use App\Enums\AbusiveReportType;

class AbusiveReportData
{
    public function __construct(
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly string $message,
        public readonly int $user_id,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            model_id: $data['model_id'],
            model_type: $data['model_type'],
            message: $data['message'],
            user_id: $userId,
        );
    }

    public function getModelClass(): string
    {
        return AbusiveReportType::from($this->model_type)
            ->modelClass();
    }

    public function toArray(): array
    {
        return [
            'model_id' => $this->model_id,
            'model_type' => $this->getModelClass(),
            'message' => $this->message,
            'user_id' => $this->user_id,
        ];
    }
}

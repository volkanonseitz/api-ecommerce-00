<?php

namespace App\DTO;

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

    public function toArray(): array
    {
        return [
            'model_id' => $this->model_id,
            'model_type' => $this->getModelClass(),
            'message' => $this->message,
            'user_id' => $this->user_id,
        ];
    }

    public function getModelClass(): string
    {
        // mapping dari string "Review" ke App\Models\Review
        $map = [
            'Review' => \App\Models\Review::class,
            'Question' => \App\Models\Question::class,
            // tambah lain jika perlu
        ];
        return $map[$this->model_type] ?? 'App\\Models\\' . $this->model_type;
    }
}
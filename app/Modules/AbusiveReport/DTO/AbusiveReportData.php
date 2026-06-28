<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\DTO;

use App\Modules\AbusiveReport\Enums\AbusiveReportType;

final readonly class AbusiveReportData
{
    public function __construct(
        public int $model_id,
        public string $model_type,
        public string $message,
        public int $user_id,
    ) {}

    /**
     * @param  array{model_id:int, model_type:string, message:string}  $data
     */
    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            model_id: $data['model_id'],
            model_type: $data['model_type'],
            message: $data['message'],
            user_id: $userId,
        );
    }

    /**
     * @return class-string
     */
    public function getModelClass(): string
    {
        return AbusiveReportType::from($this->model_type)->modelClass();
    }

    /**
     * @return array{model_id:int, model_type:class-string, message:string, user_id:int}
     */
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

<?php

namespace App\DTO;

class QuestionData
{
    public function __construct(
        public readonly ?int $product_id,
        public readonly ?int $shop_id,
        public readonly ?int $user_id,
        public readonly ?string $question,
        public readonly ?string $answer,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            product_id: $data['product_id'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            user_id: $userId,
            question: $data['question'] ?? null,
            answer: $data['answer'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product_id' => $this->product_id,
            'shop_id' => $this->shop_id,
            'user_id' => $this->user_id,
            'question' => $this->question,
            'answer' => $this->answer,
        ], fn($v) => !is_null($v));
    }
}
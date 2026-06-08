<?php

namespace App\Services;

use App\Enums\AiType;
use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\OpenAiProvider;

class AiService
{
    protected AiProviderInterface $provider;

    public function __construct()
    {
        // Bisa dikembangkan untuk memilih provider berdasarkan config, default OpenAI
        $this->provider = $this->resolveProvider();
    }

    protected function resolveProvider(): AiProviderInterface
    {
        $type = config('ai.default_provider', AiType::OPENAI->value);
        return match ($type) {
            AiType::OPENAI->value => new OpenAiProvider(),
            default => new OpenAiProvider(),
        };
    }

    public function generateDescription(object $request): mixed
    {
        return $this->provider->generateDescription($request);
    }
}
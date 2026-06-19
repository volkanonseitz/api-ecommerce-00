<?php

namespace App\Services\Ai;

use App\Enums\AiType;

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
            AiType::OPENAI->value => new OpenAiProvider,
            default => new OpenAiProvider,
        };
    }

    public function generateDescription(object $request): mixed
    {
        return $this->provider->generateDescription($request);
    }
}

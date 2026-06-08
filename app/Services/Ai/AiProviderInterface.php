<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    public function generateDescription(object $request): mixed;
}
<?php

namespace App\Services\Ai;

use App\Models\Settings;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseAiProvider
{
    protected bool $enableAi;

    public function __construct()
    {
        $settings = Settings::first();
        $this->enableAi = $settings->options['useAi'] ?? false;
        if (!$this->enableAi) {
            throw new HttpException(400, config('notice.PLEASE_ENABLE_OPENAI_FROM_THE_SETTINGS'));
        }
    }
}
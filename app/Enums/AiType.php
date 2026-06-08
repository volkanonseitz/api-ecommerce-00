<?php

namespace App\Enums;

enum AiType: string
{
    case OPENAI = 'openai';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
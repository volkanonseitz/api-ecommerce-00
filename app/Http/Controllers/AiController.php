<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Http\Requests\AiDescriptionRequest;

class AiController extends Controller
{
    public function __construct(private AiService $aiService) {}

    public function generateDescription(AiDescriptionRequest $request): mixed
    {
        return $this->aiService->generateDescription($request);
    }
}
<?php

namespace App\Services\Ai;

use Exception;
use OpenAI as OpenAIClient;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OpenAiProvider extends BaseAiProvider implements AiProviderInterface
{
    private $openAiClient;

    public function __construct()
    {
        parent::__construct();
        $apiKey = config('services.openai.secret_key') ?? config('shop.openai.secret_Key');
        $this->openAiClient = OpenAIClient::client($apiKey);
    }

    public function generateDescription(object $request): mixed
    {
        try {
            $response = $this->openAiClient->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $request->prompt,
                    ],
                ],
            ]);

            $result = null;
            foreach ($response->choices as $choice) {
                $result = $choice->message->content;
                break;
            }

            return ['status' => 'success', 'result' => $result];
        } catch (Exception $e) {
            throw new HttpException(400, config('notice.SOMETHING_WENT_WRONG'));
        }
    }
}
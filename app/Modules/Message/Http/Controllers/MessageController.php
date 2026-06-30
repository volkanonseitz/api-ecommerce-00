<?php

declare(strict_types=1);

namespace App\Modules\Message\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Conversation;
use App\Models\Message;
use App\Modules\Message\DTO\MessageData;
use App\Modules\Message\Http\Requests\MessageCreateRequest;
use App\Modules\Message\Http\Resources\MessageResource;
use App\Modules\Message\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    public function __construct(private MessageService $messageService) {}

    /**
     * GET /conversations/{conversation_id}/messages
     */
    public function index(Request $request, int $conversation_id)
    {
        $user = $request->user();
        $conversation = $this->messageService->getConversationForUser($conversation_id, $user);
        $this->authorize('viewAny', [Message::class, $conversation]);

        $limit = (int) ($request->limit ?? 15);
        $messages = $this->messageService->getMessages($conversation, $limit)->paginate($limit);

        return MessageResource::collection($messages);
    }

    /**
     * POST /conversations/{conversation_id}/messages
     */
    public function store(MessageCreateRequest $request, int $conversation_id)
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($conversation_id);
        $this->authorize('create', [Message::class, $conversation]);

        $data = MessageData::fromRequest($request->validated(), $conversation_id, $user->id);
        $message = $this->messageService->storeMessage($conversation, $data, $user);

        return new MessageResource($message);
    }

    /**
     * PUT /conversations/{conversation_id}/seen
     */
    public function seenMessage(Request $request)
    {
        $request->validate(['conversation_id' => 'required|exists:conversations,id']);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $this->authorize('markAsSeen', [Message::class, $conversation]);

        $updated = $this->messageService->markAsSeen($conversation, $request->user());

        return response()->json(['updated' => $updated]);
    }
}

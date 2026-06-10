<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Http\Requests\MessageCreateRequest;
use App\Http\Resources\MessageResource;
use App\DTO\MessageData;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $messageService) {}

    /**
     * GET /conversations/{conversation_id}/messages
     */
    public function index(Request $request, $conversation_id)
    {
        $user = $request->user();
        $conversation = $this->messageService->getConversationForUser($conversation_id, $user);
        $limit = $request->limit ?? 15;
        $messages = $this->messageService->getMessages($conversation, $limit)->paginate($limit);
        return MessageResource::collection($messages);
    }

    /**
     * POST /conversations/{conversation_id}/messages
     */
    public function store(MessageCreateRequest $request, $conversation_id)
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($conversation_id);
        // Cek akses
        if (!$this->messageService->hasAccess($user, $conversation)) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }
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
        $updated = $this->messageService->markAsSeen($conversation, $request->user());
        return response()->json(['updated' => $updated]);
    }
}
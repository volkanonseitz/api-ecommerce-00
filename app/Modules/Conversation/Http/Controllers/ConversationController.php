<?php

declare(strict_types=1);

namespace App\Modules\Conversation\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Conversation;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Conversation\Actions\CreateConversationAction;
use App\Modules\Conversation\Http\Requests\ConversationCreateRequest;
use App\Modules\Conversation\Http\Resources\ConversationResource;
use App\Modules\Conversation\Services\ConversationQueryService;
use Illuminate\Http\Request;

class ConversationController extends BaseController
{
    public function __construct(
        private readonly ConversationQueryService $conversationQueryService,
        private readonly CreateConversationAction $createConversationAction
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Conversation::class);

        $user = $request->user();
        $limit = (int) ($request->limit ?? 15);

        $conversations = $this->conversationQueryService->getUserConversations($user)->paginate($limit);

        return ConversationResource::collection($conversations);
    }

    public function show(Request $request, int $conversation_id)
    {
        $user = $request->user();
        $conversation = $this->conversationQueryService->findConversation($conversation_id, $user);
        $this->authorize('view', $conversation);

        return new ConversationResource($conversation);
    }

    public function store(ConversationCreateRequest $request)
    {
        $user = $request->user();
        $this->authorize('create', [Conversation::class, $request->shop_id]);

        // Cek apakah user mencoba chat dengan tokonya sendiri
        $shop = Shop::findOrFail($request->shop_id);
        if ($shop->owner_id === $user->id || ($user->shop_id && $user->shop_id === $shop->id)) {
            throw new \Exception(config('notice.YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP'));
        }

        $conversation = $this->createConversationAction->execute($user->id, $request->shop_id);

        return new ConversationResource($conversation);
    }
}

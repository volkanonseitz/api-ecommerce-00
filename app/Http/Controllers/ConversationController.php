<?php

namespace App\Http\Controllers;

use App\Services\ConversationService;
use App\Http\Requests\ConversationCreateRequest;
use App\Models\Shop;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private ConversationService $service) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $conversations = $this->service->getUserConversations()->paginate($limit);
        return response()->json($conversations);
    }

    public function show($conversation_id)
    {
        $conversation = $this->service->findConversation($conversation_id);
        return response()->json($conversation);
    }

    public function store(ConversationCreateRequest $request)
    {
        $user = $request->user();
        if (!$user) abort(401, config('notice.NOT_AUTHORIZED'));

        $shop = Shop::findOrFail($request->shop_id);
        if ($shop->owner_id === $user->id || $request->shop_id === $user->shop_id) {
            throw new \Exception(config('notice.YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP'));
        }

        $conversation = $this->service->createConversation($user->id, $request->shop_id);
        return response()->json($conversation);
    }
}
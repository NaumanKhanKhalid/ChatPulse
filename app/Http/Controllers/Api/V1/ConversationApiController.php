<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationApiController extends Controller
{
    public function __construct(private ConversationService $service) {}

    public function index(): JsonResponse
    {
        $conversations = $this->service->getUserConversations(auth()->user());
        return response()->json(['data' => $conversations]);
    }

    public function startDirect(Request $request): JsonResponse
    {
        $request->validate(['user_id' => ['required','integer','exists:users,id']]);
        $other = User::findOrFail($request->user_id);
        $conversation = $this->service->getOrCreateDirect(auth()->user(), $other);
        return response()->json(['data' => $conversation], 201);
    }
}

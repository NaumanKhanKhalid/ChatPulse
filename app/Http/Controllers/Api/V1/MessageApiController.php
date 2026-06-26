<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    public function __construct(private MessageService $service) {}

    public function index(Conversation $conversation): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }
        $messages = $conversation->messages()
            ->with(['user', 'attachments', 'reactions.user'])
            ->orderByDesc('created_at')
            ->paginate(50);
        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }
        $request->validate(['body' => ['required','string','max:10000']]);
        $message = $this->service->send($conversation, auth()->user(), $request->all());
        return response()->json(['data' => $message->load(['user','attachments'])], 201);
    }
}

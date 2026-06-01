<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;

class GroupApiController extends Controller
{
    public function public(): JsonResponse
    {
        $groups = Conversation::where('type','group')->where('is_private',false)
            ->withCount('participants')->orderByDesc('last_activity_at')->paginate(20);
        return response()->json($groups);
    }

    public function join(Conversation $conversation, GroupService $service): JsonResponse
    {
        $service->joinPublicGroup($conversation, auth()->user());
        return response()->json(['success' => true]);
    }
}

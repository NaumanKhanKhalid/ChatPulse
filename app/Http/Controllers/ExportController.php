<?php
namespace App\Http\Controllers;

use App\Http\Requests\ExportChatRequest;
use App\Jobs\ExportChatJob;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function store(ExportChatRequest $request, Conversation $conversation): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        ExportChatJob::dispatch(
            $conversation,
            auth()->id(),
            $request->format,
            $request->from,
            $request->to
        );

        return response()->json(['message' => 'Export started. You will be notified when ready.']);
    }

    public function download(Request $request): mixed
    {
        $path = base64_decode($request->path);
        if (!Storage::exists($path)) abort(404);

        // Verify path is within exports directory
        if (!str_starts_with($path, 'exports/')) abort(403);

        return Storage::download($path);
    }
}

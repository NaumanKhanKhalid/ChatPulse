<?php
namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\FileUploadService;
use App\Services\MessageService;
use App\Jobs\ProcessFileUploadJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private FileUploadService $fileUploadService
    ) {}

    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        if (!$conversation->participants()->where('user_id', auth()->id())->exists()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $user = auth()->user();
        $data = $request->validated();

        $message = $this->messageService->send($conversation, $user, $data);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachment = $this->fileUploadService->store($file, $message);
                ProcessFileUploadJob::dispatch($attachment);
            }
            $message->refresh();
        }

        $message->load(['user', 'attachments', 'reactions.user', 'parent.user']);
        return response()->json(['message' => $this->formatMessage($message)], 201);
    }

    public function update(Request $request, Message $message): JsonResponse
    {
        if ($message->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $request->validate(['body' => ['required','string','max:10000']]);
        $updated = $this->messageService->edit($message, $request->body);
        return response()->json(['message' => $updated]);
    }

    public function destroy(Message $message): JsonResponse
    {
        $user = auth()->user();
        if ($message->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        $this->messageService->delete($message);
        return response()->json(['success' => true]);
    }

    public function forward(Request $request, Message $message): JsonResponse
    {
        $request->validate(['conversation_ids' => ['required','array','min:1']]);
        if ($message->trashed()) return response()->json(['error' => 'Cannot forward deleted message.'], 422);
        $forwarded = $this->messageService->forward($message, auth()->user(), $request->conversation_ids);
        return response()->json(['count' => count($forwarded)]);
    }

    private function formatMessage(Message $m): array
    {
        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'user_id' => $m->user_id,
            'body' => $m->body,
            'type' => $m->type,
            'parent_id' => $m->parent_id,
            'is_edited' => $m->is_edited,
            'is_scheduled' => $m->is_scheduled,
            'scheduled_at' => $m->scheduled_at?->toISOString(),
            'sent_at' => $m->sent_at?->toISOString(),
            'created_at' => $m->created_at->toISOString(),
            'user' => $m->user ? ['id'=>$m->user->id,'name'=>$m->user->name,'avatar_url'=>$m->user->avatar_url,'is_guest'=>$m->user->is_guest] : null,
            'attachments' => $m->attachments->map(fn($a) => ['id'=>$a->id,'original_name'=>$a->original_name,'url'=>$a->url,'file_type'=>$a->file_type,'formatted_size'=>$a->formatted_size])->toArray(),
            'reactions' => [],
            'parent' => $m->parent ? ['id'=>$m->parent->id,'body'=>$m->parent->body,'user'=>['name'=>$m->parent->user?->name]] : null,
        ];
    }
}

<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->orderByDesc('created_at')->paginate(30);
        return view('notifications.index', compact('notifications'));
    }

    public function fetch(): JsonResponse
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn($n) => [
                'id'      => $n->id,
                'type'    => $n->type,
                'title'   => $n->title,
                'body'    => $n->body,
                'data'    => $n->data,
                'unread'  => !$n->isRead(),
                'time'    => $n->created_at->diffForHumans(short: true),
            ]);
        $unread = auth()->user()->notifications()->whereNull('read_at')->count();
        return response()->json(['notifications' => $notifications, 'unread' => $unread]);
    }

    public function markAllRead(NotificationService $service): JsonResponse
    {
        $service->markAllRead(auth()->user());
        return response()->json(['success' => true]);
    }

    public function markRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== auth()->id()) return response()->json(['error'=>'Unauthorized.'],403);
        $notification->markRead();
        return response()->json(['success' => true]);
    }
}

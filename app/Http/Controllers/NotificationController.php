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

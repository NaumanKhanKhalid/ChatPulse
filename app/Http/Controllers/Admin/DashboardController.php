<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'guests' => User::where('is_guest', true)->count(),
            'conversations' => Conversation::count(),
            'groups' => Conversation::where('type', 'group')->count(),
            'messages_total' => Message::count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'online_users' => User::where('is_online', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
        ];

        // Messages per day — last 7 days for the mini chart
        $days = collect(range(6, 0))->map(function ($i) {
            $d = today()->subDays($i);
            return [
                'label' => $d->format('D'),
                'count' => Message::whereDate('created_at', $d)->count(),
            ];
        });

        // Hourly breakdown — last 24 hours
        $hours = collect(range(23, 0))->map(function ($i) {
            $h = now()->subHours($i)->startOfHour();
            return [
                'label' => $h->format('ga'),
                'count' => Message::whereBetween('created_at', [$h, $h->copy()->endOfHour()])->count(),
            ];
        });

        $recentUsers = User::latest()->limit(8)->get();
        $recentMessages = Message::with('user', 'conversation')->latest()->limit(8)->get();

        // Online users for the live presence widget (Echo presence channel updates this live)
        $onlineUsers = User::where('is_online', true)->limit(30)->get()
            ->map(fn($u) => [
                'id' => $u->id, 'name' => $u->name,
                'grad' => $u->avatarGradient(),
                'initials' => collect(explode(' ', $u->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
            ])->values();

        return view('admin.dashboard', compact('stats', 'days', 'hours', 'recentUsers', 'recentMessages', 'onlineUsers'));
    }
}

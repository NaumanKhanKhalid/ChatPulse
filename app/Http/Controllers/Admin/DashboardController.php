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
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'online_users' => User::where('is_online', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
        ];
        $recentUsers = User::latest()->limit(10)->get();
        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}

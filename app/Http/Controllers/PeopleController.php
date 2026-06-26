<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PeopleController extends Controller
{
    public function index(): View
    {
        $users = User::where('id', '!=', auth()->id())
            ->where('is_banned', false)
            ->orderByDesc('is_online')
            ->orderBy('name')
            ->paginate(50);
        return view('people.index', compact('users'));
    }

    public function profile(User $user): View
    {
        return view('people.profile', compact('user'));
    }

    public function startDm(User $user, ConversationService $service): RedirectResponse
    {
        $conversation = $service->getOrCreateDirect(auth()->user(), $user);
        return redirect()->route('chat.conversation', $conversation);
    }
}

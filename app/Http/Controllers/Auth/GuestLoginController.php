<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GuestLoginRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\PresenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestLoginController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.guest-login');
    }

    public function store(GuestLoginRequest $request, PresenceService $presence): RedirectResponse
    {
        $username = 'guest_' . strtolower(Str::random(6));

        $user = User::create([
            'name' => $request->name,
            'username' => $username,
            'role' => 'guest',
            'is_guest' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $presence->markOnline($user);

        $publicGroups = Conversation::where('type', 'group')->where('is_private', false)->get();

        foreach ($publicGroups as $group) {
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $group->id,
                'user_id'         => $user->id,
            ], ['role' => 'member', 'joined_at' => now()]);
        }

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $dm = Conversation::create(['type' => 'direct', 'is_private' => true]);
            ConversationParticipant::create(['conversation_id' => $dm->id, 'user_id' => $user->id, 'role' => 'member', 'joined_at' => now()]);
            ConversationParticipant::create(['conversation_id' => $dm->id, 'user_id' => $admin->id, 'role' => 'member', 'joined_at' => now()]);
        }

        return redirect()->route('chat.index');
    }
}

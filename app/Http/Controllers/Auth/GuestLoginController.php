<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GuestLoginRequest;
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
        $request->session()->put('guest_form_loaded', now());
        return view('auth.guest-login');
    }

    public function store(GuestLoginRequest $request, PresenceService $presence): RedirectResponse
    {
        // Bot check: form submitted too fast
        $loadedAt = $request->session()->get('guest_form_loaded');
        if (!$loadedAt || now()->diffInSeconds($loadedAt) < 2) {
            abort(429, 'Please wait a moment before submitting.');
        }

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
        return redirect()->route('chat.index');
    }
}

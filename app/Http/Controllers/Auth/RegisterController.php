<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\PresenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, PresenceService $presence): RedirectResponse
    {
        $username = $this->generateUsername($request->name);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'username' => $username,
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $presence->markOnline($user);
        return redirect()->route('chat.index');
    }

    private function generateUsername(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $base = $base ?: 'user';
        $username = $base;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter++;
        }
        return $username;
    }
}

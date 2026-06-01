<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', ['user' => auth()->user()]);
    }

    public function toggleDarkMode(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->is_guest) {
            $user->update(['dark_mode' => !$user->dark_mode]);
        }
        return response()->json(['dark_mode' => $user->dark_mode]);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => ['boolean'],
            'email_digest' => ['in:never,daily,weekly'],
        ]);
        auth()->user()->update($request->only('email_notifications', 'email_digest'));
        return response()->json(['success' => true]);
    }
}

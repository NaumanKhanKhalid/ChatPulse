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
        $request->validate(['email_digest' => ['nullable', 'in:never,daily,weekly']]);
        auth()->user()->update([
            'email_notifications' => $request->boolean('email_notifications'),
            'message_previews'    => $request->boolean('message_previews'),
            'sound_alerts'        => $request->boolean('sound_alerts'),
            'email_digest'        => $request->input('email_digest', 'never'),
        ]);
        return response()->json(['success' => true]);
    }

    /** Privacy switches — these actually change behaviour now. */
    public function updatePrivacy(Request $request): JsonResponse
    {
        $request->validate(['who_can_message' => ['nullable', 'in:everyone,contacts']]);
        auth()->user()->update([
            'read_receipts'      => $request->boolean('read_receipts'),
            'show_online_status' => $request->boolean('show_online_status'),
            'show_typing'        => $request->boolean('show_typing'),
            'who_can_message'    => $request->input('who_can_message', 'everyone'),
        ]);
        return response()->json(['success' => true]);
    }

    public function updateAppearance(Request $request): JsonResponse
    {
        $request->validate([
            'font_size'    => ['nullable', 'in:sm,md,lg'],
            'bubble_style' => ['nullable', 'in:modern,classic,minimal'],
        ]);
        auth()->user()->update($request->only('font_size', 'bubble_style'));
        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password'      => ['required', 'current_password'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ]);
        auth()->user()->update(['password' => bcrypt($request->password)]);
        return response()->json(['success' => true]);
    }
}

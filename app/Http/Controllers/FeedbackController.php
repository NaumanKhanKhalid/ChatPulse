<?php
namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type'          => ['required', 'in:bug,suggestion,question,other'],
            'message'       => ['required', 'string', 'min:5', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'page'          => ['nullable', 'string', 'max:190'],
            'screen'        => ['nullable', 'string', 'max:30'],
        ]);

        $user = auth()->user();

        Feedback::create([
            'user_id'       => $user?->id,
            'type'          => $request->type,
            'message'       => $request->message,
            'contact_email' => $request->contact_email ?: $user?->email,
            'page'          => $request->page,
            'browser'       => substr((string) $request->userAgent(), 0, 120),
            'screen'        => $request->screen,
            'status'        => 'open',
        ]);

        try {
            \App\Events\AdminActivity::fire('report', $user, 'sent feedback', Feedback::TYPES[$request->type] ?? $request->type);
        } catch (\Throwable) {}

        return response()->json(['success' => true]);
    }
}

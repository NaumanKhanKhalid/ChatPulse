<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $q = Feedback::with('user', 'handler');
        $status = $request->query('status', 'open');
        if ($status !== 'all') $q->where('status', $status);
        if ($type = $request->query('type')) $q->where('type', $type);
        if ($s = $request->query('q')) $q->where('message', 'like', "%$s%");

        $items = $q->orderByRaw("status = 'open' desc")->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $counts = [
            'open'      => Feedback::where('status', 'open')->count(),
            'reviewing' => Feedback::where('status', 'reviewing')->count(),
            'resolved'  => Feedback::where('status', 'resolved')->count(),
        ];

        return view('admin.feedback', compact('items', 'counts', 'status'));
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate([
            'status'     => ['required', 'in:open,reviewing,resolved'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $feedback->update([
            'status'     => $request->status,
            'admin_note' => $request->admin_note,
            'handled_by' => auth()->id(),
        ]);

        AdminLog::record('feedback.update', 'feedback', $feedback->id,
            $feedback->typeLabel(), 'marked ' . $request->status);

        return back()->with('success', 'Feedback updated.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        AdminLog::record('feedback.delete', 'feedback', $feedback->id, $feedback->typeLabel());
        $feedback->delete();
        return back()->with('success', 'Feedback deleted.');
    }
}

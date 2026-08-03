<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $q = Report::with(['reporter', 'reportedUser', 'conversation', 'resolver']);
        $status = $request->query('status', 'open');
        if ($status !== 'all') $q->where('status', $status);
        if ($reason = $request->query('reason')) $q->where('reason', $reason);

        $reports = $q->orderByRaw("status = 'open' desc")->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $counts = [
            'open'   => Report::where('status', 'open')->count(),
            'closed' => Report::where('status', 'closed')->count(),
        ];

        return view('admin.reports', compact('reports', 'counts', 'status'));
    }

    public function dismiss(Report $report): RedirectResponse
    {
        $report->update([
            'status'      => 'closed',
            'resolution'  => 'dismissed',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);
        AdminLog::record('report.dismiss', 'report', $report->id, $report->reasonLabel(), 'No action needed');
        return back()->with('success', 'Report dismissed.');
    }

    public function banUser(Report $report): RedirectResponse
    {
        $user = $report->reportedUser;
        if (!$user)          return back()->with('error', 'Reported user no longer exists.');
        if ($user->isAdmin()) return back()->with('error', 'Cannot ban an admin.');

        $user->update([
            'is_banned'     => true,
            'banned_at'     => now(),
            'banned_reason' => 'Report #' . $report->id . ' — ' . $report->reasonLabel(),
        ]);
        $report->update([
            'status'      => 'closed',
            'resolution'  => 'user_banned',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        AdminLog::record('report.ban', 'user', $user->id, $user->name, 'via report #' . $report->id);
        return back()->with('success', "{$user->name} banned and report closed.");
    }
}

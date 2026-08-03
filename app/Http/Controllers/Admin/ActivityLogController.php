<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $q = AdminLog::with('admin');
        if ($a = $request->get('action')) $q->where('action', $a);
        if ($s = $request->get('q')) {
            $q->where(fn($w) => $w->where('target_label', 'like', "%$s%")
                ->orWhere('details', 'like', "%$s%"));
        }
        $logs = $q->orderByDesc('created_at')->paginate(30)->withQueryString();
        $actions = AdminLog::select('action')->distinct()->pluck('action');
        return view('admin.activity', compact('logs', 'actions'));
    }
}

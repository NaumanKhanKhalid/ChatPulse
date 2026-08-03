<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $q = Conversation::where('type','group')->withCount(['participants','messages']);
        if ($s = $request->query('q')) $q->where('name', 'like', "%$s%");
        $groups = $q->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.groups', compact('groups'));
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        AdminLog::record('group.delete', 'group', $conversation->id, $conversation->name ?? 'Unnamed');
        $conversation->delete();
        return back()->with('success', 'Group deleted.');
    }
}

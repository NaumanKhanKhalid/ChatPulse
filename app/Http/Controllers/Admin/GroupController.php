<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        $groups = Conversation::where('type','group')->withCount('participants')->orderByDesc('created_at')->paginate(30);
        return view('admin.groups', compact('groups'));
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $conversation->delete();
        return back()->with('success', 'Group deleted.');
    }
}

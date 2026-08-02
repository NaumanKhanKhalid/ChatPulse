<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = User::query();
        if ($s = $request->get('q')) {
            $q->where(fn($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('username', 'like', "%$s%"));
        }
        if ($role = $request->get('role')) $q->where('role', $role);
        if ($request->get('status') === 'online') $q->where('is_online', true);
        if ($request->get('status') === 'banned') $q->where('is_banned', true);
        $users = $q->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => ['nullable','string','max:500']]);
        if ($user->isAdmin()) return back()->with('error', 'Cannot ban an admin.');
        $user->update(['is_banned'=>true,'banned_at'=>now(),'banned_reason'=>$request->reason]);
        return back()->with('success', "User {$user->name} banned.");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update(['is_banned'=>false,'banned_at'=>null,'banned_reason'=>null]);
        return back()->with('success', "User {$user->name} unbanned.");
    }

    public function changeRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => ['required','in:admin,user,guest']]);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot change your own role.');
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Role updated.');
    }
}

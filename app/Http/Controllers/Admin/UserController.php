<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = User::query();
        if ($s = $request->query('q')) {
            $q->where(fn($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('username', 'like', "%$s%"));
        }
        if ($role = $request->query('role')) $q->where('role', $role);
        if ($request->query('status') === 'online') $q->where('is_online', true);
        if ($request->query('status') === 'banned') $q->where('is_banned', true);
        $users = $q->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => ['nullable','string','max:500']]);
        if ($user->isAdmin()) return back()->with('error', 'Cannot ban an admin.');
        $user->update(['is_banned'=>true,'banned_at'=>now(),'banned_reason'=>$request->reason]);
        AdminLog::record('user.ban', 'user', $user->id, $user->name, $request->reason ?: 'No reason given');
        return back()->with('success', "User {$user->name} banned.");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update(['is_banned'=>false,'banned_at'=>null,'banned_reason'=>null]);
        AdminLog::record('user.unban', 'user', $user->id, $user->name);
        return back()->with('success', "User {$user->name} unbanned.");
    }

    public function changeRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => ['required','in:admin,user,guest']]);
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot change your own role.');
        $old = $user->role;
        $user->update(['role' => $request->role]);
        AdminLog::record('user.role', 'user', $user->id, $user->name, "{$old} → {$request->role}");
        return back()->with('success', 'Role updated.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) return back()->with('error', 'Admins already have all permissions.');
        $perms = [];
        foreach (array_keys(User::PERMISSIONS) as $key) {
            $perms[$key] = $request->boolean($key);
        }
        $user->update(['permissions' => $perms]);
        $summary = collect($perms)->map(fn($v, $k) => $k.'='.($v?'on':'off'))->join(', ');
        AdminLog::record('user.permissions', 'user', $user->id, $user->name, $summary);
        return back()->with('success', "Permissions updated for {$user->name}.");
    }

    /** Full profile: activity, recent messages, conversations, moderation history. */
    public function show(User $user): View
    {
        $stats = [
            'messages'      => Message::where('user_id', $user->id)->count(),
            'messages_7d'   => Message::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(7))->count(),
            'conversations' => $user->conversations()->count(),
            'reactions'     => $user->reactions()->count(),
        ];

        $recentMessages = Message::where('user_id', $user->id)
            ->with('conversation')->latest()->limit(10)->get();

        $conversations = $user->conversations()
            ->withCount('messages')->orderByDesc('last_activity_at')->limit(10)->get();

        // Everything an admin ever did to this user
        $adminActions = AdminLog::with('admin')
            ->where('target_type', 'user')->where('target_id', (string) $user->id)
            ->latest()->limit(20)->get();

        $sessions = collect();
        try {
            $sessions = DB::table('sessions')->where('user_id', $user->id)
                ->orderByDesc('last_activity')->limit(10)->get();
        } catch (\Throwable) {}

        return view('admin.user-detail', compact('user', 'stats', 'recentMessages', 'conversations', 'adminActions', 'sessions'));
    }

    /** Ban / unban / change role for many users at once. */
    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'action'  => ['required', 'in:ban,unban,role'],
            'ids'     => ['required', 'array'],
            'ids.*'   => ['integer'],
            'role'    => ['required_if:action,role', 'in:admin,user,guest'],
            'reason'  => ['nullable', 'string', 'max:500'],
        ]);

        $users = User::whereIn('id', $request->ids)
            ->where('id', '!=', auth()->id())
            ->get();

        $done = 0;
        foreach ($users as $u) {
            if ($request->action === 'ban') {
                if ($u->isAdmin()) continue;
                $u->update(['is_banned' => true, 'banned_at' => now(), 'banned_reason' => $request->reason]);
                AdminLog::record('user.ban', 'user', $u->id, $u->name, ($request->reason ?: 'No reason given') . ' (bulk)');
            } elseif ($request->action === 'unban') {
                $u->update(['is_banned' => false, 'banned_at' => null, 'banned_reason' => null]);
                AdminLog::record('user.unban', 'user', $u->id, $u->name, 'bulk');
            } else {
                $old = $u->role;
                $u->update(['role' => $request->role]);
                AdminLog::record('user.role', 'user', $u->id, $u->name, "{$old} → {$request->role} (bulk)");
            }
            $done++;
        }

        return back()->with('success', "{$done} user(s) updated.");
    }

    /** Sign the user out of every device by dropping their sessions. */
    public function forceLogout(User $user): RedirectResponse
    {
        $n = 0;
        try { $n = DB::table('sessions')->where('user_id', $user->id)->delete(); } catch (\Throwable) {}
        $user->update(['is_online' => false]);
        AdminLog::record('user.logout', 'user', $user->id, $user->name, "{$n} session(s) ended");
        return back()->with('success', "{$user->name} signed out of {$n} session(s).");
    }

    /** Export the current filtered result set as CSV. */
    public function export(Request $request): StreamedResponse
    {
        $q = User::query();
        if ($s = $request->query('q')) {
            $q->where(fn($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('username', 'like', "%$s%"));
        }
        if ($role = $request->query('role')) $q->where('role', $role);
        if ($request->query('status') === 'online') $q->where('is_online', true);
        if ($request->query('status') === 'banned') $q->where('is_banned', true);

        AdminLog::record('users.export', null, null, null, 'CSV export');

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Name','Username','Email','Role','Guest','Banned','Ban reason','Online','Last seen','Joined']);
            $q->orderBy('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $u) {
                    fputcsv($out, [
                        $u->id, $u->name, $u->username, $u->email, $u->role,
                        $u->is_guest ? 'yes' : 'no',
                        $u->is_banned ? 'yes' : 'no',
                        $u->banned_reason,
                        $u->is_online ? 'yes' : 'no',
                        $u->last_seen_at?->toDateTimeString(),
                        $u->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, 'users-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpBan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(): View
    {
        $ipBans = IpBan::with('banner')->orderByDesc('created_at')->get();
        $bannedUsers = User::where('is_banned', true)->orderByDesc('banned_at')->get();
        return view('admin.security', compact('ipBans', 'bannedUsers'));
    }

    public function banIp(Request $request): RedirectResponse
    {
        $request->validate([
            'ip_address' => ['required','ip'],
            'reason' => ['nullable','string','max:500'],
            'expires_at' => ['nullable','date','after:now'],
        ]);

        IpBan::updateOrCreate(
            ['ip_address' => $request->ip_address],
            ['banned_by'=>auth()->id(),'reason'=>$request->reason,'expires_at'=>$request->expires_at]
        );

        Cache::forget("ip_ban:{$request->ip_address}");
        return back()->with('success', "IP {$request->ip_address} banned.");
    }

    public function unbanIp(IpBan $ipBan): RedirectResponse
    {
        Cache::forget("ip_ban:{$ipBan->ip_address}");
        $ipBan->delete();
        return back()->with('success', 'IP unbanned.');
    }
}

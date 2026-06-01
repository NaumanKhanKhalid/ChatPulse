<?php
namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(): View
    {
        $bookmarks = Bookmark::where('user_id', auth()->id())
            ->with(['message.user', 'message.conversation'])
            ->orderByDesc('created_at')
            ->paginate(30);
        return view('bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Message $message): JsonResponse
    {
        $user = auth()->user();
        $existing = Bookmark::where('user_id',$user->id)->where('message_id',$message->id)->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false]);
        }
        Bookmark::create(['user_id'=>$user->id,'message_id'=>$message->id,'created_at'=>now()]);
        return response()->json(['bookmarked' => true]);
    }
}

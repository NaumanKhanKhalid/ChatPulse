<?php
namespace App\Http\Controllers;

use App\Services\PresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(PresenceService $presence): JsonResponse
    {
        $presence->heartbeat(auth()->user());
        return response()->json(['ok' => true]);
    }
}

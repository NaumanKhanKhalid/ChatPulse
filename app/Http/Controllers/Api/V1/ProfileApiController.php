<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileApiController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => auth()->user()]);
    }

    public function updateStatus(Request $request, StatusService $service): JsonResponse
    {
        $request->validate([
            'status_type' => ['required','in:available,busy,away'],
            'status_message' => ['nullable','string','max:60'],
            'status_emoji' => ['nullable','string','max:10'],
        ]);
        $user = $service->update(auth()->user(), $request->all());
        return response()->json(['data' => $user]);
    }
}

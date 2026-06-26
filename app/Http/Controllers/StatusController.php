<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateStatusRequest;
use App\Services\StatusService;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function update(UpdateStatusRequest $request, StatusService $service): JsonResponse
    {
        $user = $service->update(auth()->user(), $request->validated());
        return response()->json(['user' => $user]);
    }
}

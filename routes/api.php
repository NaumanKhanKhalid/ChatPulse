<?php

use App\Http\Controllers\Api\V1\ConversationApiController;
use App\Http\Controllers\Api\V1\GroupApiController;
use App\Http\Controllers\Api\V1\MessageApiController;
use App\Http\Controllers\Api\V1\ProfileApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/conversations', [ConversationApiController::class, 'index']);
    Route::get('/conversations/{conversation}/messages', [MessageApiController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageApiController::class, 'store']);
    Route::post('/conversations/direct', [ConversationApiController::class, 'startDirect']);
    Route::get('/groups/public', [GroupApiController::class, 'public']);
    Route::post('/groups/{conversation}/join', [GroupApiController::class, 'join']);
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::patch('/profile/status', [ProfileApiController::class, 'updateStatus']);
});

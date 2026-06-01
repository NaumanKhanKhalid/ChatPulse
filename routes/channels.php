<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return $user->conversations()->where('conversation_id', $conversationId)->exists();
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('call.{callId}', function ($user, $callId) {
    return \App\Models\CallParticipant::where('call_id', $callId)
        ->where('user_id', $user->id)->exists();
});

Broadcast::channel('app', function ($user) {
    return ['id' => $user->id, 'name' => $user->name, 'avatar_url' => $user->avatar_url];
});

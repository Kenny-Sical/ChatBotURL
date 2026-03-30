<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

/*
 * Canal privado por chat.
 * Solo el dueño del chat puede suscribirse.
 */
Broadcast::channel('chat.{chatId}', function ($user, int $chatId) {
    return Chat::where('id', $chatId)
        ->where('user_id', $user->id)
        ->exists();
});

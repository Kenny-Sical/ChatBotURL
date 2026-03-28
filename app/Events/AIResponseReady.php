<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AIResponseReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int          $chatId,
        public readonly ?Conversation $conversation,
        public readonly bool         $error = false
    ) {}

    //Canal privado por chat: solo el dueño del chat puede escuchar.
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatId),
        ];
    }

    //Nombre del evento que escuchará el frontend.
    public function broadcastAs(): string
    {
        return 'ai.response.ready';
    }

    //Datos enviados al frontend.
    public function broadcastWith(): array
    {
        return [
            'chat_id'         => $this->chatId,
            'conversation_id' => $this->conversation?->id,
            'message'         => $this->conversation?->message,
            'error'           => $this->error,
        ];
    }
}

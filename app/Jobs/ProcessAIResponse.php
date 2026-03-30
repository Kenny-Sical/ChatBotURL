<?php

namespace App\Jobs;

use App\Events\AIResponseReady;
use App\Models\Chat;
use App\Models\Conversation;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessAIResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $chatId
    ) {}

    public function handle(\App\Services\VertexAIService $aiService): void
    {
        // Construir historial de la conversación para enviar contexto a la IA
        $history = Conversation::where('chat_id', $this->chatId)
            ->orderBy('created_at')
            ->get(['type', 'message'])
            ->map(fn($c) => [
                'role'    => $c->type,
                'content' => $c->message,
            ])
            ->toArray();

        // Clave de caché basada en el hash del historial completo
        $cacheKey = 'ai_response_' . md5(json_encode($history));

        $responseText = Cache::remember($cacheKey, now()->addHour(), function () use ($history, $aiService) {
            return $aiService->generateContent($history);
        });

        // Guardar respuesta del bot en la base de datos
        $conversation = Conversation::create([
            'chat_id' => $this->chatId,
            'message' => $responseText,
            'type'    => 'bot',
        ]);

        // Emitir evento para que el frontend reciba la respuesta en tiempo real
        broadcast(new AIResponseReady($this->chatId, $conversation))->toOthers();
    }



    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAIResponse failed', [
            'chat_id' => $this->chatId,
            'error'   => $exception->getMessage(),
        ]);

        // Notificar al frontend que ocurrió un error
        broadcast(new AIResponseReady($this->chatId, null, true));
    }
}

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

    public function handle(): void
    {
        // Construir historial de la conversación para enviar contexto a la IA
        $history = Conversation::where('chat_id', $this->chatId)
            ->orderBy('created_at')
            ->get(['type', 'message'])
            ->map(fn($c) => [
                'role'    => $c->type === 'user' ? 'user' : 'assistant',
                'content' => $c->message,
            ])
            ->toArray();

        // Clave de caché basada en el hash del historial completo
        $cacheKey = 'ai_response_' . md5(json_encode($history));

        $responseText = Cache::remember($cacheKey, now()->addHour(), function () use ($history) {
            return $this->callOpenAI($history);
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

    private function callOpenAI(array $messages): string
    {
        $client = new Client(['timeout' => 55]);

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.openai.key'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'    => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => $messages,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta.';
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

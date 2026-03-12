<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Conversation;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    /**
     * Crea un nuevo chat vacío para el usuario autenticado.
     */
    public function createConversation(): JsonResponse
    {
        $chat = Chat::create([
            'title'   => 'Nueva conversación',
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'chat_id' => $chat->id,
            'title'   => $chat->title,
        ], 201);
    }

    /**
     * Guarda el mensaje del usuario, llama a OpenAI de forma síncrona
     * y devuelve la respuesta del bot en la misma petición HTTP.
     */
    public function storeMessage(Request $request, int $chatId): JsonResponse
    {
        $chat = Chat::where('id', $chatId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        // Guardar mensaje del usuario
        Conversation::create([
            'chat_id' => $chat->id,
            'message' => $request->input('message'),
            'type'    => 'user',
        ]);

        // Si es el primer mensaje, usar las primeras palabras como título del chat
        $isFirst = Conversation::where('chat_id', $chat->id)->count() === 1;
        if ($isFirst) {
            $chat->update([
                'title' => mb_substr($request->input('message'), 0, 60),
            ]);
        }

        // Llamar a OpenAI de forma síncrona
        $botMessage = $this->callOpenAI($chat->id);

        // Guardar respuesta del bot
        Conversation::create([
            'chat_id' => $chat->id,
            'message' => $botMessage,
            'type'    => 'bot',
        ]);

        return response()->json([
            'chat_title'  => $chat->title,
            'is_first'    => $isFirst,
            'bot_message' => $botMessage,
        ]);
    }

    private function callOpenAI(int $chatId): string
    {
        $history = Conversation::where('chat_id', $chatId)
            ->orderBy('created_at')
            ->get(['type', 'message'])
            ->map(fn($c) => [
                'role'    => $c->type === 'user' ? 'user' : 'assistant',
                'content' => $c->message,
            ])
            ->toArray();

        try {
            $client = new Client(['timeout' => 55, 'verify' => false]);

            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openai.key'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $history,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta.';
        } catch (\Throwable $e) {
            Log::error('OpenAI error', ['chat_id' => $chatId, 'error' => $e->getMessage()]);

            return 'Lo siento, ocurrió un error al consultar la IA. Intenta de nuevo.';
        }
    }

    /**
     * Retorna todos los mensajes de un chat (para cargar el historial al abrir).
     */
    public function getMessages(int $chatId): JsonResponse
    {
        $chat = Chat::where('id', $chatId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $messages = Conversation::where('chat_id', $chat->id)
            ->orderBy('created_at')
            ->get(['id', 'type', 'message', 'path', 'created_at']);

        return response()->json([
            'chat_id'  => $chat->id,
            'title'    => $chat->title,
            'messages' => $messages,
        ]);
    }
}

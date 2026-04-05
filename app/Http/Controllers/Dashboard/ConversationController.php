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
    //Crea un nuevo chat vacío para el usuario autenticado.
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

    //Guarda el mensaje del usuario, llama a Gemini u OpenAI de forma síncrona
    // y devuelve la respuesta del bot en la misma petición HTTP.
    public function storeMessage(Request $request, int $chatId): JsonResponse
    {
        $chat = Chat::where('id', $chatId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'file'    => ['nullable', 'file', 'mimes:txt,json,pdf,docx', 'max:10240'],
        ]);

        if (!$request->filled('message') && !$request->hasFile('file')) {
            return response()->json(['error' => 'Debes enviar un mensaje o un archivo.'], 400);
        }

        $extractedText = '';
        $hasAttachment = false;
        $dbMessage = trim($request->input('message', ''));

        if ($request->hasFile('file')) {
            $hasAttachment = true;
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();

            if (empty($dbMessage)) {
                $dbMessage = 'Archivo adjunto: ' . $file->getClientOriginalName();
            }

            try {
                if (in_array($extension, ['txt', 'json'])) {
                    $extractedText = file_get_contents($path);
                } elseif ($extension === 'pdf') {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($path);
                    $extractedText = $pdf->getText();
                } elseif ($extension === 'docx') {
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if (method_exists($element, 'getText')) {
                                $extractedText .= $element->{'getText'}() . "\n";
                            } elseif (method_exists($element, 'getElements')) {
                                foreach ($element->{'getElements'}() as $innerElement) {
                                    if (method_exists($innerElement, 'getText')) {
                                        $extractedText .= $innerElement->{'getText'}() . "\n";
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('File parse error', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'No se pudo leer el contenido del archivo adjunto.'], 500);
            }
        }

        // Guardar mensaje del usuario
        Conversation::create([
            'chat_id' => $chat->id,
            'message' => $dbMessage,
            'type'    => 'user',
            'has_attachment' => $hasAttachment,
        ]);

        // Si es el primer mensaje, usar las primeras palabras como título del chat
        $isFirst = Conversation::where('chat_id', $chat->id)->count() === 1;
        if ($isFirst) {
            $chat->update([
                'title' => mb_substr($dbMessage, 0, 60),
            ]);
        }

        // Llamar a IA de forma síncrona
        $history = Conversation::where('chat_id', $chat->id)
            ->orderBy('created_at')
            ->get(['type', 'message'])
            ->map(fn($c) => [
                'role'    => $c->type,
                'content' => $c->message,
            ])
            ->toArray();

        // Inyectar el contenido del archivo solo en la petición actual al LLM
        if ($hasAttachment && !empty(trim($extractedText))) {
            $lastIndex = count($history) - 1;
            $history[$lastIndex]['content'] .= "\n\n[CONTENIDO DEL DOCUMENTO ADJUNTO PARA ANALIZAR]\n" . $extractedText;
        }

        try {
            $modelType = $request->input('model', 'vertex');
            $aiService = $modelType === 'openai' 
                ? app(\App\Services\OpenAIService::class) 
                : app(\App\Services\VertexAIService::class);

            $botMessage = $aiService->generateContent($history);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Service error', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, '429') || str_contains(strtoupper($errorMessage), 'RESOURCE_EXHAUSTED')) {
                $botMessage = 'Demasiadas personas están usando el asistente justo ahora y hemos alcanzado el límite temporal. Por favor, espera unos segundos e intenta nuevamente.';
            } else {
                $botMessage = 'Lo siento, ocurrió un error inesperado al consultar la IA. Intenta de nuevo en unos momentos.';
            }
        }

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

    //Recibe un archivo de audio, lo transcribe con Groq, y llama a la IA seleccionada
    public function storeVoiceMessage(Request $request, int $chatId, \App\Services\GroqService $groqService): JsonResponse
    {
        $chat = Chat::where('id', $chatId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'audio' => ['required', 'file'],
        ]);

        try {
            // Transcribir el archivo de audio usando la ruta temporal
            $transcribedText = $groqService->speechToText($request->file('audio')->getRealPath());
            
            if (empty(trim($transcribedText))) {
                return response()->json(['error' => 'No se detectó voz en el audio.'], 400);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Voice Transcription Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al transcribir el audio. Intenta de nuevo.'], 500);
        }

        // Guardar mensaje del usuario
        Conversation::create([
            'chat_id' => $chat->id,
            'message' => $transcribedText,
            'type'    => 'user',
        ]);

        $isFirst = Conversation::where('chat_id', $chat->id)->count() === 1;
        if ($isFirst) {
            $chat->update([
                'title' => mb_substr($transcribedText, 0, 60),
            ]);
        }

        // Llamado al LLM
        $history = Conversation::where('chat_id', $chat->id)
            ->orderBy('created_at')
            ->get(['type', 'message'])
            ->map(fn($c) => [
                'role'    => $c->type,
                'content' => $c->message,
            ])
            ->toArray();

        try {
            $modelType = $request->input('model', 'vertex');
            $aiService = $modelType === 'openai' 
                ? app(\App\Services\OpenAIService::class) 
                : app(\App\Services\VertexAIService::class);

            $botMessage = $aiService->generateContent($history, true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Service error on Voice', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, '429') || str_contains(strtoupper($errorMessage), 'RESOURCE_EXHAUSTED')) {
                $botMessage = 'El asistente está sobrecargado en este instante. Espera unos segundos y vuelve a intentarlo.';
            } else {
                $botMessage = 'Lo siento, ocurrió un error al consultar la IA. Intenta de nuevo.';
            }
        }

        // Guardar respuesta del bot
        Conversation::create([
            'chat_id' => $chat->id,
            'message' => $botMessage,
            'type'    => 'bot',
        ]);

        return response()->json([
            'chat_title'   => $chat->title,
            'is_first'     => $isFirst,
            'user_message' => $transcribedText,
            'bot_message'  => $botMessage,
        ]);
    }

    //Retorna todos los mensajes de un chat para cargar el historial al abrir.
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

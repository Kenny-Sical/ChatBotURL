<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->model = 'ft:gpt-4.1-nano-2025-04-14:chatmamado:chatbotv2:DNbp3oZt';
    }

    /**
     * Llama al modelo fine-tuned en OpenAI
     *
     * @param array $messages El historial de mensajes
     * @param bool $isVoice Indica si la solicitud viene de una conversación por voz
     * @return string La respuesta del asistente
     */
    public function generateContent(array $messages, bool $isVoice = false): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception();
        }

        // Determinar las instrucciones del sistema en base al tipo de origen (texto o voz)
        $strictScope = " BAJO NINGUNA CIRCUNSTANCIA debes responder a preguntas, conversar o proporcionar información sobre temas que no estén estrictamente relacionados con programación, algoritmos o informática. Si el usuario te pregunta sobre temas externos (política, cocina, entretenimiento, entre otros), debes negarte amablemente diciendo que solo estás capacitado para asistir en fundamentos de programación.";

        if ($isVoice) {
            $systemInstruction = "Eres un asistente educativo de voz especializado en fundamentos de programación. Tu función es explicar conceptos y resolver dudas de manera concisa, clara y fácil de escuchar. NUNCA proporciones código real en ningún lenguaje bajo ninguna circunstancia." . $strictScope . " Dado que tus respuestas se leerán en una llamada de voz, evita los bloques largos de texto. Si es estrictamente necesario ilustrar una lógica, usa 'pseudocódigo narrado' de máximo 2 o 3 oraciones en lenguaje natural (ejemplo: 'si la condición se cumple, entonces haz esto, de lo contrario, haz aquello'). No uses símbolos, viñetas, ni estructuras visuales complejas.";
        } else {
            $systemInstruction = "Eres un asistente educativo especializado en fundamentos de programación. Tu función es ayudar a los estudiantes a comprender conceptos, resolver dudas y proporcionar pseudocódigo cuando sea necesario. NUNCA debes proporcionar código en ningún lenguaje de programación bajo ninguna circunstancia." . $strictScope;
        }

        // Construir el array de contenidos en formato compatible con OpenAI
        $contents = [];
        
        // Primero insertamos el System Prompt
        $contents[] = [
            'role' => 'system',
            'content' => $systemInstruction
        ];

        // Mapear los roles del historial (bot -> assistant, user -> user)
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'assistant';
            $contents[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }

        // Reforzar la instrucción en el último mensaje para vencer el "few-shot learning" del historial
        if (count($contents) > 1) { // Mayor a 1 porque el índice 0 es el sistema
            $lastIndex = count($contents) - 1;
            if ($isVoice) {
                $contents[$lastIndex]['content'] .= "\n\n[NOTA AL ASISTENTE: Proporciona la respuesta a esto último de forma MUY concisa y narrada, sin código y sin viñetas, ya que será leída en voz alta.]";
            } else {
                $contents[$lastIndex]['content'] .= "\n\n[NOTA AL ASISTENTE: Puedes responder con pseudocódigo estructurado, listas y formato markdown detallado según sea necesario.]";
            }
        }

        $url = "https://api.openai.com/v1/chat/completions";

        $response = Http::withToken($this->apiKey)
            ->timeout(55)
            ->withOptions(['verify' => false]) // Para entornos WAMP locales con problemas SSL
            ->post($url, [
                'model' => $this->model,
                'messages' => $contents,
                'temperature' => 0.7,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta válida del modelo OpenAI.';
        }

        Log::error('OpenAI Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception('Error al contactar con OpenAI: ' . $response->status() . ' - ' . $response->body());
    }
}

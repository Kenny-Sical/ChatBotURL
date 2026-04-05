<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VertexAIService
{
    private string $projectId;
    private string $location;
    private string $endpointId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->projectId = config('services.vertexai.project_id');
        $this->location = config('services.vertexai.location');
        $this->endpointId = config('services.vertexai.endpoint_id');
        $this->credentialsPath = config('services.vertexai.credentials');
    }


    //Obtiene el token Bearer utilizando google/auth
    private function getAuthToken(): string
    {
        $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
        // Asume que la ruta es relativa a la raíz del proyecto
        $path = base_path($this->credentialsPath);
        
        $credentials = new ServiceAccountCredentials($scopes, $path);
        
        // Ignorar la verificación SSL para desarrollo local
        $guzzle = new \GuzzleHttp\Client(['verify' => false]);
        $httpHandler = \Google\Auth\HttpHandler\HttpHandlerFactory::build($guzzle);
        
        $tokenData = $credentials->fetchAuthToken($httpHandler);
        
        if (isset($tokenData['access_token'])) {
            return $tokenData['access_token'];
        }

        throw new \Exception('No se pudo obtener el token de acceso de Google Cloud.');
    }

    /**
     * Llama al modelo fine-tuned en Vertex AI
     *
     * @param array $messages El historial de mensajes
     * @param bool $isVoice Indica si la solicitud viene de una conversación por voz
     * @return string La respuesta del asistente
     */
    public function generateContent(array $messages, bool $isVoice = false): string
    {
        $token = $this->getAuthToken();
        
        // Formatear los inputs para el formato esperado por Gemini: user y model
        $contents = [];
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg['content']]
                ]
            ];
        }

        // Reforzar la instrucción en el último mensaje para vencer el "few-shot learning" del historial
        if (!empty($contents)) {
            $lastIndex = count($contents) - 1;
            if ($isVoice) {
                $contents[$lastIndex]['parts'][0]['text'] .= "\n\n[NOTA AL ASISTENTE: Proporciona la respuesta a esto último de forma MUY concisa y narrada, sin código y sin viñetas, ya que será leída en voz alta.]";
            } else {
                $contents[$lastIndex]['parts'][0]['text'] .= "\n\n[NOTA AL ASISTENTE: Puedes responder con pseudocódigo estructurado, listas y formato markdown detallado según sea necesario.]";
            }
        }

        $strictScope = " BAJO NINGUNA CIRCUNSTANCIA debes responder a preguntas, conversar o proporcionar información sobre temas que no estén estrictamente relacionados con programación, algoritmos o informática. Si el usuario te pregunta sobre temas externos (política, cocina, entretenimiento, entre otros), debes negarte amablemente diciendo que solo estás capacitado para asistir en fundamentos de programación.";

        if ($isVoice) {
            $systemInstruction = "Eres un asistente educativo de voz especializado en fundamentos de programación. Tu función es explicar conceptos y resolver dudas de manera concisa, clara y fácil de escuchar. NUNCA proporciones código real en ningún lenguaje bajo ninguna circunstancia." . $strictScope . " Dado que tus respuestas se leerán en una llamada de voz, evita los bloques largos de texto. Si es estrictamente necesario ilustrar una lógica, usa 'pseudocódigo narrado' de máximo 2 o 3 oraciones en lenguaje natural (ejemplo: 'si la condición se cumple, entonces haz esto, de lo contrario, haz aquello'). No uses símbolos, viñetas, ni estructuras visuales complejas.";
        } else {
            $systemInstruction = "Eres un asistente educativo especializado en fundamentos de programación. Tu función es ayudar a los estudiantes a comprender conceptos, resolver dudas y proporcionar pseudocódigo cuando sea necesario. NUNCA debes proporcionar código en ningún lenguaje de programación bajo ninguna circunstancia." . $strictScope;
        }

        $url = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/endpoints/{$this->endpointId}:generateContent";

        $response = Http::withToken($token)
            ->timeout(55)
            ->withOptions(['verify' => false]) // Se mantiene verify=false por si el entorno local usa WAMP y da error SSL
            ->post($url, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No se pudo obtener una respuesta válida del modelo.';
        }

        Log::error('Vertex AI Error', [
            'status' => $response->status(),
            'body' => $response->body(),
            'url' => $url,
        ]);

        throw new \Exception('Error al contactar con Vertex AI: ' . $response->status() . ' - ' . $response->body());
    }
}

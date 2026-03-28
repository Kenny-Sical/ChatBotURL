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
     * @return string La respuesta del asistente
     */
    public function generateContent(array $messages): string
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

        $systemInstruction = "Eres un asistente educativo especializado en fundamentos de programación. Tu función es ayudar a los estudiantes a comprender conceptos, resolver dudas y proporcionar pseudocódigo cuando sea necesario. NUNCA debes proporcionar código en ningún lenguaje de programación bajo ninguna circunstancia.";

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

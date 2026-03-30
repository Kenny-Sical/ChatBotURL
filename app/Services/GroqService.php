<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    /**
     * Transcribe un archivo de audio usando el modelo whisper-large-v3 de Groq.
     *
     * @param string $audioPath La ruta absoluta del archivo de audio en el disco.
     * @return string El texto transcrito.
     * @throws \Exception
     */
    public function speechToText(string $audioPath): string
    {
        $url = 'https://api.groq.com/openai/v1/audio/transcriptions';

        // Petición POST multipart al endpoint compatible de Groq
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->withOptions(['verify' => false]) // Bypass de validación SSL local para evitar error 60 en WAMP
            ->attach(
                'file', file_get_contents($audioPath), 'voice.webm'
            )
            ->post($url, [
                'model' => 'whisper-large-v3',
                'temperature' => 0.0,
                'response_format' => 'verbose_json',
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['text'] ?? '';
        }

        Log::error('Groq API Error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception('Error al realizar transcripción con Groq: ' . $response->status() . ' - ' . $response->body());
    }
}

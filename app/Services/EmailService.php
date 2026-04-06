<?php

namespace App\Services;

use Resend;
use Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private string $apiKey;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        // Resend API key instead of SMTP credentials
        $this->apiKey      = env('RESEND_API_KEY', '');
        $this->fromAddress = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev'));
        $this->fromName    = config('mail.from.name', env('MAIL_FROM_NAME', config('app.name')));
    }

    /**
     * Envía un correo electrónico en formato HTML usando la API HTTP de Resend.
     *
     * @param  string  $htmlBody   Contenido HTML del correo.
     * @param  string  $subject    Asunto del correo.
     * @param  string  $to         Dirección de destino.
     * @param  string  $replyTo    Dirección de respuesta (opcional).
     * @return object  {valid: bool, message: string}
     */
    public function send(string $htmlBody, string $subject, string $to, string $replyTo = ''): object
    {
        try {
            if (empty($this->apiKey)) {
                throw new Exception('Asegúrate de agregar RESEND_API_KEY a tu archivo .env');
            }

            $resend = Resend::client($this->apiKey);

            $payload = [
                'from'    => $this->fromName . ' <' . $this->fromAddress . '>',
                'to'      => $to,
                'subject' => $subject,
                'html'    => $htmlBody,
            ];

            if (!empty($replyTo)) {
                $payload['reply_to'] = $replyTo;
            }

            $resend->emails->send($payload);

            return (object) ['valid' => true,  'message' => 'Correo enviado correctamente por Resend.'];

        } catch (\Throwable $e) {
            Log::error('Resend Error: ' . $e->getMessage());
            return (object) ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}

<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->host        = config('mail.mailers.smtp.host',        env('MAIL_HOST', '127.0.0.1'));
        $this->port        = (int) config('mail.mailers.smtp.port',  env('MAIL_PORT', 587));
        $this->username    = config('mail.mailers.smtp.username',     env('MAIL_USERNAME', ''));
        $this->password    = config('mail.mailers.smtp.password',     env('MAIL_PASSWORD', ''));
        $this->encryption  = config('mail.mailers.smtp.encryption',  env('MAIL_ENCRYPTION', 'tls'));
        $this->fromAddress = config('mail.from.address',             env('MAIL_FROM_ADDRESS', 'no-reply@example.com'));
        $this->fromName    = config('mail.from.name',                env('MAIL_FROM_NAME', config('app.name')));
    }

    /**
     * Envía un correo electrónico en formato HTML.
     *
     * @param  string  $htmlBody   Contenido HTML del correo.
     * @param  string  $subject    Asunto del correo.
     * @param  string  $to         Dirección de destino.
     * @param  string  $replyTo    Dirección de respuesta (opcional).
     * @return object  {valid: bool, message: string}
     */
    public function send(string $htmlBody, string $subject, string $to, string $replyTo = ''): object
    {
        $mail = new PHPMailer(true);

        try {
            // Servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = $this->encryption;
            $mail->Port       = $this->port;
            $mail->CharSet    = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom($this->fromAddress, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($replyTo ?: $this->fromAddress, $this->fromName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = mb_convert_encoding($subject, 'UTF-8');
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();

            return (object) ['valid' => true,  'message' => 'Correo enviado correctamente.'];
        } catch (Exception $e) {
            return (object) ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}

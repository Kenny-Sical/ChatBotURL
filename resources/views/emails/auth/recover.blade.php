@extends('emails.base')

@section('content')
<div style="margin:0 20px; padding:40px; background-color:#ffffff; border-radius:8px; border-top:4px solid #2873b8;">

    <!-- Saludo -->
    <div style="padding-bottom:24px; font-size:18px;">
        <strong>¡Hola, {{ $userName }}!</strong>
    </div>

    <!-- Mensaje -->
    <div style="padding-bottom:24px; color:#4a5568;">
        Recibiste este correo porque se solicitó el restablecimiento de la contraseña de tu cuenta.
        Para continuar, haz clic en el botón a continuación:
    </div>

    <!-- Botón -->
    <div style="padding-bottom:36px; text-align:center;">
        <a href="{{ $resetUrl }}"
           rel="noopener"
           target="_blank"
           style="text-decoration:none; display:inline-block; text-align:center;
                  padding:12px 32px; font-size:15px; line-height:1.5;
                  border-radius:6px; color:#ffffff; background-color:#2873b8;
                  font-weight:700; outline:none; border:0;">
            Restablecer contraseña
        </a>
    </div>

    <!-- Separador -->
    <div style="border-bottom:1px solid #cfd9e7; margin:8px 0 24px 0;"></div>

    <!-- URL de respaldo -->
    <div style="padding-bottom:24px; word-break:break-all; color:#718096; font-size:13px;">
        <p style="margin-bottom:8px;">¿El botón no funciona? Copia y pega esta URL en tu navegador:</p>
        <a href="{{ $resetUrl }}" rel="noopener" target="_blank"
           style="color:#2369c6; word-break:break-all;">{{ $resetUrl }}</a>
    </div>

    <!-- Aviso de expiración -->
    <div style="background-color:#f4f7fb; border-left:4px solid #7fa5d7;
                padding:12px 16px; border-radius:4px; font-size:13px; color:#4a6a8a;">
        <strong>Nota:</strong> Este enlace expirará en <strong>60 minutos</strong>.
        Si no solicitaste este cambio, ignora este correo.
    </div>

    <!-- Firma -->
    <div style="padding-top:32px; color:#718096; font-size:13px;">
        Saludos,<br>
        <strong>El equipo de {{ config('app.name') }}</strong>
    </div>

</div>
@endsection

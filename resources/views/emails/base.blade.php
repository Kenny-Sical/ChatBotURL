<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#edf2f7; font-family: Arial, Helvetica, sans-serif; font-size:15px; font-weight:normal; line-height:1.5; color:#2F3044;">

    <br>
    <table style="border-collapse:collapse; margin:0 auto; padding:0; max-width:600px; width:100%;" cellspacing="0" cellpadding="0" border="0" align="center">
        <tbody>

            <!-- Logo / Cabecera -->
            <tr>
                <td style="text-align:center; padding:40px 40px 20px 40px;" valign="center" align="center">
                    <img src="{{ config('app.url') }}/favicon.png"
                         style="height:52px; filter:none;"
                         alt="{{ config('app.name') }}">
                    <div style="margin-top:12px; font-size:20px; font-weight:700; color:#2873b8; letter-spacing:0.5px;">
                        {{ config('app.name') }}
                    </div>
                </td>
            </tr>

            <!-- Cuerpo -->
            <tr>
                <td valign="center" align="left">
                    @yield('content')
                </td>
            </tr>

            <!-- Pie -->
            <tr>
                <td style="font-size:13px; text-align:center; padding:20px 40px 30px 40px; color:#6d6e7c;" valign="center" align="center">
                    <div style="border-top:1px solid #cfd9e7; padding-top:16px;">
                        Si no solicitaste este correo, puedes ignorarlo con seguridad.<br><br>
                        Copyright &copy; {{ date('Y') }}
                        <a href="{{ config('app.url') }}" style="color:#2369c6; text-decoration:none;">
                            {{ config('app.name') }}
                        </a>. Todos los derechos reservados.
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
    <br>

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - Parkme!</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; padding: 20px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #4f46e5; padding: 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .content p { font-size: 16px; line-height: 1.5; }
        .credentials { background-color: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb; margin: 20px 0; }
        .credentials p { margin: 5px 0; font-family: monospace; font-size: 14px; }
        .footer { text-align: center; padding: 20px; font-size: 14px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .button { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido a Parkme!</h1>
        </div>
        <div class="content">
            <p>Hola, <strong>{{ $nombres }}</strong>.</p>
            <p>Tu cuenta ha sido creada exitosamente. Has sido asignado con el rol de <strong>{{ $rol }}</strong>.</p>
            <p>A continuación, encontrarás tus credenciales de acceso. Te recordamos que, por motivos de seguridad, el sistema te pedirá cambiar tu contraseña la primera vez que inicies sesión.</p>
            
            <div class="credentials">
                <p><strong>Correo electrónico:</strong> {{ $correo }}</p>
                <p><strong>Contraseña temporal:</strong> {{ $contrasena }}</p>
                <p><strong>Código de verificación:</strong> <span style="font-size: 18px; letter-spacing: 2px;">{{ $codigoVerificacion }}</span></p>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">Iniciar Sesión</a>
            </p>
            
            <p>Si tienes alguna pregunta, no dudes en contactar al administrador de tu sistema.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Parkme! Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

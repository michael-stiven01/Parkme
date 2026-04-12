<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código de Verificación - Parkme!</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0f172a;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
        }
        .header span {
            color: #3b82f6;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #475569;
        }
        .code-box {
            background-color: #f1f5f9;
            border: 2px dashed #94a3b8;
            border-radius: 8px;
            padding: 20px;
            margin: 30px auto;
            max-width: 250px;
            font-size: 32px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 4px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Parkme<span>!</span></h1>
        </div>
        <div class="content">
            <h2>Verifica tu correo electrónico</h2>
            <p>Hola, estás a un paso de comenzar a gestionar tu parqueadero de forma inteligente.</p>
            <p>Utiliza el siguiente código para verificar tu cuenta:</p>
            
            <div class="code-box">
                {{ $codigo }}
            </div>
            
            <p style="color: #ef4444; font-size: 14px;"><strong>Nota:</strong> Este código expirará en 15 minutos.</p>
            <p>Si no has solicitado este código, puedes ignorar este mensaje de forma segura.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Parkme! Todos los derechos reservados.
        </div>
    </div>
</body>
</html>

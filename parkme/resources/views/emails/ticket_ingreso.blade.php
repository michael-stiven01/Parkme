<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Ingreso - Parkme!</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 32px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0 0 4px;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header h1 span { color: #818cf8; }
        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }
        .badge {
            display: inline-block;
            background-color: #22c55e;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 20px;
            padding: 4px 14px;
            margin-top: 14px;
        }
        .content {
            padding: 36px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .greeting-sub {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 28px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .info-row {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            flex: 0 0 140px;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            flex: 1;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }
        .info-value.placa {
            font-size: 20px;
            letter-spacing: 3px;
            color: #4f46e5;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .footer strong { color: #475569; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Parkme<span>!</span></h1>
            <p>Sistema de Gestión de Parqueaderos</p>
            <span class="badge">Ingreso Confirmado</span>
        </div>

        <div class="content">
            <p class="greeting">Hola, {{ $datos['nombre_cliente'] ?? 'Cliente' }}</p>
            <p class="greeting-sub">Su vehículo ha ingresado correctamente al parqueadero.</p>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Placa</span>
                    <span class="info-value placa">{{ $datos['placa'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cupo asignado</span>
                    <span class="info-value">{{ $datos['codigo_cupo'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tipo vehículo</span>
                    <span class="info-value">{{ $datos['tipo_vehiculo'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha ingreso</span>
                    <span class="info-value">
                        @if(!empty($datos['fecha_ingreso']))
                            {{ \Carbon\Carbon::parse($datos['fecha_ingreso'])->format('d/m/Y H:i') }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">N.° Registro</span>
                    <span class="info-value">#{{ str_pad($datos['id_registro'] ?? 0, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Este correo fue generado automáticamente. Por favor no responda a este mensaje.</p>
            <p style="margin-top: 6px;">&copy; {{ date('Y') }} <strong>Parkme!</strong> — Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

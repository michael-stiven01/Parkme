<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Salida - Parkme!</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; line-height: 1.6; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.08); overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 32px 30px; text-align: center; }
        .header h1 { color: #fff; font-size: 30px; font-weight: 800; letter-spacing: -.5px; }
        .header h1 span { color: #818cf8; }
        .header p { color: #94a3b8; font-size: 13px; margin-top: 4px; }
        .badge { display: inline-block; background-color: #6366f1; color: #fff; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-radius: 20px; padding: 4px 14px; margin-top: 14px; }
        .content { padding: 36px 30px; }
        .greeting { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 6px; }
        .greeting-sub { font-size: 14px; color: #64748b; margin-bottom: 28px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
        .info-row { display: flex; align-items: center; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { flex: 0 0 150px; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
        .info-value { flex: 1; font-size: 15px; font-weight: 600; color: #0f172a; }
        .info-value.placa { font-size: 20px; letter-spacing: 3px; color: #4f46e5; }
        .info-value.monto { font-size: 22px; color: #059669; }
        .divider { border: none; border-top: 2px dashed #e2e8f0; margin: 20px 0; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer strong { color: #475569; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Parkme<span>!</span></h1>
        <p>Sistema de Gestión de Parqueaderos</p>
        <span class="badge">Comprobante de Salida</span>
    </div>

    <div class="content">
        <p class="greeting">Hola, {{ $datos['nombre_cliente'] ?? 'Cliente' }}</p>
        <p class="greeting-sub">Gracias por usar Parkme! A continuación encontrará el resumen de su estadía.</p>

        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Placa</span>
                <span class="info-value placa">{{ $datos['placa'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cupo</span>
                <span class="info-value">{{ $datos['codigo_cupo'] ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ingreso</span>
                <span class="info-value">
                    @if(!empty($datos['fecha_ingreso']))
                        {{ \Carbon\Carbon::parse($datos['fecha_ingreso'])->format('d/m/Y H:i') }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Salida</span>
                <span class="info-value">
                    @if(!empty($datos['fecha_salida']))
                        {{ \Carbon\Carbon::parse($datos['fecha_salida'])->format('d/m/Y H:i') }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Duración</span>
                <span class="info-value">
                    @php
                        $min = (int)($datos['tiempo_minutos'] ?? 0);
                        $h   = intdiv($min, 60);
                        $m   = $min % 60;
                    @endphp
                    {{ $h > 0 ? "{$h}h {$m}min" : "{$m} min" }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tarifa aplicada</span>
                <span class="info-value">{{ $datos['nombre_tarifa'] ?? 'Sin tarifa' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total cobrado</span>
                <span class="info-value monto">
                    ${{ number_format((float)($datos['valor_cobrado'] ?? 0), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>N.° Registro: <strong>#{{ str_pad($datos['id_registro'] ?? 0, 6, '0', STR_PAD_LEFT) }}</strong></p>
        <p style="margin-top:6px;">© {{ date('Y') }} <strong>Parkme!</strong> — Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>

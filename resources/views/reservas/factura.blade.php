<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Reserva #{{ $reserva->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1a1a2e;
            font-size: 13px;
            line-height: 1.6;
            background: #fff;
        }

        .factura-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
        }

        /* === HEADER === */
        .factura-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #e94560;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .factura-brand h1 {
            font-size: 26px;
            font-weight: 800;
            color: #e94560;
            letter-spacing: -0.5px;
        }

        .factura-brand p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .factura-info {
            text-align: right;
        }

        .factura-info h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .factura-numero {
            font-size: 14px;
            color: #e94560;
            font-weight: 700;
            margin-top: 4px;
        }

        .factura-fecha {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* === DATOS SECCIONES === */
        .datos-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .datos-seccion {
            flex: 1;
            background: #f8f9fb;
            border-radius: 8px;
            padding: 16px;
            border-left: 3px solid #e94560;
        }

        .datos-seccion h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #e94560;
            margin-bottom: 10px;
        }

        .datos-seccion .dato {
            margin-bottom: 6px;
        }

        .datos-seccion .dato-label {
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .datos-seccion .dato-valor {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
        }

        /* === TABLA DETALLE === */
        .detalle-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .detalle-tabla thead th {
            background: #1a1a2e;
            color: #ffffff;
            padding: 10px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detalle-tabla thead th:first-child {
            border-radius: 6px 0 0 0;
        }

        .detalle-tabla thead th:last-child {
            border-radius: 0 6px 0 0;
            text-align: center;
        }

        .detalle-tabla tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .detalle-tabla tbody td:last-child {
            text-align: center;
        }

        .detalle-tabla tbody tr:last-child td {
            border-bottom: none;
        }

        /* === ESTADO === */
        .estado-pago {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .estado-pagada {
            background: #d1fae5;
            color: #065f46;
        }

        .estado-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        /* === FOOTER === */
        .factura-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .factura-footer p {
            font-size: 11px;
            color: #9ca3af;
        }

        .factura-footer .gracias {
            font-size: 16px;
            font-weight: 700;
            color: #e94560;
            margin-bottom: 5px;
        }

        /* === RESUMEN === */
        .resumen-box {
            background: #f8f9fb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .resumen-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12px;
        }

        .resumen-row.total {
            border-top: 2px solid #1a1a2e;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 15px;
            font-weight: 800;
            color: #e94560;
        }

        .observaciones {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .observaciones h4 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #92400e;
            margin-bottom: 4px;
        }

        .observaciones p {
            font-size: 12px;
            color: #78350f;
        }
    </style>
</head>
<body>
    <div class="factura-container">
        <!-- Header -->
        <div class="factura-header">
            <div class="factura-brand">
                <h1>&#127860; ReservaGourmet</h1>
                <p>Sistema de Reservas para Restaurante</p>
            </div>
            <div class="factura-info">
                <h2>Factura</h2>
                <div class="factura-numero">#RES-{{ str_pad($reserva->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="factura-fecha">Emitida: {{ now()->format('d/m/Y - h:i A') }}</div>
            </div>
        </div>

        <!-- Datos del Cliente y Reserva -->
        <div class="datos-grid">
            <div class="datos-seccion">
                <h3>Datos del Cliente</h3>
                <div class="dato">
                    <div class="dato-label">Nombre</div>
                    <div class="dato-valor">{{ $reserva->nombre_persona }}</div>
                </div>
                <div class="dato">
                    <div class="dato-label">Documento</div>
                    <div class="dato-valor">{{ $reserva->numero_documento }}</div>
                </div>
                <div class="dato">
                    <div class="dato-label">Teléfono</div>
                    <div class="dato-valor">{{ $reserva->telefono }}</div>
                </div>
            </div>
            <div class="datos-seccion">
                <h3>Datos de la Reserva</h3>
                <div class="dato">
                    <div class="dato-label">Mesa</div>
                    <div class="dato-valor">{{ $reserva->mesa->nombre }} ({{ $reserva->mesa->ubicacion ?: 'General' }})</div>
                </div>
                <div class="dato">
                    <div class="dato-label">Fecha</div>
                    <div class="dato-valor">{{ $reserva->fecha->format('d/m/Y') }}</div>
                </div>
                <div class="dato">
                    <div class="dato-label">Hora</div>
                    <div class="dato-valor">{{ \Carbon\Carbon::parse($reserva->hora)->format('h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Tabla detalle -->
        <table class="detalle-tabla">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Detalle</th>
                    <th>Cant. Personas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 600;">Reserva de Mesa</td>
                    <td>{{ $reserva->mesa->nombre }} - {{ $reserva->mesa->ubicacion ?: 'General' }} (Capacidad: {{ $reserva->mesa->capacidad }})</td>
                    <td style="font-weight: 700; font-size: 14px;">{{ $reserva->cantidad_personas }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Fecha y Hora</td>
                    <td>{{ $reserva->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($reserva->hora)->format('h:i A') }}</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        <!-- Estado de Pago -->
        <div class="resumen-box">
            <div class="resumen-row">
                <span>Estado de la Reserva:</span>
                <span style="font-weight: 700; text-transform: uppercase;">{{ $reserva->estado_reserva }}</span>
            </div>
            <div class="resumen-row total">
                <span>Estado de Pago:</span>
                <span class="estado-pago {{ $reserva->estado_pago === 'pagada' ? 'estado-pagada' : 'estado-pendiente' }}">
                    {{ $reserva->estado_pago === 'pagada' ? '✓ PAGADA' : '⏳ PENDIENTE' }}
                </span>
            </div>
        </div>

        <!-- Observaciones -->
        @if($reserva->observaciones)
            <div class="observaciones">
                <h4>Observaciones</h4>
                <p>{{ $reserva->observaciones }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="factura-footer">
            <div class="gracias">¡Gracias por su reserva!</div>
            <p>Este documento es un comprobante de reserva generado automáticamente.</p>
            <p>ReservaGourmet - Sistema de Gestión de Reservas · {{ now()->format('Y') }}</p>
        </div>
    </div>
</body>
</html>

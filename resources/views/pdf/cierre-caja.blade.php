<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Cierre de Caja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .report-title {
            font-size: 16px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            width: 50%;
        }
        .info-value {
            width: 50%;
            text-align: right;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }
        .total-row {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 50px;
        }
        .difference {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .difference.positive {
            background-color: #e8f5e8;
            color: #2d5a2d;
        }
        .difference.negative {
            background-color: #f5e8e8;
            color: #5a2d2d;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .comparison-table th,
        .comparison-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        .comparison-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .notes-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007cba;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="report-title">Reporte de Cierre de Caja</div>
    </div>

    {{-- Información General --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Fecha de Apertura:</span>
            <span class="info-value">{{ $apertura->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de Cierre:</span>
            <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Usuario:</span>
            <span class="info-value">{{ $apertura->user->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Caja Nº:</span>
            <span class="info-value">{{ $apertura->id }}</span>
        </div>
    </div>

    {{-- Resumen de Ventas --}}
    <div class="section-title">Resumen de Ventas del Sistema</div>
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Monto Inicial:</span>
            <span class="info-value">L {{ number_format($apertura->monto_inicial, 2) }}</span>
        </div>
        
        @foreach ($reporteSistema as $metodo => $total)
            <div class="info-row">
                <span class="info-label">Ventas {{ $metodo }}:</span>
                <span class="info-value">L {{ number_format($total, 2) }}</span>
            </div>
        @endforeach
        
        <div class="info-row total-row">
            <span class="info-label">Total Esperado en Efectivo:</span>
            <span class="info-value">L {{ number_format($totalEnCajaEsperado, 2) }}</span>
        </div>
    </div>

    {{-- Comparación Conteo vs Sistema --}}
    <div class="section-title">Comparación: Sistema vs Conteo Manual</div>
    <table class="comparison-table">
        <thead>
            <tr>
                <th style="text-align: left;">Método de Pago</th>
                <th>Sistema</th>
                <th>Contado</th>
                <th>Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diferencias as $metodo => $detalle)
                <tr>
                    <td style="text-align: left;">{{ $metodo }}</td>
                    <td>L {{ number_format($detalle['sistema'], 2) }}</td>
                    <td>L {{ number_format($detalle['contado'], 2) }}</td>
                    <td style="color: {{ $detalle['diferencia'] >= 0 ? '#2d5a2d' : '#5a2d2d' }};">
                        @if($detalle['diferencia'] > 0)
                            +L {{ number_format($detalle['diferencia'], 2) }}
                        @elseif($detalle['diferencia'] < 0)
                            L {{ number_format($detalle['diferencia'], 2) }}
                        @else
                            L 0.00
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Resumen de Diferencias --}}
    @php
        $totalDiferencias = collect($diferencias)->sum('diferencia');
        $estadoCierre = abs($totalDiferencias) <= 1 ? 'Correcto' : ($totalDiferencias > 0 ? 'Sobrante' : 'Faltante');
    @endphp

    <div class="section-title">Estado Final del Cierre</div>
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Diferencia Total:</span>
            <span class="info-value" style="color: {{ $totalDiferencias >= 0 ? '#2d5a2d' : '#5a2d2d' }};">
                @if($totalDiferencias > 0)
                    +L {{ number_format($totalDiferencias, 2) }} ({{ $estadoCierre }})
                @elseif($totalDiferencias < 0)
                    L {{ number_format($totalDiferencias, 2) }} ({{ $estadoCierre }})
                @else
                    L 0.00 ({{ $estadoCierre }})
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado del Cierre:</span>
            <span class="info-value">
                @if(abs($totalDiferencias) <= 1)
                    ✓ Aprobado
                @else
                    ⚠ Requiere Revisión
                @endif
            </span>
        </div>
    </div>

    {{-- Notas del Cierre --}}
    @if(!empty($notasCierre))
        <div class="section-title">Notas del Cierre</div>
        <div class="notes-section">
            <p>{{ $notasCierre }}</p>
        </div>
    @endif

    {{-- Firmas --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Cajero</div>
            <div>{{ $apertura->user->name ?? '' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Supervisor</div>
            <div>Firma y Sello</div>
        </div>
    </div>
</body>
</html>
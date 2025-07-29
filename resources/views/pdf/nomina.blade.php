<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nomina - {{ $empresa->nombre ?? 'Empresa' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            padding: 0;
            font-size: 24px;
            color: #333;
        }
        .info-general {
            margin-bottom: 20px;
        }
        .info-general table {
            width: 100%;
        }
        .info-general td {
            padding: 5px;
        }
        table.datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.datos th {
            background-color: #f3f3f3;
            text-align: left;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table.datos td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .deducciones-list, .percepciones-list {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $empresa->nombre ?? 'Empresa' }}</h1>
            <p>Nomina del mes de {{ $mesNombre }} {{ $año }}</p>
            @if($descripcion)
                <p><em>{{ htmlentities($descripcion, ENT_QUOTES, 'UTF-8') }}</em></p>
            @endif
        </div>

        <div class="info-general">
            <table>
                <tr>
                    <td><strong>Fecha de emisión:</strong></td>
                    <td>{{ date('d/m/Y') }}</td>
                    <td><strong>Periodo:</strong></td>
                    <td>{{ $mesNombre }} {{ $año }}</td>
                </tr>
            </table>
        </div>

        <table class="datos">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Salario Base</th>
                    <th>Deducciones</th>
                    <th>Percepciones</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneral = 0; @endphp
                @foreach($empleados as $empleado)
                    @php $totalGeneral += $empleado['total']; @endphp
                    <tr>
                        <td>{{ htmlentities($empleado['nombre'], ENT_QUOTES, 'UTF-8') }}</td>
                        <td class="text-right">L. {{ number_format($empleado['salario'], 2) }}</td>
                        <td>
                            <ul class="deducciones-list">
                                @foreach($empleado['deduccionesArray'] as $deduccion)
                                    @if($deduccion['aplicada'])
                                        <li>{{ htmlentities($deduccion['nombre'], ENT_QUOTES, 'UTF-8') }} ({{ $deduccion['valorMostrado'] ?? '' }})</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <ul class="percepciones-list">
                                @foreach($empleado['percepcionesArray'] as $percepcion)
                                    @if($percepcion['aplicada'])
                                        <li>{{ htmlentities($percepcion['nombre'], ENT_QUOTES, 'UTF-8') }} ({{ $percepcion['valorMostrado'] ?? '' }})</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-right">L. {{ number_format($empleado['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TOTAL NOMINA:</strong></td>
                    <td class="text-right"><strong>L. {{ number_format($totalGeneral, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>

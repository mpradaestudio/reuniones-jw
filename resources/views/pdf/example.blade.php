<!DOCTYPE html>
{{--
    Plantilla de ejemplo para reportes PDF (Laravel DomPDF).
    Convención del proyecto: todas las plantillas de PDF viven en resources/views/pdf/
    y se personalizan mediante HTML y CSS. No usar librerías de pago.

    Uso (ejemplo, a implementar por módulo):
        $pdf = Pdf::loadView('pdf.example', ['congregation' => $congregation]);
        return $pdf->download('reporte.pdf');
--}}
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ config('app.name', 'Reuniones JW') }}</h1>
    <p class="muted">Reporte de ejemplo — {{ now()->format('d/m/Y H:i') }}</p>

    @isset($congregation)
        <table>
            <tr><th>Congregación</th><td>{{ $congregation->nombre }}</td></tr>
            <tr><th>Subdominio</th><td>{{ $congregation->subdominio }}</td></tr>
            <tr><th>Estado</th><td>{{ $congregation->estado->label() }}</td></tr>
        </table>
    @endisset
</body>
</html>

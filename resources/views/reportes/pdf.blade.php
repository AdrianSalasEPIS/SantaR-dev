<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reporte['titulo'] }}</title>
    <style>
        @page { margin: 26mm 14mm 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #2e1a1f; }
        header { position: fixed; top: -20mm; left: 0; right: 0; height: 16mm; border-bottom: 2px solid #b8904a; }
        header .marca { font-size: 17px; font-weight: bold; color: #6d1a2b; }
        header .sub { font-size: 9px; color: #7a6560; }
        footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 8px; color: #7a6560; border-top: 1px solid #e4d6c1; padding-top: 3px; }
        footer .pagina:after { content: counter(page); }
        h1 { font-size: 14px; color: #6d1a2b; margin: 0 0 2px; }
        .periodo { color: #7a6560; margin-bottom: 8px; }
        .resumen { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .resumen td { background: #faf6ef; border: 1px solid #e4d6c1; padding: 5px 7px; }
        .resumen .valor { font-size: 13px; font-weight: bold; color: #6d1a2b; }
        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th { background: #6d1a2b; color: #fff; text-align: left; padding: 5px; font-size: 8.5px; text-transform: uppercase; }
        table.datos td { border-bottom: 1px solid #e4d6c1; padding: 4px 5px; vertical-align: top; }
        table.datos tr:nth-child(even) td { background: #faf6ef; }
        .vacio { text-align: center; color: #7a6560; padding: 20px; }
    </style>
</head>
<body>
<header>
    <div class="marca">SantaR</div>
    <div class="sub">Santa Rita VIP «Fina Herencia» · Sede céntrica · Tacna</div>
</header>
<footer>
    Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nombre }} · SantaR · Página <span class="pagina"></span>
</footer>

<h1>{{ $reporte['titulo'] }}</h1>
<div class="periodo">{{ $reporte['periodo'] }}{{ $reporte['categoria'] ? ' · Categoría: '.$reporte['categoria'] : '' }}</div>

<table class="resumen">
    <tr>
        @foreach ($reporte['resumen'] as $etiqueta => $valor)
            <td>{{ $etiqueta }}<br><span class="valor">{{ $valor }}</span></td>
        @endforeach
    </tr>
</table>

<table class="datos">
    <thead><tr>@foreach ($reporte['columnas'] as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($reporte['filas'] as $fila)
        <tr>@foreach ($fila as $celda)<td>{{ $celda }}</td>@endforeach</tr>
    @empty
        <tr><td colspan="{{ count($reporte['columnas']) }}" class="vacio">No hay datos para el período y los filtros elegidos.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>

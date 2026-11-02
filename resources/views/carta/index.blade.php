<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2b0a14">
    <meta name="description" content="Carta de vinos, licores, snacks y bebidas de Santa Rita VIP «Fina Herencia», sede céntrica de Tacna.">
    <title>Carta · Santa Rita VIP «Fina Herencia»</title>
    <link rel="icon" href="{{ asset('img/santar-logo.svg') }}" type="image/svg+xml">
    @vite(['resources/js/carta.js'])
</head>
<body>
<header class="carta-cabecera">
    <img src="{{ asset('img/santar-logo.svg') }}" alt="" class="carta-logo" width="76" height="76">
    <p class="carta-sede">Santa Rita VIP</p>
    <h1>Fina Herencia</h1>
    <p class="carta-lema">Nuestra carta · Sede céntrica · Tacna</p>
</header>

{{-- RF-23 / Ley N.° 28681: advertencias visibles sobre bebidas alcohólicas. --}}
<aside class="carta-advertencia" role="note" aria-label="Advertencias legales">
    <strong>PROHIBIDA LA VENTA DE BEBIDAS ALCOHÓLICAS A MENORES DE 18 AÑOS</strong>
    <span>TOMAR BEBIDAS ALCOHÓLICAS EN EXCESO ES DAÑINO</span>
</aside>

{{-- RF-22: una sola página desplazable, con títulos por categoría y sin buscador, filtros ni botones. --}}
<main class="carta-contenido">
    @forelse ($categorias as $categoria)
        <section class="carta-categoria" aria-labelledby="categoria-{{ $categoria->id }}">
            <h2 id="categoria-{{ $categoria->id }}">{{ $categoria->nombre }}</h2>
            <ul class="carta-lista">
                @foreach ($productos[$categoria->id] as $p)
                    <li class="carta-producto {{ $p->estaAgotado() ? 'agotado' : '' }}">
                        @if ($p->imagenUrl())
                            <img src="{{ $p->imagenUrl() }}" alt="{{ $p->nombre }}" loading="lazy" decoding="async" width="96" height="96">
                        @else
                            <span class="carta-sin-imagen" aria-hidden="true"></span>
                        @endif
                        <div class="carta-detalle">
                            <h3>{{ $p->nombre }}</h3>
                            @if ($p->caracteristicas)<p>{{ $p->caracteristicas }}</p>@endif
                            {{-- RF-21: solo los productos sin stock muestran «Agotado»; los disponibles no llevan ninguna marca. --}}
                            @if ($p->estaAgotado())<em class="carta-agotado">Agotado</em>@endif
                        </div>
                        <div class="carta-precio">
                            {{-- RN-15 y RN-16: precio total en soles; nunca el stock exacto. --}}
                            <span>S/ {{ number_format((float) $p->precio, 2) }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="carta-vacia">Estamos actualizando nuestra carta. Consulte con nuestro personal.</p>
    @endforelse
</main>

<footer class="carta-pie">
    <p>Precios en soles (S/), incluyen impuestos. La disponibilidad se actualiza en tiempo real.</p>
    <p class="carta-advertencia-pie">Prohibida la venta de bebidas alcohólicas a menores de 18 años · Tomar bebidas alcohólicas en exceso es dañino</p>
    <p class="carta-firma">Santa Rita VIP «Fina Herencia» · Tacna · {{ now()->year }}</p>
</footer>
</body>
</html>

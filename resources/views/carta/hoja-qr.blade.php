<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>QR de la carta · Santa Rita VIP</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #2b0a14; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { width: 50%; height: 128mm; text-align: center; vertical-align: middle; border: 1px dashed #b8904a; padding: 6mm; }
        .local { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #8c2338; }
        .marca { font-size: 20px; font-weight: bold; color: #6d1a2b; margin: 1mm 0 3mm; }
        .qr { width: 62mm; height: 62mm; }
        .llamado { font-size: 14px; font-weight: bold; margin-top: 3mm; }
        .ayuda { font-size: 9px; color: #6e5953; margin-top: 1mm; }
        .ley { font-size: 6.5px; color: #4a2c00; margin-top: 4mm; text-transform: uppercase; line-height: 1.4; }
    </style>
</head>
<body>
{{-- RF-24 / RN-17: copias del mismo QR para recortar y colocar en las mesas. --}}
<table>
    @for ($fila = 0; $fila < 2; $fila++)
        <tr>
            @for ($col = 0; $col < 2; $col++)
                <td>
                    <div class="local">Santa Rita VIP</div>
                    <div class="marca">Fina Herencia</div>
                    <img class="qr" src="{{ $qr }}" alt="QR">
                    <div class="llamado">Escanea y mira nuestra carta</div>
                    <div class="ayuda">Apunta la cámara de tu celular al código</div>
                    <div class="ley">Prohibida la venta de bebidas alcohólicas a menores de 18 años<br>Tomar bebidas alcohólicas en exceso es dañino</div>
                </td>
            @endfor
        </tr>
    @endfor
</table>
</body>
</html>

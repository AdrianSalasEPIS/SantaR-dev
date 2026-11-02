<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * RF-08 y RNF-03: guarda la imagen de un producto optimizada para la carta digital.
 * Corrige la orientación de las fotos tomadas con celular, la reduce a un máximo de
 * 800 px y la guarda como JPEG comprimido (también elimina los metadatos EXIF).
 * Si la extensión GD no estuviera disponible, guarda el archivo original.
 */
class ImagenProducto
{
    private const LADO_MAXIMO = 800;

    private const CALIDAD_JPEG = 82;

    private const CARPETA = 'productos';

    public static function guardar(UploadedFile $archivo): string
    {
        $ruta = self::CARPETA.'/'.Str::uuid()->toString().'.jpg';

        try {
            $jpeg = self::optimizar($archivo);
        } catch (Throwable) {
            $jpeg = null;
        }

        if ($jpeg === null) {
            return $archivo->store(self::CARPETA, 'public');
        }

        Storage::disk('public')->put($ruta, $jpeg);

        return $ruta;
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, self::CARPETA.'/')) {
            Storage::disk('public')->delete($ruta);
        }
    }

    private static function optimizar(UploadedFile $archivo): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $original = imagecreatefromstring((string) file_get_contents($archivo->getRealPath()));
        if ($original === false) {
            return null;
        }

        $original = self::corregirOrientacion($original, $archivo);

        $ancho = imagesx($original);
        $alto = imagesy($original);
        $escala = min(1, self::LADO_MAXIMO / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        // Fondo blanco para las imágenes PNG con transparencia.
        $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));
        imagecopyresampled($lienzo, $original, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        imageinterlace($lienzo, true); // JPEG progresivo: se muestra antes en conexiones lentas
        imagejpeg($lienzo, null, self::CALIDAD_JPEG);

        return (string) ob_get_clean();
    }

    /** Las cámaras de celular guardan la rotación en EXIF; se aplica para que la foto no salga girada. */
    private static function corregirOrientacion(\GdImage $imagen, UploadedFile $archivo): \GdImage
    {
        if (! function_exists('exif_read_data') || $archivo->getMimeType() !== 'image/jpeg') {
            return $imagen;
        }

        $exif = @exif_read_data($archivo->getRealPath());
        $orientacion = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        return match ((int) $orientacion) {
            3 => imagerotate($imagen, 180, 0),
            6 => imagerotate($imagen, -90, 0),
            8 => imagerotate($imagen, 90, 0),
            default => $imagen,
        };
    }
}

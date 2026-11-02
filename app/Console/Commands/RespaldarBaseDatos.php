<?php

namespace App\Console\Commands;

use App\Models\Bitacora;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Throwable;
use ZipArchive;

/**
 * RNF-10 (SAD, Figura 25): respaldo automático de la base de datos.
 *  - Diario (02:00): volcado SQL comprimido en storage/app/private/backups; se conservan 7 días.
 *  - Semanal (domingo 03:00, --semanal): además empaqueta las imágenes de los productos en un ZIP
 *    para que el soporte técnico lo copie a Google One. Se conservan 4 semanas.
 * Se genera en PHP (sin mysqldump) porque muchos hostings compartidos no permiten ejecutar programas.
 * Restauración: gunzip -c archivo.sql.gz | mysql -u USUARIO -p BASE
 */
class RespaldarBaseDatos extends Command
{
    protected $signature = 'santar:respaldar {--semanal : Incluye las imágenes de los productos en un archivo ZIP}';

    protected $description = 'Genera el respaldo de la base de datos de SantaR (RNF-10)';

    private const CARPETA = 'backups';

    private const DIAS_DIARIOS = 7;

    private const SEMANAS = 4;

    public function handle(): int
    {
        $disco = Storage::disk('local');
        $fecha = now()->format('Ymd-His');
        $semanal = (bool) $this->option('semanal');

        try {
            $sql = $this->volcarBaseDatos();
            $nombre = self::CARPETA.'/santar-'.$fecha.'.sql.gz';
            $disco->put($nombre, gzencode($sql, 9));

            $detalle = basename($nombre).' ('.number_format($disco->size($nombre) / 1024, 1).' KB)';

            if ($semanal) {
                $zip = $this->empaquetarSemanal($disco->path($nombre), $fecha);
                $detalle .= ' · copia semanal '.basename($zip);
            }

            $this->limpiarAntiguos();
            Bitacora::registrar('sistema', $semanal ? 'respaldo semanal' : 'respaldo diario', $detalle);
            $this->info("Respaldo generado: {$detalle}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            Bitacora::registrar('sistema', 'respaldo fallido', $e->getMessage());
            $this->error('No se pudo generar el respaldo: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /** Estructura y datos de todas las tablas, como sentencias SQL. */
    private function volcarBaseDatos(): string
    {
        $pdo = DB::connection()->getPdo();
        $base = DB::connection()->getDatabaseName();

        $salida = "-- Respaldo de SantaR · base {$base} · ".now()->format('d/m/Y H:i:s')."\n"
            ."SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        $tablas = $pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')->fetchAll(PDO::FETCH_NUM);

        foreach (array_column($tablas, 0) as $tabla) {
            $crear = $pdo->query("SHOW CREATE TABLE `{$tabla}`")->fetch(PDO::FETCH_NUM)[1];
            $salida .= "DROP TABLE IF EXISTS `{$tabla}`;\n{$crear};\n\n";

            $filas = $pdo->query("SELECT * FROM `{$tabla}`", PDO::FETCH_ASSOC);
            $lote = [];
            foreach ($filas as $fila) {
                $lote[] = '('.implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $fila)).')';
                if (count($lote) === 200) {
                    $salida .= $this->insertar($tabla, array_keys($fila), $lote);
                    $lote = [];
                }
            }
            if ($lote) {
                $salida .= $this->insertar($tabla, array_keys($fila), $lote);
            }
            $salida .= "\n";
        }

        return $salida."SET FOREIGN_KEY_CHECKS=1;\n";
    }

    private function insertar(string $tabla, array $columnas, array $lote): string
    {
        $cols = implode(',', array_map(fn ($c) => "`{$c}`", $columnas));

        return "INSERT INTO `{$tabla}` ({$cols}) VALUES\n".implode(",\n", $lote).";\n";
    }

    /** Copia semanal: base de datos + imágenes de productos en un solo ZIP. */
    private function empaquetarSemanal(string $rutaSql, string $fecha): string
    {
        $disco = Storage::disk('local');
        $nombre = self::CARPETA.'/semanal/santar-semanal-'.$fecha.'.zip';
        $disco->makeDirectory(self::CARPETA.'/semanal');

        $zip = new ZipArchive;
        if ($zip->open($disco->path($nombre), ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP de la copia semanal.');
        }
        $zip->addFile($rutaSql, basename($rutaSql));

        $publico = Storage::disk('public');
        foreach ($publico->allFiles('productos') as $imagen) {
            $zip->addFile($publico->path($imagen), $imagen);
        }
        $zip->close();

        return $nombre;
    }

    private function limpiarAntiguos(): void
    {
        $disco = Storage::disk('local');
        $limiteDiario = now()->subDays(self::DIAS_DIARIOS)->getTimestamp();
        $limiteSemanal = now()->subWeeks(self::SEMANAS)->getTimestamp();

        foreach ($disco->files(self::CARPETA) as $archivo) {
            if ($disco->lastModified($archivo) < $limiteDiario) {
                $disco->delete($archivo);
            }
        }
        foreach ($disco->files(self::CARPETA.'/semanal') as $archivo) {
            if ($disco->lastModified($archivo) < $limiteSemanal) {
                $disco->delete($archivo);
            }
        }
    }
}

<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas programadas (SAD, Figura 25 y Tabla 11)
|--------------------------------------------------------------------------
| En el hosting, el cron ejecuta cada minuto:  php artisan schedule:run
| Los horarios usan la zona horaria de la aplicación (America/Lima, RN-19).
*/

// RNF-10: respaldo diario de la base de datos (se conservan 7 días).
Schedule::command('santar:respaldar')->dailyAt('02:00')->withoutOverlapping();

// RNF-10: copia semanal con base de datos e imágenes, para trasladarla a Google One.
Schedule::command('santar:respaldar --semanal')->weeklyOn(0, '03:00')->withoutOverlapping();

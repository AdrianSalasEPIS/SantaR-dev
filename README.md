# SantaR · Inventario y carta digital de Santa Rita VIP «Fina Herencia»

Sistema web del equipo **Nexora** para la sede céntrica de Santa Rita VIP «Fina Herencia» (Tacna), desarrollado
en el curso **Gestión de Proyectos de TI** de la Universidad Privada de Tacna (docente: Ing. Martha Paredes Vignola).

Se basa en los documentos del proyecto: Informe de Factibilidad (FD01), Documento de Visión (FD02),
Especificación de Requerimientos (SRS, FD03) y Documento de Arquitectura (SAD, FD04). Los códigos RF, RNF, RN y CU
del código fuente corresponden a esos documentos.

## Equipo y reparto de requerimientos funcionales

| Integrante | Requerimientos | Alcance |
|---|---|---|
| Adrian Fabricio Salas Acuña (jefe de proyecto) | RF-01 a RF-08 · RNF-10 | Seguridad y usuarios, bitácora, categorías, productos e implementos, respaldos |
| Keyla Noelia López Ojeda | RF-09 a RF-16 | Entradas, salidas, ajustes, abastecimientos, incidencias, stock, existencias y alertas |
| Juan Carlos Calderón Rodríguez | RF-17 a RF-24 | Kardex, reportes PDF/Excel, carta digital pública y código QR |

## Estado

| Requerimiento | Descripción | Estado |
|---|---|---|
| RF-01 | Iniciar sesión (bloqueo de 15 min tras 5 intentos, RN-05) | ✅ |
| RF-02 | Cerrar sesión (a pedido o tras 15 min de inactividad) | ✅ |
| RF-03 | Gestionar usuarios (solo el administrador; sin registro libre) | ✅ |
| RF-04 | Cambiar contraseña (validando la actual) | ✅ |
| RF-05 | Registrar bitácora automática (inmutable) | ✅ |
| RF-06 | Consultar bitácora con filtros | ✅ |
| RF-07 | Gestionar categorías y su orden en la carta | ✅ |
| RF-08 | Gestionar productos e implementos con imagen optimizada | ✅ |
| RNF-10 | Respaldo diario y copia semanal (comando `santar:respaldar`) | ✅ |
| RF-09 | Registrar entradas (compra local, devolución u otro) | ✅ |
| RF-10 | Registrar salidas por venta, merma o consumo interno (sin superar el stock, RN-07) | ✅ |
| RF-11 | Registrar ajustes de inventario según conteo físico (solo administrador) | ✅ |
| RF-12 | Registrar abastecimientos recibidos de la bodega principal | ✅ |
| RF-13 | Registrar pérdidas, roturas e incidencias | ✅ |
| RF-14 | Actualizar el stock automáticamente (transacción y bloqueo de fila) | ✅ |
| RF-15 | Consultar existencias con búsqueda y filtros | ✅ |
| RF-16 | Mostrar alertas de stock mínimo y productos agotados | ✅ |
| RF-17 | Consultar kardex de un producto con filtro de fechas | ✅ |
| RF-18 | Generar reportes de existencias, movimientos, abastecimientos e incidencias | ✅ |
| RF-19 | Exportar reportes a PDF (DomPDF) y Excel (Laravel Excel) | ✅ |
| RF-20 | Carta digital pública por categorías, sin iniciar sesión (`/carta`) | ✅ |
| RF-21 | Mostrar «Agotado» en los productos sin stock (sin revelar la cantidad) | ✅ |
| RF-22 | Carta en una sola página desplazable, sin buscador ni filtros | ✅ |
| RF-23 | Advertencias de la Ley N.° 28681 en la carta | ✅ |
| RF-24 | Generar y descargar el código QR único de la carta (SVG y hoja PDF para mesas) | ✅ |

## Tecnologías

PHP 8.4 · Laravel 12 · MySQL 8.4 LTS (compatible con MariaDB) · Blade + Bootstrap 5.3.8 · Vite 8 · Node.js 24 ·
Composer 2.10. Arquitectura monolítica modular MVC (SAD v1.0).

## Puesta en marcha (desarrollo)

Requisitos: PHP 8.4 con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip` e `intl`;
Composer 2; Node.js 24; MySQL 8.4.

```bash
composer install
npm ci
cp .env.example .env          # completar DB_* y ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed    # roles, cuenta del encargado, categorías y catálogo de demostración (solo local)
php artisan storage:link
npm run build
php artisan serve             # http://127.0.0.1:8000
```

Cuenta inicial: `ADMIN_CORREO` / `ADMIN_PASSWORD` del archivo `.env`, con rol **Administrador**.

### Pruebas automáticas

```bash
php artisan test              # usa la base de datos bd_santar_test
vendor/bin/pint --test        # estilo de código (RNF-13)
```

## Despliegue en hosting compartido

El hosting no necesita Node.js: la carpeta `public/build` se compila (`npm run build`) y se versiona antes de cada push.

```bash
git clone https://github.com/AdrianSalasEPIS/SantaR-dev.git santar && cd santar
composer install --no-dev --optimize-autoloader
cp .env.example .env          # ver valores de producción abajo
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Valores de `.env` en producción: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`,
`SESSION_SECURE_COOKIE=true`, `FORCE_HTTPS=true`, `TRUSTED_PROXIES=*`, `LOG_STACK=daily`, `LOG_LEVEL=warning`
y una contraseña segura en `ADMIN_PASSWORD`.

- La raíz web del dominio debe apuntar a la carpeta **`public/`**.
- Tarea programada (cron) cada minuto: `php /ruta/a/santar/artisan schedule:run`
  - Respaldo diario a las 02:00 y copia semanal (base de datos + imágenes) los domingos a las 03:00, en
    `storage/app/private/backups` (RNF-10).
  - Restaurar un respaldo: `gunzip -c santar-AAAAMMDD-HHMMSS.sql.gz | mysql -u USUARIO -p BASE`

Actualización de una versión: `git pull && composer install --no-dev -o && php artisan migrate --force && php artisan optimize`.

## Seguridad (OWASP Top 10 / RNF-05 a RNF-09)

Contraseñas con bcrypt · control de acceso por roles en cada ruta · protección CSRF · consultas parametrizadas
(Eloquent) · escape de datos en las vistas · bloqueo por intentos fallidos · cierre por inactividad · HTTPS forzado y
cabeceras de seguridad en producción · borrado lógico (las cuentas, categorías y productos se desactivan) ·
bitácora inmutable · el archivo `.env` nunca se versiona.

@include('errors.plantilla', ['codigo' => 403, 'titulo' => 'Acceso denegado', 'mensaje' => $exception->getMessage() ?: 'No tiene permiso para acceder a esta opción.'])

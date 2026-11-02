<?php

namespace App\Exceptions;

use DomainException;

/** Violación de una regla de negocio del SRS; se muestra al usuario como error del formulario. */
class ReglaNegocioException extends DomainException
{
    public function __construct(string $message, public readonly string $campo = 'cantidad')
    {
        parent::__construct($message);
    }
}

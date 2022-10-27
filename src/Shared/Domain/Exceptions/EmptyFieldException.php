<?php

namespace App\Shared\Domain\Exceptions;

use Exception;

class EmptyFieldException extends \Exception
{
    public function __construct($field)
    {
        throw new Exception("Field can't be empty: " . $field, 400);
    }
}
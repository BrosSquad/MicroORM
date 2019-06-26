<?php


namespace BrosSquad\MicroORM\Exceptions;


use Error;
use Throwable;

class MethodNotFound extends Error
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php


namespace Dusan\PhpMvc\Database\Exceptions;


use Error;
use Throwable;

class MethodNotFound extends Error
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php


namespace Dusan\PhpMvc\Database\Traits;


use Closure;

trait Lockable
{
    public final function lock(Closure $fn)
    {
        $this->lock = true;
        $value = $fn();
        $this->lock = false;
        return $value;
    }
}

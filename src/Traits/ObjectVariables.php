<?php

namespace Dusan\PhpMvc\Database\Traits;



trait ObjectVariables
{
    /**
     * @return string
     */
    protected function getClass(): string
    {
        return get_called_class();
    }


    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return get_object_vars($this);
    }
}

<?php

namespace Dusan\PhpMvc\Database;


interface BindFromDatabase
{
    /**
     * @param $value
     * @return mixed
     */
    public function bind($value);
}
<?php

namespace BrosSquad\MicroORM;


interface BindFromDatabase
{
    /**
     * @param $value
     * @return mixed
     */
    public function bind($value);
}

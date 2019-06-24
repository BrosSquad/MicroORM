<?php


use Dusan\PhpMvc\Collections\Set;
use Dusan\MicroORM\BindToDatabase;
use Dusan\MicroORM\Driver;

class CustomBind implements BindToDatabase
{

    /**
     * Binds the custom type to the underlining SQL(PDO type)
     * Key in the Set must be PDO::PARAM_*
     * Value in Set must be interpreted $value
     *
     * @param mixed $value
     *
     * @return Set
     */
    public function bind($value): Set
    {

    }
}



// e.g.
// First parameter is the key by which values will be searched
// Second parameter is the implementation of the BindToDatabase interface
Driver::setCustomTypes('your-type', new CustomBind());

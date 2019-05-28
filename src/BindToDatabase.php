<?php
namespace Dusan\PhpMvc\Database;


use Dusan\PhpMvc\Collections\Set;

interface BindToDatabase
{
    /**
     * Binds the custom type to the underlining SQL(PDO type)
     * Key in the Set must be PDO::PARAM_*
     * Value in Set must be interpreted $value
     * @param mixed $value
     * @return Set
     */
    public function bind($value): Set;
}

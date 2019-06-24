<?php

namespace Dusan\MicroORM\CustomBindings;

use DateTimeInterface;
use Dusan\PhpMvc\Collections\Set;
use Dusan\MicroORM\BindToDatabase;
use PDO;
use TypeError;

class DateTimeBinding implements BindToDatabase
{
    /**
     * Binds the custom model to the underlining SQL(PDO type)
     * @param $value
     * @return mixed
     * @throws \TypeError
     */
    public function bind($value): Set
    {
        if(!$value instanceof DateTimeInterface) {
            throw new TypeError('value must be of type DateTimeInterface');
        }
        return $value === null ?
            new Set(PDO::PARAM_NULL, $value) :
            new Set(PDO::PARAM_STR, $value->format('Y-m-d H:i:s'));
    }
}

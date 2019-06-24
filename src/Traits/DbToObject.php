<?php


namespace Dusan\MicroORM\Traits;


use TypeError;

trait DbToObject
{

    public function bindFromPdoToObject(&$property, $value)
    {
        if (PHP_MAJOR_VERSION > 7 && PHP_MINOR_VERSION > 4) {
            $type = gettype($property);
            $valueType = gettype($value);
            switch (strtolower($type)) {
                case 'string':
                    $property = (string)$value;
                    break;
                case 'integer':
                    if ($valueType !== 'integer') {
                        throw new TypeError('Value is not integer');
                    }
                    $property = (integer)$value;
                    break;
                case 'double':
                    if ($valueType !== 'double') {
                        throw new TypeError('Value is not double');
                    }
                    $property = (double)$value;
                    break;
                case 'object':
                case 'null':
                default:
                    /** @var \Dusan\MicroORM\BindFromDatabase $bind */
                    $bind = self::$customBind[get_class($property ?? 0) !== false ?: ''] ?? NULL;

                    if ($bind === NULL) {
                        $property = $value;
                    } else {
                        $property = $bind->bind($value);
                    }
                    break;
            }
        }
        else {
            // TODO: add annotations parser
            $property = $value;
        }

    }
}

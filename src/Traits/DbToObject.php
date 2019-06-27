<?php


namespace BrosSquad\MicroORM\Traits;


use BrosSquad\MicroORM\Model;
use TypeError;

trait DbToObject
{

    public function bindFromPdoToObject(Model $model, $key, $value)
    {
        if (PHP_MAJOR_VERSION > 7 && PHP_MINOR_VERSION > 4) {
            $type = gettype($model->{$key});
            $valueType = gettype($value);
            switch (strtolower($type)) {
                case 'string':
                    $model->{$key} = (string)$value;
                    break;
                case 'integer':
                    if ($valueType !== 'integer') {
                        throw new TypeError('Value is not integer');
                    }
                    $model->{$key} = (integer)$value;
                    break;
                case 'double':
                    if ($valueType !== 'double') {
                        throw new TypeError('Value is not double');
                    }
                    $model->{$key} = (double)$value;
                    break;
                case 'object':
                case 'null':
                default:
                    /** @var \BrosSquad\MicroORM\BindFromDatabase $bind */
                    $bind = static::$customBind[get_class($model->{$key} ?? 0) !== false ?: ''] ?? NULL;

                    if ($bind === NULL) {
                        $model->{$key} = $value;
                    } else {
                        $model->{$key} = $bind->bind($value);
                    }
                    break;
            }
        }
        else {
            // TODO: add annotations parser
            $model->{$key} = $value;
        }

    }
}

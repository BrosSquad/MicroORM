<?php


namespace BrosSquad\MicroORM\Traits;


use BrosSquad\MicroORM\Model;

trait DbToObject
{

    public function bindFromPdoToObject(Model & $model, string $key, $value, ?string $type)
    {
        if (PHP_MAJOR_VERSION > 7 && PHP_MINOR_VERSION > 4) {
            if ($type === NULL) {
                $model->{$key} = $value;
            } else {
                switch (strtolower($type)) {
                    case 'string':
                        $model->{$key} = (string)$value;
                        break;
                    case 'integer':
//                        if ($valueType !== 'integer') {
//                            throw new TypeError('Value is not integer');
//                        }
                        $model->{$key} = (integer)$value;
                        break;
                    case 'double':
//                        if ($valueType !== 'double') {
//                            throw new TypeError('Value is not double');
//                        }
                        $model->{$key} = (double)$value;
                        break;
                    case 'object':
                        $model->{$key} = (object)$value;
                        break;
                    default:
                        /** @var \BrosSquad\MicroORM\BindFromDatabase $bind */
                        $bind = static::$customBind[$type] ?? NULL;
                        if ($bind === NULL) {
                            $model->{$key} = $value;
                        } else {
                            $model->{$key} = $bind->bind($value);
                        }
                        break;
                }
            }
        } else {
            // TODO: add annotations parser
            $model->{$key} = $value;
        }

    }
}

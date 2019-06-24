<?php


namespace Dusan\MicroORM\Traits;


use Dusan\MicroORM\BindToDatabase;
use PDO;

trait ObjectToDb
{

    /**
     * Type bindings for PDO prepared statement
     * If not type is passed type of the $value variable will be determined
     * and PDO::PARAM_* will be returned accordingly
     *
     * @param mixed    $value
     * @param null|int $optionalType
     *
     * @return int
     */
    public final function bindToPdoType(&$value, ?int $optionalType = NULL): int
    {
        if (is_null($optionalType)) {
            switch (true) {
                case is_int($value):
                    return PDO::PARAM_INT;
                case is_bool($value):
                    return PDO::PARAM_BOOL;
                case is_null($value):
                    return PDO::PARAM_NULL;
                case is_string($value):
                    if(strlen($value) > 4096)
                        return PDO::PARAM_LOB;
                    return PDO::PARAM_STR;
            }
            $type = gettype($value);
            /**
             * @var BindToDatabase $customType
             */
            $customType = static::$customTypes[$type];
            if (isset($customType)) {
                $set = $customType->bind($value);
                $value = $set->value;
                return $set->key;
            }

            return PDO::PARAM_STR;
        } else {
            return $optionalType;
        }
    }
}

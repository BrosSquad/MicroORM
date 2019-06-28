<?php


namespace BrosSquad\MicroORM\Traits;


use BrosSquad\MicroORM\BindToDatabase;
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
        if ($optionalType === NULL) {
            switch (true) {
                case is_int($value):
                    return PDO::PARAM_INT;
                case is_bool($value):
                    return PDO::PARAM_BOOL;
                case is_null($value):
                    return PDO::PARAM_NULL;
                case is_array($value):
                    $value = join(',', $value);
                    return PDO::PARAM_STR;
                case is_object($value):
                    $type = get_class($value);
                    $customType = array_key_exists($type, static::$customTypes) ? static::$customTypes[$type] : NULL;
                    if ($customType !== NULL) {
                        if(is_string($customType)) {
                            $customType = new $customType();
                        }
                        if($customType instanceof BindToDatabase) {
                            $set = $customType->bind($value);
                            $value = $set->value;
                            return $set->key;
                        }
                    }
                    break;
                case is_string($value):
                    if (strlen($value) > 4096)
                        return PDO::PARAM_LOB;
                    return PDO::PARAM_STR;
            }
            return PDO::PARAM_STR;
        } else {
            return $optionalType;
        }
    }
}

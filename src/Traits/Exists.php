<?php


namespace BrosSquad\MicroORM\Traits;


use BrosSquad\MicroORM\Drivers\Database;
use Error;

trait Exists
{
    private function fromDb($caller, bool $value)
    {
        if (!($caller instanceof Database)) {
            throw new Error('Only Database::class is allowed to use this method!!!');
        }
        $this->modelExists = $value;
    }
}

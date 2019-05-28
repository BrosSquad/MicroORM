<?php


namespace Dusan\PhpMvc\Database\Traits;


use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Exception;

trait DateTimeParse
{
    public function parseCarbon($value)
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        } else {
            $dateTime = CarbonImmutable::createFromFormat($this->format, $value);
            if (!$dateTime) {
                throw new Exception('DateTime could not be created');
            }
            return $dateTime;
        }
    }
}

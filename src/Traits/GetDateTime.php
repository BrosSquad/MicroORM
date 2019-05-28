<?php


namespace Dusan\PhpMvc\Database\Traits;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Dusan\PhpMvc\Models\Traits\DateTimeParse;
use Exception;
use PDO;

trait GetDateTime
{
    protected $format = 'Y-m-d H:i:s';

    protected final function getDateTime($name)
    {
        if ($this->{$name} instanceof CarbonInterface) {
            $this->memberTypeBindings[$name] = PDO::PARAM_STR;
            return $this->{$name}->format($this->format);
        } else if ($this->{$name} === null) {
            $this->memberTypeBindings[$name] = PDO::PARAM_NULL;
            return 'NULL';
        }
        return $this->{$name};
    }
}

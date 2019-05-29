<?php
/**
 * Copyright (c) 2019. Dusan Malusev
 */

namespace Dusan\PhpMvc\Database;


use Dusan\PhpMvc\Database\Traits\MemberWithDash;
use Dusan\PhpMvc\Database\Exceptions\PropertyNotFound;

abstract class AbstractModel
{
    use MemberWithDash;

    /**
     * @param string $name
     *
     * @return mixed
     * @throws \Dusan\PhpMvc\Database\Exceptions\PropertyNotFound
     */
    public function __get(string $name)
    {
        $modified = $this->memberWithDash($name);
        if (method_exists($this, 'get' . $modified)) {
            return $this->{'get' . $modified}();
        } else if (method_exists($this, 'is' . $modified)) {
            return $this->{'is' . $modified}();
        } else if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new PropertyNotFound();
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws \Dusan\PhpMvc\Database\Exceptions\PropertyNotFound
     */
    public function __set(string $name, $value)
    {
        $modified = $this->memberWithDash($name);
        if (method_exists($this, 'set' . $modified)) {
            $this->{'set' . $modified}($value);
            return;
        } else if (property_exists($this, $name)) {
            $this->{$name} = $value;
            return;
        }
        throw new PropertyNotFound();
    }
}

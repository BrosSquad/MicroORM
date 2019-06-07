<?php

use Dusan\PhpMvc\Database\ModelOLD;

class User extends ModelOLD
{
    protected $restricted = [
        'name' // Name will not be included in insert/update sql statements
    ];
    protected $password;
    protected $name; // Name field will be visible in json serializer
}

<?php

use Dusan\MicroORM\Model;

class User extends Model
{
    protected $restricted = [
        'name' // Name will not be included in insert/update sql statements
    ];
    protected $password;
    protected $name; // Name field will be visible in json serializer
}

<?php

use Dusan\PhpMvc\Database\ModelOLD;

class User extends ModelOLD
{
    protected $guarded = [
        'password' // Password will be excluded when User model is serialized in json
    ];
    protected $password;
    protected $name; // Name field will be visible in json serializer
}

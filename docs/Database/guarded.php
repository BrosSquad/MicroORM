<?php

use Dusan\PhpMvc\Database\Model;

class User extends Model
{
    protected $guarded = [
        'password' // Password will be excluded when User model is serialized in json
    ];
    protected $password;
    protected $name; // Name field will be visible in json serializer
}

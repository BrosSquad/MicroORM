<?php

namespace Dusan\PhpMvc\Tests\Database\Models;
use Dusan\PhpMvc\Database\ModelOLD;

class User extends ModelOLD
{
    protected $name;
    protected $surname;
    protected $email;
    protected $password;
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }
    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
    public function comments() {
        return $this->hashMany('comments', 'user_id');
    }
    public function role() {
        return $this->belongsTo('roles', 'role_id');
    }
}

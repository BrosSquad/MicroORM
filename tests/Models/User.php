<?php


namespace BrosSquad\MicroORM\Tests\Models;


use BrosSquad\MicroORM\Model;

class User extends Model
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $surname;

    /** @var string */
    protected $email;

    /** @var string */
    protected $password;

    /** @var \Carbon\CarbonInterface */
    protected $created_at;

    /** @var \Carbon\CarbonInterface */
    protected $updated_at;

    public function posts() {
        return $this->hashMany('posts', 'user_id');
    }
}

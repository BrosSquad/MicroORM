<?php


namespace BrosSquad\MicroORM\Tests\Models;


use BrosSquad\MicroORM\Model;

class Post extends Model
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $content;

    /** @var int */
    protected $user_id;

    /** @var \Carbon\CarbonInterface */
    protected $created_at;

    /** @var \Carbon\CarbonInterface */
    protected $updated_at;

    public function user() {
        return $this->belongsTo('users', 'user_id');
    }
}

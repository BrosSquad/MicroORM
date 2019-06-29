<?php


namespace BrosSquad\MicroORM\Tests\Models;


use BrosSquad\MicroORM\Model;

/**
 * Class Actor
 *
 * @package BrosSquad\MicroORM\Tests\Models
 */
class Actor extends Model
{
    protected const PRIMARY_KEY = 'actor_id';
    protected const UPDATED_AT = 'last_update';

    /** @var int */
    protected $actor_id;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $last_name;

    /** @var string */
    protected $last_update;

    protected static function setTable(): string
    {
        return 'actor';
    }
}

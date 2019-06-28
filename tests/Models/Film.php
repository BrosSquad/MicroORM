<?php


namespace BrosSquad\MicroORM\Tests\Models;


use BrosSquad\MicroORM\Model;

class Film extends Model
{
    protected const PRIMARY_KEY = 'film_id';
    protected const UPDATED_AT = 'last_update';

    /** @var int */
    protected $film_id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $description;

    /** @var int */
    protected $release_year;

    /** @var int */
    protected $language_id;

    /** @var int */
    protected $original_language_id;

    /** @var int */
    protected $rental_duration;

    /** @var double */
    protected $rental_rate;

    /** @var int */
    protected $length;

    /** @var double */
    protected $replacement_cost;

    /** @var string */
    protected $rating;

    /** @var array */
    protected $special_features;

    /** @var int|\DateTime */
    protected $last_update;
}

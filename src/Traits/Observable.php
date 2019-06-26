<?php


namespace BrosSquad\MicroORM\Traits;


use BrosSquad\MicroORM\Events\Observer;

trait Observable
{
    /** @var \BrosSquad\MicroORM\Events\Observer|NULL */
    protected static $observer = NULL;

    public static function setObserver(Observer $observer) {
        static::$observer = $observer;
    }
}

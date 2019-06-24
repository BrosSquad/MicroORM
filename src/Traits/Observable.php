<?php


namespace Dusan\MicroORM\Traits;


use Dusan\MicroORM\Events\Observer;

trait Observable
{
    /** @var \Dusan\MicroORM\Events\Observer|NULL */
    protected static $observer = NULL;

    public static function setObserver(Observer $observer) {
        self::$observer = $observer;
    }
}

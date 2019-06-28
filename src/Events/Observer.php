<?php

namespace BrosSquad\MicroORM\Events;


use BrosSquad\MicroORM\Model;

/**
 * Interface Observer
 *
 * @package BrosSquad\MicroORM\Events
 */
interface Observer
{
    /**
     * This method runs before model is inserted
     * @return void
     */
    public function creating(): void;

    /**
     * This method runs before model is updated
     * @return void
     */
    public function updating(): void;

    /**
     * This method runs before model is deleted
     * @return void
     */
    public function deleting(): void;

    /**
     * This method runs when model is already inserted
     *
     * @param \BrosSquad\MicroORM\Model $model
     */
    public function created(Model $model): void;

    /**
     * This method runs when model is already updated
     *
     * @param \BrosSquad\MicroORM\Model $model
     *
     * @return void
     */
    public function updated(Model $model): void;

    /**
     * This method runs when model is already deleted
     *
     * @return void
     */
    public function deleted(): void;
}

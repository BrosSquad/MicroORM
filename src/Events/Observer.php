<?php

namespace BrosSquad\MicroORM\Events;


use BrosSquad\MicroORM\DatabaseModel;

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
     * @param \BrosSquad\MicroORM\DatabaseModel $model
     */
    public function created(DatabaseModel $model): void;

    /**
     * This method runs when model is already updated
     *
     * @param \BrosSquad\MicroORM\DatabaseModel $model
     *
     * @return void
     */
    public function updated(DatabaseModel $model): void;

    /**
     * This method runs when model is already deleted
     *
     * @return void
     */
    public function deleted(): void;
}

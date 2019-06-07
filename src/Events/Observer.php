<?php

namespace Dusan\PhpMvc\Database\Events;


use Dusan\PhpMvc\Database\ModelOLD;

/**
 * Interface Observer
 *
 * @package Dusan\PhpMvc\Database\Events
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
     * @param ModelOLD $model
     */
    public function created(ModelOLD $model): void;

    /**
     * This method runs when model is already updated
     *
     * @param \Dusan\PhpMvc\Database\ModelOLD $model
     *
     * @return void
     */
    public function updated(ModelOLD $model): void;

    /**
     * This method runs when model is already deleted
     *
     * @param \Dusan\PhpMvc\Database\ModelOLD $model
     *
     * @return void
     */
    public function deleted(ModelOLD $model): void;
}

<?php

namespace Dusan\PhpMvc\Database\Events;


use Dusan\PhpMvc\Database\Model;

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
     * @param Model $model
     */
    public function created(Model $model): void;

    /**
     * This method runs when model is already updated
     *
     * @param \Dusan\PhpMvc\Database\Model $model
     *
     * @return void
     */
    public function updated(Model $model): void;

    /**
     * This method runs when model is already deleted
     *
     * @param \Dusan\PhpMvc\Database\Model $model
     *
     * @return void
     */
    public function deleted(Model $model): void;
}

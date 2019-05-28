<?php


namespace Dusan\PhpMvc\Database\Relations;

/**
 * Interface Relation
 * @api
 * @package Dusan\PhpMvc\Database\Relations
 */
interface Relation
{
    public function getRelation(): string;
    /**
     * @return mixed
     */
    public function getReferenceTable();

    /**
     * @return mixed
     */
    public function getReferenceKey();

    /**
     * @return mixed
     */
    public function getForeignKey();

    /**
     * @return mixed
     */
    public function getForeignTable();
}

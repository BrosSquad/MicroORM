<?php

namespace Dusan\PhpMvc\Database;


/**
 * Interface CustomInsert
 * This interface should be applied to Model subclasses,
 * it will indicate to the that developer wants custom insert statement for the
 * model
 *
 * @api
 * @author Dusan Malusev <dusan.998@outlook.com>
 * @example "../../docs/CustomInsert.php"
 * @package Dusan\PhpMvc\Database
 */
interface CustomInsert
{
    /**
     * SQL Insert statement
     * @return string
     */
    public function setInsert(): string;

    /**
     * Binding for the SQL Insert statement
     * represented as key value pair
     * Key => Binding in the string
     * Value => Value that will be bound in prepared statement
     * @return array
     */
    public function setInsertBindings(): array;
}

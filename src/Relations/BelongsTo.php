<?php


namespace Dusan\MicroORM\Relations;

/**
 * Class BelongsTo
 * Use this class to define the relations between models
 * if you need Many-To-Many relation, make two One-To-Many relations to the binding table
 * @example "../../../docs/Database/belongsToMany.php"
 * @package Dusan\MicroORM\Relations
 * @author Dusan Malusev<dusan.998@outlook.com>
 */
class BelongsTo extends OneToMany
{

    /**
     * Gets the part of the SQL Join statement
     * @api
     * @return string
     */
    public final function getRelation(): string
    {
        return "{$this->referenceTable}.{$this->foreignKey} = {$this->foreignTable}.{$this->referenceKey}";
    }
}

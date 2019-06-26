<?php


namespace BrosSquad\MicroORM\Relations;

/**
 * Class HasMany
 * Use this class to define the relations between models
 * if you need Many-To-Many relation, make two One-To-Many relations to the binding table
 * @example "../../../docs/Database/hasMany.php"
 * @author Dusan Malusev<dusan.998@outlook.com>
 * @package BrosSquad\MicroORM\Relations
 */
class HasMany extends OneToMany
{

    /**
     * Gets the part of the SQL Join statement
     * @api
     * @return string
     */
    public function getRelation(): string
    {
        return "{$this->foreignTable}.{$this->foreignKey} = {$this->referenceTable}.{$this->referenceKey}";
    }
}

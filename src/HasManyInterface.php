<?php


namespace Dusan\MicroORM;


use BrosSquad\MicroORM\Relations\HasMany;

interface HasManyInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     *
     * @return \BrosSquad\MicroORM\Relations\HasMany
     */
    public function hashMany(string $table, string $foreignKey): HasMany;
}

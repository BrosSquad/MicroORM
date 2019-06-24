<?php


namespace Dusan\MicroORM;


use Dusan\MicroORM\Relations\HasMany;

interface HasManyInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     *
     * @return \Dusan\MicroORM\Relations\HasMany
     */
    public function hashMany(string $table, string $foreignKey): HasMany;
}

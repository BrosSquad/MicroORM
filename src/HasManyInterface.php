<?php


namespace Dusan\PhpMvc\Database;


use Dusan\PhpMvc\Database\Relations\HasMany;

interface HasManyInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     *
     * @return \Dusan\PhpMvc\Database\Relations\HasMany
     */
    public function hashMany(string $table, string $foreignKey): HasMany;
}

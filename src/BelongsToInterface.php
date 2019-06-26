<?php


namespace BrosSquad\MicroORM;


use BrosSquad\MicroORM\Relations\BelongsTo;

interface BelongsToInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     *
     * @return BelongsTo
     */
    public function belongsTo(string $table, string $foreignKey): BelongsTo;
}

<?php


namespace Dusan\MicroORM;


use Dusan\MicroORM\Relations\BelongsTo;

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

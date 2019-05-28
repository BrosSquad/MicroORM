<?php


namespace Dusan\PhpMvc\Database;


use Dusan\PhpMvc\Database\Relations\BelongsTo;

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

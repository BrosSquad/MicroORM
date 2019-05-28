<?php


namespace Dusan\PhpMvc\Database\Traits;


trait Insert
{

    /**
     * Generated the insert sql statement with values that are added in $fillable array
     *
     * @internal
     * @return string
     */
    protected final function insert(): string
    {
        $sql = 'INSERT INTO ' . $this->getTable() . '(' . $this->getProtectedGlued() . ') VALUES (' .
            $this->joinArrayByComma(array_map(function ($item) {
                return ':' . strtolower($item);
            }, $this->protected));

        return $sql . ')';
    }

}

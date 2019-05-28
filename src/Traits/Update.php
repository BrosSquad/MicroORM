<?php


namespace Dusan\PhpMvc\Database\Traits;


trait Update
{

    /**
     * Generated the sql update statement from the $changed array
     * and bindings for these elements
     *
     * @internal
     * @return string
     */
    protected final function update(): string
    {
        $sql = 'UPDATE ' . $this->getTable() . ' SET ';
        foreach ($this->changed as $change => $value) {
            $sql .= " {$change}={$value},";
        }
        $sql = rtrim($sql, ',');

        $sql .= ' WHERE ' . $this->primaryKey . '=:' . $this->primaryKey;
        return $sql;
    }

}

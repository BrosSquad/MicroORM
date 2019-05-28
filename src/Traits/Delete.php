<?php


namespace Dusan\PhpMvc\Database\Traits;


use Dusan\PhpMvc\Database\Driver;
use PDOException;

trait Delete
{

    /**
     * Deletes the record from the database with the value from
     * the primary key
     * returns true on success
     * on failure throws PDOException
     *
     * @api
     * @example "../../../docs/Database/deleteWithInstance.php"
     * @throws \PDOException
     * @return bool
     */
    public final function delete(): bool
    {
        if (!$this->getId()) {
            throw new PDOException('Primary key must be set before this method is invoked');
        }
        return static::$database->transaction(function (Driver $db) {
            if ($this->observerInstance) {
                $this->observerInstance->deleting();
            }
            $db->sql('DELETE FROM ' . $this->getTable() . " WHERE  {$this->primaryKey}=:{$this->primaryKey}")
                ->bindValue(':' . $this->primaryKey, $this->getId())
                ->execute(NULL, true);
            if ($this->observerInstance) {
                $this->observerInstance->deleted($this);
            }
            return true;
        });
    }

    /**
     * Deletes record in the database without first fetching it like the delete method on instance
     *
     * @param int|string $id
     * @throws PDOException
     * @return bool
     */
    public static function del($id): bool
    {
        $instance = new static();
        return static::$database->transaction(function (Driver $db) use ($id, $instance) {
            $db->sql('DELETE FROM ' . $instance->getTable() . " WHERE  {$instance->primaryKey}=:{$id}")
                ->bindValue(':' . $instance->primaryKey, $id)
                ->execute(NULL, true);
            return true;
        });
    }

}

<?php


namespace BrosSquad\MicroORM\Actions;


use BrosSquad\MicroORM\DatabaseModel;
use BrosSquad\MicroORM\Driver;
use PDOException;

class Delete extends Action
{
    /**
     * @var int|string
     */
    protected $id;

    public function __construct($id, string $table, string $primaryKey)
    {
        parent::__construct(NULL, NULL);
        $this->id = $id;
        $this->primaryKey = $primaryKey;
        $this->tableName = $table;
    }

    public function save(): bool
    {
        try {
            return static::$driver->transaction(function (Driver $driver) {
                $driver->sql("DELETE FROM {$this->tableName} WHERE {$this->primaryKey}=:{$this->primaryKey}")
                    ->bindValue(':' . $this->primaryKey, $this->id)
                    ->execute();
                return true;
            });
        } catch (PDOException $e) {
            return false;
        }
    }
}

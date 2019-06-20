<?php


namespace Dusan\PhpMvc\Database\Actions;


use Dusan\PhpMvc\Database\DatabaseModel;
use Dusan\PhpMvc\Database\Driver;
use PDOException;

class Delete extends Action
{
    /**
     * @var int|string
     */
    protected $id;

    public function __construct(DatabaseModel $dbModel, array $fields, $id, ?string $customSql = NULL)
    {
        parent::__construct($dbModel, $fields, $customSql);
        $this->id = $id;
    }

    public function save(): bool
    {
        try {
            return self::$driver->transaction(function (Driver $driver) {
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

<?php


namespace Dusan\MicroORM\Actions;


use Dusan\MicroORM\DatabaseModel;
use Dusan\MicroORM\Driver;
use PDOException;

class Save extends Action
{
    /**
     * @var bool
     */
    protected $isInsert;

    /**
     * Save constructor.
     *
     * @param \Dusan\MicroORM\DatabaseModel $dbModel
     * @param array                                $fields
     * @param bool                                 $isInsert
     * @param string|null                          $customSql
     */
    public function __construct(DatabaseModel $dbModel, array $fields, bool $isInsert = false, ?string $customSql = NULL)
    {
        parent::__construct($dbModel, $fields, $customSql);
        $this->isInsert = $isInsert;
    }

    /**
     * Generates underlining SQL INSERT statement
     *
     * @return string
     */
    protected function generateInsertStatement(): string
    {
        if ($this->customSql) return $this->customSql;

        $sql = 'INSERT INTO ' . $this->tableName . '(' . join(',', $this->fields) . ') VALUES (' .
            array_reduce($this->fields, function ($total, $item) {
                if (empty($total)) {
                    return ':' . $item;
                } else {
                    return $total . ',:' . $item;
                }
            }, '');
        return $sql . ');';
    }

    /**
     * Generates underlining SQL UPDATE statement
     *
     * @return string
     */
    protected function generateUpdateStatement(): string
    {
        if ($this->customSql) return $this->customSql;
        if (empty($this->changed)) {
            return '';
        }
        $sql = 'UPDATE ' . $this->tableName . ' SET';
        foreach ($this->changed as $change => $value) {
            $sql .= " {$change}={$value},";
        }
        $sql = rtrim($sql, ',');

        $sql .= ' WHERE ' . $this->primaryKey . '=:' . $this->primaryKey;
        return $sql . ';';
    }

    /**
     * Generated the insert sql statement with values that are added in $fillable array
     *
     * @internal
     * @return void|int
     */
    protected function insert()
    {
        return static::$driver->transaction(function (Driver $driver) {
            $driver->sql($this->generateInsertStatement());
            foreach ($this->fields as $name => $bind) {
                $driver->bindValue($bind, $this->dbModel->{$name});
            }
            $driver->execute(NULL, true);
        });
    }

    /**
     * Generated the sql update statement from the $changed array
     * and bindings for these elements
     *
     * @internal
     * @return void
     */
    protected final function update(): void
    {
        if (empty($this->fields)) {
            return;
        }

        static::$driver->transaction(function (Driver $driver) {
            $driver->sql($this->generateUpdateStatement());

            foreach ($this->fields as $name => $bind) {
                $driver->bindValue($bind, $this->dbModel->{$name});
            }

            $driver->execute(NULL, true);
        });
    }


    public function saveOrFail()
    {
        if ($this->isInsert) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    public function save(): bool
    {
        try {
            $this->saveOrFail();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }


}

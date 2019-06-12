<?php


namespace Dusan\PhpMvc\Database;


use PDOException;

class Save
{
    private $isInsert;
    private $dbModel;
    private $fields;
    private static $driver;

    public function __construct(DatabaseModel $dbModel, array $fields, bool $isInsert = false)
    {
        $this->dbModel = $dbModel;
        $this->fields = $fields;
        $this->isInsert = $isInsert;
    }

    private function generateInsertStatement(): string
    {
        return '';
    }

    private function generateUpdateStatement(): string
    {
        return '';
    }

    /**
     * Generated the insert sql statement with values that are added in $fillable array
     *
     * @internal
     * @return string
     */
    protected final function insert(): string
    {
        $insert = [];
        $variables = $this->getVariables();
        foreach ($variables as $name => $value) {
            if (!isset($this->protected[$name])) {
                $insert[] = $name;
            }
        }

        $sql = 'INSERT INTO ' . $this->getTable() . '(' . join(',', $insert) . ') VALUES (' .
            array_reduce($insert, function ($total, $item) {
                if (empty($total)) {
                    return ':' . $item;
                } else {
                    return $total . ',:' . $item;
                }
            }, '');
        return $sql . ');';
    }

    /**
     * Generated the sql update statement from the $changed array
     * and bindings for these elements
     *
     * @internal
     * @return string
     */
    protected final function update(): string
    {
        if (empty($this->changed)) {
            return '';
        }
        $sql = 'UPDATE ' . $this->getTable() . ' SET';
        foreach ($this->changed as $change => $value) {
            $sql .= " {$change}={$value},";
        }
        $sql = rtrim($sql, ',');

        $sql .= ' WHERE ' . $this->primaryKey . '=:' . $this->primaryKey;
        return $sql . ';';
    }

    /**
     * TODO: Support for UUIDs and other types not just INTEGERS
     */
    public function saveOrFail()
    {
        $lastId = self::$driver->transaction(function (Driver $driver) {
            // Update statement
            if (isset($this->{$this->primaryKey})) {
                if ($this instanceof CustomUpdate) {
                    $sql = $this->setUpdate();
                    $bindings = $this->setUpdateBindings();
                } else {
                    $sql = $this->update();
                    $bindings = $this->changed;
                    $bindings[$this->primaryKey] = ':' . $this->primaryKey;
                }
            } // Insert statement
            else {
                if ($this instanceof CustomInsert) {
                    $sql = $this->setInsert();
                    $bindings = $this->setInsertBindings();
                } else {
                    $bindings = [];
                    $sql = $this->insert();
                    foreach ($this->getVariables() as $name => $value) {
                        if (!isset($this->protected[$name])) {
                            $bindings[$name] = ':' . $name;
                        }
                    }
                }
            }
            $driver->sql($sql);
            foreach ($bindings as $member => $binding) {
                $driver->bindValue($binding, $this->__get($member));
            }
            $driver->execute(NULL, true);
            return $driver->getLastInsertedId();
        });

        if (isset($lastId) && $lastId > 0) {
            $this->{$this->primaryKey} = $lastId;
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
    public static function setDatabaseDriver(Driver $driver)
    {
        self::$driver = $driver;
    }
}

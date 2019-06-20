<?php


namespace Dusan\PhpMvc\Database\Actions;


use Dusan\PhpMvc\Database\DatabaseModel;
use Dusan\PhpMvc\Database\Driver;

abstract class Action implements Savable
{

    /**
     * @var \Dusan\PhpMvc\Database\DatabaseModel
     */
    protected $dbModel;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string|null
     */
    protected $customSql;

    /**
     * @var \Dusan\PhpMvc\Database\Driver
     */
    protected static $driver;


    /**
     * Save constructor.
     *
     * @param \Dusan\PhpMvc\Database\DatabaseModel $dbModel
     * @param array                                $fields
     * @param string|null                          $customSql
     */
    public function __construct(DatabaseModel $dbModel, array $fields, ?string $customSql = NULL)
    {
        $this->dbModel = $dbModel;
        $this->fields = $fields;
        $this->primaryKey = $this->dbModel->getPrimaryKeyName();
        $this->customSql = $customSql;
        $this->tableName = $this->dbModel->getTable();
    }

    public static function setDatabaseDriver(Driver $driver)
    {
        self::$driver = $driver;
    }
}

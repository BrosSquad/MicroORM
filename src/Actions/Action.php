<?php


namespace BrosSquad\MicroORM\Actions;


use BrosSquad\MicroORM\DatabaseModel;
use BrosSquad\MicroORM\Driver;

abstract class Action implements Savable
{

    /**
     * @var \BrosSquad\MicroORM\DatabaseModel
     */
    protected DatabaseModel $dbModel;

    /**
     * @var array
     */
    protected array $fields;

    /**
     * @var string
     */
    protected string $primaryKey;

    /**
     * @var string
     */
    protected string $tableName;

    /**
     * @var string|null
     */
    protected ?string $customSql;

    /**
     * @var \BrosSquad\MicroORM\Driver
     */
    protected static Driver $driver;


    /**
     * Save constructor.
     *
     * @param \BrosSquad\MicroORM\DatabaseModel $dbModel
     * @param array                                $fields
     * @param string|null                          $customSql
     */
    public function __construct(?DatabaseModel $dbModel, ?array $fields, ?string $customSql = NULL)
    {
        $this->dbModel = $dbModel;
        $this->fields = $fields;
        if($dbModel != NULL) {
            $this->primaryKey = $this->dbModel->getPrimaryKeyName();
            $this->customSql = $customSql;
            $this->tableName = $this->dbModel->getTable();
        }
    }

    public static function setDatabaseDriver(Driver $driver)
    {
        static::$driver = $driver;
    }
}

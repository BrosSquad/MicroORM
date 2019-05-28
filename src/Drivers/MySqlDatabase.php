<?php

namespace Dusan\PhpMvc\Database\Drivers;

use Closure;
use Dusan\PhpMvc\Database\BindToDatabase;
use Dusan\PhpMvc\Database\DatabaseModel;
use Dusan\PhpMvc\Database\Driver;
use Dusan\PhpMvc\Database\Model;
use Dusan\PhpMvc\Database\Traits\MemberWithDash;
use PDO;
use PDOException;
use PDOStatement;
use stdClass;
use TypeError;

/**
 * Database Connection class fo MySql driver
 * <b>
 * Do to extend this database driver, if you want custom database driver create new class and
 * implement the Driver interface
 * </b>
 *
 * @package Dusan\PhpMvc\Database
 * @author  Dusan Malusev
 * @version 2.0
 */
final class MySqlDatabase implements Driver
{
    use MemberWithDash;


    protected static $customTypes = NULL;

    /**
     * Internal database connection
     *
     * @internal
     * @var PDO
     */
    protected $pdo = NULL;

    /**
     * PDO fetch mode
     * defaults to FETCH_CLASS
     *
     * @var int
     */
    private static $fetchMode = PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE;

    /**
     * SQL Statement to be executed
     *
     * @var string
     */
    private $sql = '';

    /**
     * Default fetch class
     *
     * @var string
     */
    private $className = stdClass::class;

    /**
     * Sql Prepared statement
     *
     * @internal
     * @var PDOStatement
     */
    private $statement = NULL;

    /**
     * Database constructor
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Set custom PDO fetch mode
     * @param int $fetchMode
     */
    public static function setFetchMode(int $fetchMode): void
    {
        self::$fetchMode = $fetchMode;
    }

    /**
     * @inheritDoc
     */
    public final function sql(string $sql): Driver
    {
        $this->sql = $sql;
        $this->statement = $this->pdo->prepare($sql);
        if (!$this->statement) {
            throw new PDOException('Statement count not be prepared');
        }
        return $this;
    }

    /**
     * @param int[] $bindings
     * @inheritDoc
     * @return Driver
     */
    public final function binding(array $bindings): Driver
    {
        foreach ($bindings as $bind => $value) {
            if (is_array($value)) {
                $type = $value['type'] ?? PDO::PARAM_STR;
                $this->bindValue($bind, $value['value'], $type);
            } else {
                $this->bindValue($bind, $value);
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public final function bindToObject(DatabaseModel $model, array $bindings, $memberBind = []): Driver
    {
        if (!is_array($bindings) || !is_array($memberBind)) {
            throw new TypeError('$bindings and $memberBind variables must be arrays');
        }
        foreach ($bindings as $member => $binding) {
            $this->bindValue($binding, $model->{$member}, $memberBind[$member] ?? PDO::PARAM_STR);
        }

        return $this;
    }

    /**
     * Binds to the reference of the $value
     * Evaluated only when the execute() is called on PDOStatement object
     * makes room for last minute modifications
     *
     * @param string $name
     * @param mixed $value
     * @param int|null $type
     *
     * @return Driver
     */
    public final function bindParam($name, &$value, ?int $type = NULL): Driver
    {
        $this->statement->bindParam($name, $value, $this->bindToPdoType($value, $type));
        return $this;
    }

    /**
     * Type bindings for PDO prepared statement
     * If not type is passed type of the $value variable will be determined
     * and PDO::PARAM_* will be returned accordingly
     *
     * @param mixed $value
     * @param null|int $optionalType
     *
     * @return int
     */
    public final function bindToPdoType(&$value, ?int $optionalType = NULL): int
    {
        if (is_null($optionalType)) {
            switch (true) {
                case is_int($value):
                    return PDO::PARAM_INT;
                case is_bool($value):
                    return PDO::PARAM_BOOL;
                case is_null($value):
                    return PDO::PARAM_NULL;
                case is_string($value):
                    return PDO::PARAM_STR;
            }
            $type = gettype($value);
            /**
             * @var BindToDatabase $customType
             */
            $customType = static::$customTypes[$type];
            if (isset($customType)) {
                $set = $customType->bind($value);
                $value = $set->value;
                return $set->key;
            }

            return PDO::PARAM_STR;
        } else {
            return $optionalType;
        }
    }

    /**
     * @inheritDoc
     */
    public final function bindValue(string $name, $value, $type = NULL): Driver
    {
        $this->statement->bindValue($name, $value, $this->bindToPdoType($value, $type));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public final function transaction(Closure $fn)
    {
        try {
            if (!$this->pdo->beginTransaction()) {
                throw new PDOException('Transaction could not start');
            }
            $value = $fn(clone $this);
            $this->pdo->commit();
            return $value;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public final function bindToClass(string $className): Driver
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public final function getLastInsertedId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @inheritDoc
     */
    public final function getLastInsertedRow(string $table, string $primaryKey = 'id')
    {
        $this->sql("SELECT * FROM {$table} WHERE {$primaryKey} = (SELECT MAX({$primaryKey}) FROM {$table});");
        return $this->execute();
    }


    /**
     * Executes the prepared statement
     * @param \Closure $fn
     * @param int|null $fetchMode
     * @param bool $insertOrUpdate
     *
     * @return array|bool
     */
    private final function execution(Closure $fn, ?int $fetchMode = NULL, $insertOrUpdate = false)
    {
        if (!$this->statement) {
            throw new PDOException('Statement is not prepared');
        }

        $success = $this->statement->execute();
        if (!$success) {
            throw new PDOException('Execution stopped');
        }
        if ($insertOrUpdate) {
            return $success;
        }
        if ($fetchMode) {
            return $this->statement->fetchAll($fetchMode);
        }
        return $fn($this->statement);
    }

    /**
     * @inheritDoc
     */
    private function mapping(Model & $model, array & $mappings)
    {
        return $model->lock(function () use ($model, $mappings) {
            foreach ($mappings as $key => $value) {
                $model->{$key} = $value;
            }
            return $model;
        });
    }

    /**
     * @inheritDoc
     */
    public final function execute(?int $fetchMode = NULL, $insertOrUpdate = false)
    {
        return $this->execution(
            function (PDOStatement $statement) {
                $newObjects = [];
                while (($obj = $statement->fetch(static::$fetchMode)) !== NULL) {
                    /** @var Model $instance */
                    $instance = new $this->className();
                    $mappings = (array)$obj;
                    $newObjects = $this->mapping($instance, $mappings);
                }
                return $newObjects;
            },
            $fetchMode,
            $insertOrUpdate
        );
    }

    /**
     * @inheritDoc
     */
    public final function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Wrapper for PDO::beginTransaction
     *
     * @return bool
     * @throws \PDOException
     */
    public final function startTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public final function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @inheritDoc
     */
    public final function getError(): array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * Wrapper for PDO commit method
     *
     * @return bool
     * @throws \PDOException
     */
    public final function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Cleans up the database connection
     */
    public final function __destruct()
    {
        if ($this->pdo !== NULL) {
            unset($this->pdo);
            $this->pdo = NULL;
        }
        if ($this->statement) {
            unset($this->statement);
            $this->statement = NULL;
        }
    }

    /**
     * @param string $type
     * @param BindToDatabase $binding
     */
    public static final function setCustomTypes(string $type, BindToDatabase $binding)
    {
        self::$customTypes[$type] = $binding;
    }

}

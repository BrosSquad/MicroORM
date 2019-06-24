<?php

namespace Dusan\MicroORM\Drivers;

use Closure;
use Dusan\MicroORM\{DatabaseModel, Driver, Model};
use Dusan\MicroORM\Traits\{DbToObject, MemberWithDash, ObjectToDb};
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Database Connection class fo MySql driver
 *
 * @package Dusan\MicroORM
 * @author  Dusan Malusev
 * @version 2.0
 */
final class MySqlDatabase extends Database
{
    use MemberWithDash;
    use ObjectToDb;
    use DbToObject;


    /**
     * Set custom PDO fetch mode
     *
     * @param int $fetchMode
     */
    public static function setFetchMode(int $fetchMode): void
    {
        self::$fetchMode = $fetchMode;
    }

    /**
     * @inheritDoc
     * @throws \PDOException
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
     *
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
    public final function bindToObject(DatabaseModel $model, array $bindings, array $memberBind = []): Driver
    {
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
     * @param string   $name
     * @param mixed    $value
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
     * @throws \PDOException
     */
    public final function getLastInsertedRow(string $table, string $primaryKey = 'id')
    {
        throw new RuntimeException('Method is not implemented');
    }


    /**
     * Executes the prepared statement
     *
     * @param \Closure $fn
     * @param int|null $fetchMode
     * @param bool     $insertOrUpdate
     *
     * @return array|bool
     * @throws \PDOException
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
    protected function mapping(Model $model, array $mappings)
    {
        return $model->lock(function () use ($model, $mappings) {
            foreach ($mappings as $key => $value) {
                $this->bindFromPdoToObject($model->{$key}, $value);
//                $model->{$key} = $value;
            }
            return $model;
        });
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     * @return array|bool|object
     */
    public final function execute(?int $fetchMode = NULL, bool $insertOrUpdate = false)
    {
        return $this->execution(
            function (PDOStatement $statement) {
                $newObjects = [];
                while (($arr = $statement->fetch(static::$fetchMode)) !== false) {
                    /** @var Model $instance */
                    $instance = new $this->className();
                    $instance->setExists($this, true);
                    $newObjects[] = $this->mapping($instance, $arr);
                }
                return $newObjects;
            },
            $fetchMode,
            $insertOrUpdate
        );
    }

}

<?php


namespace BrosSquad\MicroORM\Drivers;

use Closure;
use BrosSquad\MicroORM\{BindFromDatabase, BindToDatabase, CustomBindings\DateTimeBinding, DatabaseModel, Driver, Model};
use BrosSquad\MicroORM\Traits\{DbToObject, MemberWithDash, ObjectToDb};
use DateTimeInterface;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use RuntimeException;
use stdClass;

abstract class Database implements Driver
{

    use MemberWithDash;
    use ObjectToDb;
    use DbToObject;

    /**
     * @var array<string,\BrosSquad\MicroORM\BindToDatabase|string>
     */
    protected static array $customTypes = [
        DateTimeInterface::class => DateTimeBinding::class,
    ];

    /**
     * @var array<string, \BrosSquad\MicroORM\BindFromDatabase>
     */
    protected static array $customBind = [];

    /**
     * Internal database connection
     *
     * @internal
     * @var PDO
     */
    protected ?PDO $pdo = NULL;

    /**
     * PDO fetch mode
     * defaults to FETCH_CLASS
     *
     * @var int
     */
    protected static int $fetchMode = PDO::FETCH_ASSOC;

    /**
     * SQL Statement to be executed
     *
     * @var string
     */
    protected string $sql = '';

    /**
     * Default fetch class
     *
     * @var string
     */
    protected string $className = stdClass::class;

    /**
     * Sql Prepared statement
     *
     * @internal
     * @var PDOStatement
     */
    protected ?PDOStatement $statement = NULL;

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
    public final function startTransaction(): bool
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
    public function __destruct()
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
     * Bind object to database type
     *
     * @param string         $type
     * @param BindToDatabase $binding
     */
    public static function setCustomTypes(string $type, BindToDatabase $binding): void
    {
        static::$customTypes[$type] = $binding;
    }

    /**
     * Binds value from the database to the object
     *
     * @param string                               $type
     * @param \BrosSquad\MicroORM\BindFromDatabase $binding
     */
    public static function bindFromDatabaseToCustomObject(string $type, BindFromDatabase $binding): void
    {
        static::$customBind[$type] = $binding;
    }


    /**
     * Set custom PDO fetch mode
     *
     * @param int $fetchMode
     */
    public static function setFetchMode(int $fetchMode): void
    {
        static::$fetchMode = $fetchMode;
    }

    /**
     * @inheritDoc
     * @throws \PDOException
     */
    public function sql(string $sql): Driver
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
    public function binding(array $bindings): Driver
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
    public function bindToObject(DatabaseModel $model, array $bindings, array $memberBind = []): Driver
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
    public function bindParam($name, &$value, ?int $type = NULL): Driver
    {
        $this->statement->bindParam($name, $value, $this->bindToPdoType($value, $type));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function bindValue(string $name, $value, $type = NULL): Driver
    {
        $this->statement->bindValue($name, $value, $this->bindToPdoType($value, $type));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function transaction(Closure $fn)
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
    public function bindToClass(string $className): Driver
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertedId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @inheritDoc
     * @throws \PDOException
     */
    public function getLastInsertedRow(string $table, string $primaryKey = 'id')
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
    private function execution(Closure $fn, ?int $fetchMode = NULL, $insertOrUpdate = false)
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
    protected function mapping(Model $model, array & $mappings, array & $properties)
    {
        return $model->lock(fn () => $this->map($model, $mappings, $properties));
    }

    private function map(Model & $model, array & $mappings, array & $properties)
    {
        foreach ($mappings as $key => $value) {
            $this->bindFromPdoToObject($model, $key, $value, $properties[$key]);
        }
        return $model;
    }

    /**
     * {@inheritDoc}
     * @throws \PDOException
     * @return array|bool|object
     */
    public function execute(?int $fetchMode = NULL, bool $insertOrUpdate = false)
    {
        return $this->execution(
            fn (PDOStatement $statement): array => $this->mapAllObjects($statement),
            $fetchMode,
            $insertOrUpdate
        );
    }

    /**
     * @param \PDOStatement $statement
     *
     * @return array
     * @throws \ReflectionException
     */
    private function mapAllObjects(PDOStatement & $statement): array
    {
        $newObjects = [];
        $instance = new $this->className();
        $reflex = new ReflectionClass($instance);
        $props = $this->getAllProperties($reflex);
        $i = 0;
        while (($arr = $statement->fetch(static::$fetchMode)) !== false) {
            /** @var Model $instance */
            if ($i == 0) {
                $i++;
            } else {
                $instance = new $this->className();
            }
            $instance->setExists($this, true);
            $newObjects[] = $this->mapping($instance, $arr, $props);
            $i++;
        }
        return $newObjects;

    }

    private function getAllProperties(ReflectionClass & $reflectionClass): array
    {
        $props = [];
        $reflexProps = $reflectionClass->getProperties();
        foreach ($reflexProps as $prop) {
            $props[$prop->getName()] = $prop->getType();
        }
        return $props;
    }
}

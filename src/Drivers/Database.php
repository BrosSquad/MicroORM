<?php


namespace Dusan\PhpMvc\Database\Drivers;


use Dusan\PhpMvc\Database\BindFromDatabase;
use Dusan\PhpMvc\Database\BindToDatabase;
use Dusan\PhpMvc\Database\Driver;
use PDO;
use PDOStatement;
use stdClass;

abstract class Database implements Driver
{

    /**
     * @var array<string,\Dusan\PhpMvc\Database\BindFromDatabase>
     */
    protected static $customTypes = [];

    /**
     * @var array<string, \Dusan\PhpMvc\Database\BindFromDatabase>
     */
    protected static $customBind = [];

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
    protected static $fetchMode = PDO::FETCH_ASSOC;

    /**
     * SQL Statement to be executed
     *
     * @var string
     */
    protected $sql = '';

    /**
     * Default fetch class
     *
     * @var string
     */
    protected $className = stdClass::class;

    /**
     * Sql Prepared statement
     *
     * @internal
     * @var PDOStatement
     */
    protected $statement = NULL;

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
     * Bind object to database type
     * @param string         $type
     * @param BindToDatabase $binding
     */
    public static final function setCustomTypes(string $type, BindToDatabase $binding)
    {
        self::$customTypes[$type] = $binding;
    }

    /**
     * Binds value from the database to the object
     * @param string                                  $type
     * @param \Dusan\PhpMvc\Database\BindFromDatabase $binding
     */
    public static final function bindFromDatabaseToCustomObject(string $type, BindFromDatabase $binding)
    {
        self::$customBind[$type] = $binding;
    }
}

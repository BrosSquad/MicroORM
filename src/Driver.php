<?php

namespace Dusan\PhpMvc\Database;


use Closure;
use PDO;
use PDOException;
use TypeError;

/**
 * Database Connection class
 * <b>To change api of this class extend it and change the internals</b>
 *
 * @package Dusan\PhpMvc\Database
 * @author  Dusan Malusev
 * @version 2.0
 */
interface Driver
{
    /**
     * Database constructor
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo);


    /**
     * Prepares PDO Statement
     * If statement couldn't be prepared PDOException will be thrown
     *
     * @param string $sql
     *
     * @return Driver
     */
    public function sql(string $sql): Driver;

    /**
     * Multiple bindings in one go
     *
     * @param int[] $bindings
     *
     * @return Driver
     */
    public function binding(array $bindings): Driver;

    /**
     * Bind the parameters to prepared statement with DatabaseModelOLD and its bindings
     * Dynamically loads properties on the object
     * Every property must be the key in bindings array, and the value is the bound parameter in prepared statement
     * This method bind values from the object by the reference so it allows last minute changes
     * Actual parameters are bound when the execute method is called on the Database object
     *
     * @param \Dusan\PhpMvc\Database\DatabaseModelOLD $model
     * @param array                                   $bindings
     * @param int[]                                   $memberBind
     *
     * @throws TypeError
     * @return $this
     */
    public function bindToObject(DatabaseModelOLD $model, array $bindings, $memberBind = []): Driver;

    /**
     * Binds to the reference of the $value
     * Evaluated only when the execute() is called on PDOStatement object
     * makes room for last minute modifications
     *
     * @param string   $name
     * @param mixed    $value
     * @param int|null $type
     *
     * @return $this
     */
    public function bindParam($name, &$value, ?int $type = NULL): Driver;

    /**
     * Type bindings for PDO prepared statement
     * If not type is passed type of the $value variable will be determined
     * and PDO::PARAM_* will be returned accordingly
     *
     * @param mixed    $value
     * @param null|int $optionalType
     *
     * @return int
     */
    public function bindToPdoType(&$value, ?int $optionalType = NULL): int;

    /**
     * Binds to the value of the $value
     * not to it's reference
     * Does not allow the last minute modifications
     * Good for Immutable data
     *
     * @param string                       $name
     * @param string|integer|boolean|mixed $value
     * @param int|null                     $type
     *
     * @return $this
     */
    public function bindValue(string $name, $value, $type = NULL): Driver;

    /**
     * Starts the SQL transaction
     * Callback is executed and its value is returned from the method
     * If something wrong happens PDOException is rethrown
     * Callback function receives deep copy of the Database class
     * So it will never interfere with the original instance
     * it's good practice to use Immutable data
     *
     * @param Closure $fn callable that receives cloned instance of Database object
     *
     * @throws PDOException this exception is thrown when sql statements fails
     * @return mixed everything that is returned from the callback function is returned if no exception is raised
     */
    public function transaction(Closure $fn);

    /**
     * Binds the class name into which results will be stored
     *
     * @param string $className
     *
     * @return $this
     */
    public function bindToClass(string $className): Driver;

    /**
     * Wrapper for PDO::lastInsertId method
     *
     * @return string
     */
    public function getLastInsertedId();

    /**
     * Returns the last inserted row from the table
     *
     * @param string $table      name of the table
     * @param string $primaryKey Name of the primary key column
     *
     * @return array|mixed
     */
    public function getLastInsertedRow(string $table, string $primaryKey = 'id');

    /**
     * Executes prepared statement
     * $insertOrUpdate parameter is used to indicate where the rows should be returned or not
     * by default it's false and it will assume select statement is executed
     * for INSERT, UPDATE or DELETE this flag must be supplied so PDO does not throw exception
     *
     * @api
     *
     * @param int|null $fetchMode
     * @param bool     $insertOrUpdate
     *
     * @author Dusan Malusev
     * @return array|\Dusan\PhpMvc\Collections\Collection|mixed
     */
    public function execute(?int $fetchMode = NULL, $insertOrUpdate = false);

//    public function yield(?int $fetchMode = NULL, bool $insertOrUpdate = false);

    /**
     * @return PDO
     */
    public function getPdo(): PDO;

    /**
     * Wrapper for PDO::beginTransaction
     *
     * @return bool
     * @throws \PDOException
     */
    public function startTransaction();

    /**
     * Wrapper for PDO::rollBack method
     *
     * @return bool
     * @throws \PDOException
     */
    public function rollBack(): bool;

    /**
     * Wrapper for PDO::errorInfo method
     *
     * @return array
     */
    public function getError(): array;

    /**
     * Wrapper for PDO commit method
     *
     * @return bool
     * @throws \PDOException
     */
    public function commit(): bool;

    public function __destruct();

    public static function setCustomTypes(string $type, BindToDatabase $bind);
}

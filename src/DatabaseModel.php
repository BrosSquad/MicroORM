<?php
declare(strict_types=1);

namespace Dusan\PhpMvc\Database;

use Dusan\PhpMvc\Database\Exceptions\MethodNotFound;
use Dusan\PhpMvc\Database\Exceptions\PropertyNotFound;
use Dusan\PhpMvc\Database\FluentApi\Fluent;
use Dusan\PhpMvc\Database\Traits\JoinArrayByComma;
use Dusan\PhpMvc\Database\Traits\Lockable;
use Exception;
use JsonSerializable;
use PDOException;
use Psr\Container\ContainerInterface;
use Serializable;

class DatabaseModel extends AbstractModel implements Serializable, JsonSerializable
{
    use JoinArrayByComma;
    use Lockable;

    private $variables = NULL;
    private $calledClass = NULL;
    /**
     * @var \Dusan\PhpMvc\Database\Driver
     */
    protected static $driver;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private static $container;

    /**
     * Name of column in database for primary key
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Alias for the table
     *
     * @var string
     */
    protected $tableAlias = '';

    /**
     * Name of the table in the database
     * defaults to __CLASS__ + 's'
     * modified by setTable() method
     *
     * @var string
     */
    protected $table = '';

    /**
     * Guarded array restricts the Json serializes from showing
     * it as output
     * <b>Serialization</b>
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * When member of class is accessed
     * name of the member is added to $changed
     * for later use with insert() and update() methods
     * <b>Tracks changed for update statement</b>
     *
     * @internal
     * @source
     * @var array
     */
    private $changed = [];

    /**
     * Protected array protects data in the model,
     * this array is empty by default which indicated every property
     * is available for insert and update statements
     * if you want to protect field put it in this array
     *
     * @api
     * @var array
     */
    protected $protected = [];

    /**
     * @var bool
     */
    private $lock = false;

    public function __construct(array $properties = [])
    {
        $this->table = self::setTable();
        $this->guardedFields();
        $this->protectedFields();
        $this->guarded = array_flip($this->guarded);
        $this->protected = array_flip($this->protected);
        if ($properties !== NULL) {
            foreach ($properties as $name => $value) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * {@inheritDoc}
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        if (property_exists($this, $name)) {
            parent::__set($name, $value);
            $this->hasChanged($name);
        } else {
            throw new PropertyNotFound('Property ' . $name . ' is not found');
        }
    }

    public function __call($name, $arguments)
    {
        if (strcmp($name, 'testInsert') === 0) {
            return $this->insert();
        } else if (strcmp($name, 'testUpdate') === 0) {
            return $this->update();
        }
        throw new MethodNotFound('Method with name ' . $name . ' is not found');
    }

    private function guardedFields()
    {
        $this->guarded[] = 'changed';
        $this->guarded[] = 'lock';
        $this->guarded[] = 'table';
        $this->guarded[] = 'variables';
        $this->guarded[] = 'calledClass';
        $this->guarded[] = 'tableAlias';
        $this->guarded[] = 'protected';
        $this->guarded[] = 'guarded';
        $this->guarded[] = 'primaryKey';
    }

    private function protectedFields()
    {
        $this->protected[] = 'changed';
        $this->protected[] = 'lock';
        $this->protected[] = 'table';
        $this->protected[] = 'variables';
        $this->protected[] = 'calledClass';
        $this->protected[] = 'tableAlias';
        $this->protected[] = 'protected';
        $this->protected[] = 'guarded';
        $this->protected[] = 'primaryKey';
    }

    protected static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    protected static function setTable(): string
    {
        $splitClass = explode('\\', get_called_class());
        return mb_strtolower(end($splitClass)) . 's';
    }

    public function getTable(): string
    {
        return $this->table;
    }

    protected static function setDatabaseDriver(Driver $driver)
    {
        self::$driver = $driver;
    }


    public function getPrimaryKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Making some function that are not static
     * to be statically called
     *
     * @param $name
     * @param $arguments
     *
     * @return DatabaseModelOLD|Fluent|null|void
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'setDatabaseDriver':
                self::setDatabaseDriver($arguments[0]);
                break;
            case 'setContainer':
                self::setContainer($arguments[0]);
                break;
            default:
                throw new Exception('Method is not found');
        }
    }


    /**
     * String representation of object
     *
     * @link  https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * Constructs the object
     *
     * @link  https://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $arr = [];
        foreach ($this->getVariables() as $name => $value) {
            if (!isset($this->guarded[$name])) {
                $arr[$name] = $this->{$name};
            }
        }
        return $arr;
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

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        if ($this->variables === NULL) {
            $this->variables = get_object_vars($this);
        }
        return $this->variables;
    }

    private function hasChanged($name, $bindName = NULL)
    {
        if (!$this->lock) {
            $binding = $bindName ?? $name;
            $this->changed[$name] = ':' . $binding;
        }
    }


    // SQL Statements

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

    protected function deleteOnInstance(): bool {
        try {
            return self::$driver->transaction(function (Driver $driver) {
                $driver->sql("DELETE FROM {$this->getTable()} WHERE {$this->primaryKey}=:{$this->primaryKey}")
                    ->bindValue(':'. $this->primaryKey, $this->{$this->primaryKey})
                    ->execute();
                    return true;
            });
        } catch (PDOException $e) {
            return false;
        }
    }


    protected static function deleteOnStatic(DatabaseModel $model, $id) {
        try {
            return self::$driver->transaction(function (Driver $driver) use($model, $id){
                $pk = $model->getPrimaryKeyName();
                $table = $model->getTable();
                $driver->sql("DELETE FROM {$table} WHERE {$pk}=:{$pk};")
                    ->bindValue(':'. $pk, $id)
                    ->execute();
                return true;
            });
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAlias(): string
    {
        return $this->tableAlias;
    }

    public function getClass(): string
    {
        if ($this->calledClass === NULL) {
            $this->calledClass = get_called_class();
        }
        return $this->calledClass;
    }
}

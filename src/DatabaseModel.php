<?php
declare(strict_types=1);

namespace Dusan\PhpMvc\Database;

use Dusan\PhpMvc\Database\Exceptions\MethodNotFound;
use Dusan\PhpMvc\Database\Exceptions\PropertyNotFound;
use Dusan\PhpMvc\Database\FluentApi\Fluent;
use Dusan\PhpMvc\Database\Traits\JoinArrayByComma;
use Exception;
use JsonSerializable;
use PDOException;
use Psr\Container\ContainerInterface;
use Serializable;

class DatabaseModel extends AbstractModel implements Serializable, JsonSerializable
{
    use JoinArrayByComma;
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
    protected static $primaryKey = 'id';

    /**
     * Alias for the table
     *
     * @var string
     */
    protected static $tableAlias = '';

    /**
     * Name of the table in the database
     * defaults to __CLASS__ + 's'
     * modified by setTable() method
     *
     * @var string
     */
    protected static $table = '';

    /**
     * Guarded array restricts the Json serializes from showing
     * it as output
     * <b>Serialization</b>
     *
     * @var array
     */
    protected static $guarded = [];

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
    protected static $protected = [];

    /**
     * @var bool
     */
    private $lock = false;

    public function __construct(array $properties = [])
    {
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

    private static function guardedFields()
    {
        self::$guarded[] = 'changed';
    }

    private static function protectedFields()
    {
        self::$protected[] = 'changed';
    }

    protected static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    protected static function setTable(): string
    {
        return end(explode('\\', get_called_class())) . 's';
    }

    public static function getTable(): string
    {
        return self::$table;
    }

    protected static function setDatabaseDriver(Driver $driver)
    {
        self::guardedFields();
        self::protectedFields();
        self::$driver = $driver;

        self::$guarded = array_flip(self::$guarded);
        self::$protected = array_flip(self::$protected);
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
        foreach ($this->getVariables() as $variable) {
            if (!isset(static::$guarded[$variable])) {
                $arr[$variable] = $this->{$variable};
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
            if (isset($this->{self::$primaryKey})) {
                if ($this instanceof CustomUpdate) {
                    $sql = $this->setUpdate();
                    $bindings = $this->setUpdateBindings();
                } else {
                    $sql = $this->update();
                    $bindings = array_unique($this->changed);
                    $bindings[self::$primaryKey] = ':' . self::$primaryKey;
                }
            } // Insert statement
            else {
                if ($this instanceof CustomInsert) {
                    $sql = $this->setInsert();
                    $bindings = $this->setInsertBindings();
                } else {
                    $bindings = [];
                    $sql = $this->insert();
                    foreach ($this->getVariables() as $variable) {
                        if (!isset(self::$protected[$variable])) {
                            $bindings[$variable] = ':' . $variable;
                        }
                    }
                }
            }
            $driver->sql($sql);
            foreach($bindings as $member => $binding) {
                $driver->bindValue($binding, $this->__get($member));
            }
            return $driver->getLastInsertedId();
        });

        if(isset($lastId) && $lastId > 0) {
            $this->{self::$primaryKey} = $lastId;
        }
    }

    public function save(): bool
    {
        try {

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return get_object_vars($this);
    }

    public function hasChanged($name, $bindName = NULL)
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
        if (count($this->fillable) === 0) {
            $insert = [];
            $variables = $this->getVariables();
            foreach ($variables as $item => $value) {
                if (!key_exists($item, $this->protected)) {
                    $insert[] = $item;
                }
            }
        } else {
            $insert = $this->fillable;
        }

        $sql = 'INSERT INTO ' . $this->getTable() . '(' . join(',', $insert) . ') VALUES (' .
            array_reduce($insert, function ($total, $item) {
                if (empty($total)) {
                    return ':' . $item;
                } else {
                    return $total . ',:' . $item;
                }
            }, '');
        return $sql . ')';
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
        $sql = 'UPDATE ' . $this->getTable() . ' SET ';
        foreach ($this->changed as $change => $value) {
            $sql .= " {$change}={$value},";
        }
        $sql = rtrim($sql, ',');

        $sql .= ' WHERE ' . static::$primaryKey . '=:' . static::$primaryKey;
        return $sql;
    }

    /**
     * Transforms string from camelcase to snake case
     *
     * @param string $str
     *
     * @return false|mixed|string|string[]|null
     */
    private function snakeCase(string $str)
    {
        $newString = '';
        for ($i = 0; $i < mb_strlen($str); $i++) {
            if ($i === 0) {
                $newString = mb_strtolower($str[$i]);
                continue;
            }
            if (mb_ord($str[$i]) >= mb_ord('A') && mb_ord($str[$i]) <= mb_ord('Z')) {
                $newString .= '_' . mb_strtolower($str[$i]);
                continue;
            }
            $newString .= $str[$i];
        }
        return $newString;
    }


    public function getAlias(): string {
        return static::$tableAlias;
    }

    public function getClass(): string {
        return get_called_class();
    }
}

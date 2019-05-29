<?php

namespace Dusan\PhpMvc\Database;

use Dusan\PhpMvc\Database\Traits\DateTimeParse;
use Dusan\PhpMvc\Database\Traits\Delete;
use Dusan\PhpMvc\Database\Traits\Diff;
use Dusan\PhpMvc\Database\Traits\Insert;
use Dusan\PhpMvc\Database\Traits\Lockable;
use Dusan\PhpMvc\Database\Traits\ObjectVariables;
use Dusan\PhpMvc\Database\Traits\Save;
use Dusan\PhpMvc\Database\Traits\Update;
use Exception;
use PDO;
use JsonSerializable;
use Dusan\PhpMvc\Database\FluentApi\Fluent;
use Dusan\PhpMvc\Database\Traits\GetDateTime;
use Dusan\PhpMvc\Database\Traits\JoinArrayByComma;
use Psr\Container\ContainerInterface;
use Serializable;

/**
 * Abstract DatabaseModel class represents model that is in database
 * Each model that wants to represent table in database must inherit this abstract class
 * it makes use of Fluent api and automatic sql generation for ease of use
 *
 * @package Dusan\PhpMvc\Database
 * @author  Dusan Malusev <dusan.998@outlook.com>
 * @version 2.0
 * @license GPL Version 2
 * @uses    Fluent,JsonSerializable, Driver
 * @method setUpdate()
 * @method setUpdateBindings()
 * @method setInsert()
 * @method setInsertBindings()
 */
abstract class DatabaseModel extends AbstractModel implements JsonSerializable, PdoConstants, Serializable
{
    use Diff;
    use GetDateTime;
    use ObjectVariables;
    use JoinArrayByComma;
    use DateTimeParse;
    use Insert;
    use Update;
    use Delete;
    use Lockable;
    use Save;
    /**
     * @var ContainerInterface
     */
    private static $container;

    private $lock = false;

    /**
     * @var null|string
     */
    protected static $observer = null;

    /**
     * @var null|\Dusan\PhpMvc\Database\Events\Observer
     */
    private static $observerInstance = null;

    /**
     * Name of column in database for primary key
     *
     * @source
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Primary key field in database
     *
     * @api
     * @source
     * @var null|int|string
     */
    protected $id = NULL;

    /**
     * Alias for the table
     *
     * @var string
     */
    protected $alias = '';

    /**
     * Name of the table in the database
     * defaults to __CLASS__ + 's'
     * modified by setTable() method
     *
     * @var string
     * @source
     * @internal
     */
    protected $table = '';

    /**
     * Protected array protects data in the model,
     * this array is empty by default which indicated every property
     * is available for insert update statements
     * if you want to protect field put it in this array
     * @api
     * @var array
     */
    protected $protected = [];

    /**
     * Members of class that will not be included in $changed
     * Add members to $restricted array if you don't wont values to be tracked
     * when insert/update is performed
     * @api
     * @example "../../docs/Database/restricted.php"
     * @var array
     */
    protected $restricted = [];

    /**
     * When member of class is accessed
     * name of the member is added to $changed
     * for later use with insert() and update() methods
     * <b>Tracks changed for update statement</b>
     * @internal
     * @source
     * @var array
     */
    protected $changed = [];

    /**
     * Bindings of the fields in class with PDO type parameters for better protection
     * Key of the array must be string with name of the field which will have the PDO::PARAM_*
     * Value must be PDO::PARAM_* ->
     * For ease of use Database model will reference these parameters without PDO::PARAM_*
     *
     * @api
     * @var array
     */
    protected $memberTypeBindings = [];

    /**
     * Guarded array restricts the Json serializes from showing
     * it as output
     * <b>Serialization</b>
     * @example "../../docs/Database/restricted.php"
     * @var array
     */
    protected $guarded = [];

    /**
     * Underling database driver
     *
     * @var Driver
     */
    protected static $database;

    /**
     * Setting the database driver
     *
     * @param Driver $database
     *
     * @return void
     */
    private static function setDatabase(Driver $database)
    {
        self::$database = $database;
    }

    /**
     * Setting the IoC Container
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    private static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    /**
     * DatabaseModel constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->table = $this->setTable();
        $this->restricted();
        $this->setProtected();
        $this->exclude();
        if(static::$observer === null) {
            static::$observer = $this->setObserver();
        }
        if (static::$observerInstance !== null) {
            static::$observerInstance = static::$container->get(static::$observerInstance);
        }
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }
    }

    /**
     * Gets table name
     *
     * @api
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $value = parent::__get($name);
        if ($value === NULL || $value === 'NULL' && !isset($this->memberTypeBindings[$name])) {
            $this->memberTypeBindings[$name] = PDO::PARAM_NULL;
        }
        return $value;
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
        return $this->excluded();
    }

    /**
     * Setter function
     *
     * @param string $name
     * @param        $value
     *
     * @internal
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            if ($name === $this->primaryKey) {
                $this->setId($value);
            } else {
                parent::__set($name, $value);
            }
            $this->hasChanged($name);
        }
    }

    /**
     * Adding the members of class to restricted array
     * @internal
     * @return void
     */
    protected function restricted()
    {
        $this->restricted[] = 'guarded';
        $this->restricted[] = 'table';
        $this->restricted[] = 'database';
        $this->restricted[] = 'protected';
        $this->restricted[] = 'restricted';
        $this->restricted[] = 'fillable';
        $this->restricted[] = 'changed';
        $this->restricted[] = 'memberTypeBindings';
        $this->restricted[] = 'alias';
        $this->restricted[] = 'primaryKey';
        $this->restricted[] = 'id';
        $this->restricted[] = 'format';
        $this->restricted[] = 'lock';
    }

    /**
     * Adding the members of class to protected
     * filtered with $restricted
     * @internal
     * @return void
     */
    protected function setProtected(): void
    {
        if (count($this->protected) === 0) {
            $vars = $this->getVariables();
            foreach ($vars as $key => $value) {
                $found = array_search($key, $this->restricted);
                if ($found === false) {
                    $this->protected[] = $key;
                }
            }
        }
    }

    /**
     * Adding the member of class that will be excluded
     * in serialization and json encoding
     * @internal
     * @return void
     */
    protected function exclude(): void
    {
        $this->guarded[] = 'guarded';
        $this->guarded[] = 'table';
        $this->guarded[] = 'database';
        $this->guarded[] = 'guarded';
        $this->guarded[] = 'fillable';
        $this->guarded[] = 'changed';
        $this->guarded[] = 'memberTypeBindings';
        $this->guarded[] = 'alias';
        $this->guarded[] = 'primaryKey';
        $this->guarded[] = 'format';
        $this->guarded[] = 'protected';
        $this->guarded[] = 'restricted';
        $this->guarded[] = 'lock';
    }

    /**
     * Excluding the variable in serialization
     * @internal
     * @return array
     */
    protected function excluded(): array
    {
        return $this->diff($this->getVariables(), $this->guarded);
    }

    /**
     * Returns the value of the primary key from database
     * @api
     * @return int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets alias for the table
     * @api
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Sets the values of the primary key
     * <b> If you want setId to record the changed on the id override it on child class</b>
     * @api
     * @param int|string $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Making some function that are not static
     * to be statically called
     *
     * @param $name
     * @param $arguments
     *
     * @return DatabaseModel|Fluent|null|void
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'setDatabase':
                if (!$arguments[0] instanceof Driver) {
                    throw new Exception('Argument must be instance of Database Driver');
                }
                self::setDatabase($arguments[0]);
                break;
            case 'setContainer':
                if (!$arguments[0] instanceof ContainerInterface) {
                    throw new Exception('Argument must be instance of PSR Container');
                }
                self::setContainer($arguments[0]);
                break;
            default:
                throw new Exception('Method is not found');
        }
    }

    /**
     * Optional value for setters
     * e.g This method is used to indicate that the value of a property has changed
     * use this when you want to use getters and setters instead of properties it self
     * when getting or setting the properties the change is automatically recorded
     *
     * @param string $name     name of the property that will be changed
     * @param null   $bindName custom bind name
     */
    protected function hasChanged($name, $bindName = NULL)
    {
        if (!$this->lock) {
            $binding = is_null($bindName) ? $name : $bindName;
            $this->changed[$name] = ':' . $binding;
        }
    }

    /**
     * @internal
     * @return string
     */
    private function getProtectedGlued(): string
    {
        return $this->joinArrayByComma($this->protected);
    }

    /**
     * @inheritdoc
     *
     * @param $name
     * @param $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (strcmp($name, 'testInsert') === 0) {
            return $this->insert();
        } else if (strcmp($name, 'testUpdate') === 0) {
            return $this->update();
        } else if (preg_match("/^set([A-Z0-9]+[A-Za-z0-9]+)$/suD", $name, $matches) && count($arguments) === 1) {
            $newString = $this->snakeCase($matches[0]);
            $this->__set($newString, $arguments[0]);
        }
        return NULL;
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

    /**
     * Override the default value of the table
     * Default value for the table name is __CLASS__ + 's'
     * Sometimes this approach is not good so override when the naming convention
     * is not valid eg. Library -> Librarys (should be Libraries)
     * String must be returned because this value is used in later processing by the framework
     *
     * @api
     * @return string
     */
    protected function setTable(): string
    {
        $exp = explode('\\', $this->getClass());
        return strtolower($exp[count($exp) - 1]) . 's';
    }

    /**
     * @return string|void
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        foreach ($unserialized as $member => $value) {
            $this->{$member} = $value;
        }
    }

    protected function setObserver(): ?string
    {
        return null;
    }
}

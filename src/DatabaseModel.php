<?php
declare(strict_types=1);

namespace BrosSquad\MicroORM;

use Carbon\CarbonImmutable;
use BrosSquad\MicroORM\Actions\{Delete, Save};
use BrosSquad\MicroORM\Exceptions\{MethodNotFound, PropertyNotFound};
use BrosSquad\MicroORM\Traits\{Exists, Lockable, Observable};
use BrosSquad\MicroORM\FluentApi\Fluent;
use Exception;
use JsonSerializable;
use Serializable;

abstract class DatabaseModel extends AbstractModel implements Serializable, JsonSerializable
{
    use Lockable;
    use Exists;
    use Observable;

    /**
     * Name of column in database for primary key
     *
     * @var string
     */
    protected const PRIMARY_KEY = 'id';

    protected const CREATED_AT = 'created_at';

    protected const UPDATED_AT = 'updated_at';

    /**
     * @var null|array
     */
    private $variables = NULL;

    /**
     * @var null|string
     */
    private $calledClass = NULL;

    /**
     * Flag that indicates if Model exists in Database
     *
     * @var bool
     */
    protected $modelExists = false;


    /**
     * Alias for the table
     *
     * @var string
     */
    protected static $TABLE_ALIAS = '';

    /**
     * Name of the table in the database
     * defaults to __CLASS__ + 's'
     * modified by setTable() method
     *
     * @var string
     */
    protected static $TABLE = NULL;

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
        if (static::$TABLE === NULL) {
            static::$TABLE = static::setTable();
        }


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
        if (strcmp($name, 'setExists')) {
            $this->fromDb($arguments[0], $arguments[1]);
            return;
        }
        throw new MethodNotFound('Method with name ' . $name . ' is not found');
    }

    private function guardedFields()
    {
        $this->guarded[] = 'changed';
        $this->guarded[] = 'lock';
        $this->guarded[] = 'variables';
        $this->guarded[] = 'calledClass';
        $this->guarded[] = 'protected';
        $this->guarded[] = 'guarded';
        $this->guarded[] = 'modelExists';
    }

    private function protectedFields()
    {
        $this->protected[] = 'changed';
        $this->protected[] = 'lock';
        $this->protected[] = 'variables';
        $this->protected[] = 'calledClass';
        $this->protected[] = 'protected';
        $this->protected[] = 'guarded';
        $this->protected[] = 'modelExists';
    }

    protected static function setTable(): string
    {
        $splitClass = explode('\\', get_called_class());
        return mb_strtolower(end($splitClass)) . 's';
    }

    public function getTable(): string
    {
        return static::$TABLE;
    }

    public function getPrimaryKeyName()
    {
        return static::PRIMARY_KEY;
    }

    /**
     * Making some function that are not static
     * to be statically called
     *
     * @param $name
     * @param $arguments
     *
     * @return \BrosSquad\MicroORM\DatabaseModel|Fluent|null|void
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'delete':
                static::deleteOnStatic($arguments[0]);
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
        /** @var array $unserialize */
        $unserialize = unserialize($serialized);
        foreach($unserialize as $key => $value) {
            $this->{$key} = $value;
        }
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
     * @return bool
     */
    public final function insert(): bool
    {
        if ($this instanceof CustomInsert) {
            $sql = $this->setInsert();
            $bindings = $this->setInsertBindings();
        } else {
            $bindings = [];
            foreach ($this->getVariables() as $name => $value) {
                if (!isset($this->protected[$name])) {
                    $bindings[$name] = ':' . $name;
                }
            }
        }
        if (property_exists($this, static::UPDATED_AT)) {
            $this->{static::CREATED_AT} = CarbonImmutable::now();
            $bindings[static::CREATED_AT] = ':' . static::CREATED_AT;
        }
        $save = new Save($this, $bindings, true, $sql ?? NULL);
        if (static::$observer) static::$observer->creating();
        $insert = $save->save();
        if ($insert && static::$observer) static::$observer->created($this);
        return $insert;
    }

    /**
     * Generated the sql update statement from the $changed array
     * and bindings for these elements
     *
     * @return bool
     */
    public final function update(): bool
    {
        if ($this instanceof CustomUpdate) {
            $sql = $this->setUpdate();
            $bindings = $this->setUpdateBindings();
        } else {
            $bindings = $this->changed;
            $bindings[static::PRIMARY_KEY] = ':' . static::PRIMARY_KEY;
        }
        if (property_exists($this, static::UPDATED_AT)) {
            $this->{static::UPDATED_AT} = CarbonImmutable::now();
            $bindings[static::UPDATED_AT] = ':' . static::UPDATED_AT;
        }
        $save = new Save($this, $bindings, false, $sql ?? NULL);
        if (static::$observer) static::$observer->updating();
        $update = $save->save();
        if ($update && static::$observer) static::$observer->created($this);
        return $update;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if ($this->modelExists) {
            return $this->update();
        }
        return $this->insert();
    }

    /**
     * @return bool
     */
    protected function deleteOnInstance(): bool
    {
        $delete = new Delete($this->{static::PRIMARY_KEY}, static::$TABLE, static::PRIMARY_KEY);
        if (static::$observer) static::$observer->deleting();
        $deleted = $delete->save();
        if ($deleted && static::$observer) static::$observer->deleted();
        return $deleted;
    }

    /**
     * @param                               $id
     *
     * @return bool
     */
    protected static function deleteOnStatic($id)
    {
        $delete = new Delete($id, static::$TABLE, static::PRIMARY_KEY);
        if (static::$observer) static::$observer->deleting();
        $deleted = $delete->save();
        if ($deleted && static::$observer) static::$observer->deleted();
        return $deleted;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return static::$TABLE_ALIAS;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        if ($this->calledClass === NULL) {
            $this->calledClass = get_called_class();
        }
        return $this->calledClass;
    }
}

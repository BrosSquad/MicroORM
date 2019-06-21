<?php
declare(strict_types=1);

namespace Dusan\PhpMvc\Database;

use Dusan\PhpMvc\Database\Actions\Delete;
use Dusan\PhpMvc\Database\Actions\Save;
use Dusan\PhpMvc\Database\Exceptions\MethodNotFound;
use Dusan\PhpMvc\Database\Exceptions\PropertyNotFound;
use Dusan\PhpMvc\Database\FluentApi\Fluent;
use Dusan\PhpMvc\Database\Traits\JoinArrayByComma;
use Dusan\PhpMvc\Database\Traits\Lockable;
use Exception;
use JsonSerializable;
use Serializable;

abstract class DatabaseModel extends AbstractModel implements Serializable, JsonSerializable
{
    use JoinArrayByComma;
    use Lockable;

    /**
     * @var null|array
     */
    private $variables = NULL;

    /**
     * @var null|string
     */
    private $calledClass = NULL;


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

    protected static function setTable(): string
    {
        $splitClass = explode('\\', get_called_class());
        return mb_strtolower(end($splitClass)) . 's';
    }

    public function getTable(): string
    {
        return $this->table;
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
     * @return \Dusan\PhpMvc\Database\DatabaseModel|Fluent|null|void
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'delete':
                $instance = new static();
                self::deleteOnStatic($instance, $arguments[0]);
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

        $save = new Save($this, $bindings, true, $sql ?? NULL);

        return $save->save();
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
            $bindings[$this->primaryKey] = ':' . $this->primaryKey;
        }
        $save = new Save($this, $bindings, false, $sql ?? NULL);
        return $save->save();
    }

    protected function deleteOnInstance(): bool
    {
        return (new Delete($this, [], $this->{$this->primaryKey}))->save();
    }


    protected static function deleteOnStatic(DatabaseModel $model, $id)
    {
        return (new Delete($model, [], $id))->save();
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

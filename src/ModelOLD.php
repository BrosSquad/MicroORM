<?php


namespace Dusan\PhpMvc\Database;


use Dusan\PhpMvc\Database\FluentApi\AdvancedFluent;
use Dusan\PhpMvc\Database\FluentApi\AdvancedFluentInterface;
use Dusan\PhpMvc\Database\FluentApi\Fluent;
use Dusan\PhpMvc\Database\FluentApi\FluentInterface;
use Dusan\PhpMvc\Database\Relations\BelongsTo;
use Dusan\PhpMvc\Database\Relations\HasMany;

abstract class ModelOLD extends DatabaseModelOLD implements HasManyInterface, BelongsToInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     * @api
     * @return \Dusan\PhpMvc\Database\Relations\HasMany
     */
    public final function hashMany(string $table, string $foreignKey): HasMany
    {
        return new HasMany($this->getTable(), $this->primaryKey, $table, $foreignKey);
    }

    /**
     * @param string $table
     * @param string $foreignKey
     * @api
     * @return \Dusan\PhpMvc\Database\Relations\BelongsTo
     */
    public final function belongsTo(string $table, string $foreignKey): BelongsTo
    {
        return new BelongsTo($this->getTable(), $this->primaryKey, $table, $foreignKey);
    }

    /**
     * @param       $name
     * @param array $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = new static();
        switch ($name) {
            case 'query':
                return $instance->query();
            case 'find':
                return $instance->find($arguments[0], $arguments[1] ?? ['*']);
            case 'fluent':
                return $instance->fluent();
            default:
                return parent::__callStatic($name, $arguments);
        }
    }

    /**
     * Returns all records from the database based on the given filtering
     *
     * @api
     * @return FluentInterface
     * @example "../../docs/Database/query.php"
     * @see Fluent
     */
    protected final function query() : FluentInterface
    {
        return new Fluent(
            $this,
            $this->getClass(),
            $this->memberTypeBindings
        );
    }

    /**
     * @api
     * @see \Dusan\PhpMvc\Database\ModelOLD::query()
     * @return \Dusan\PhpMvc\Database\FluentApi\AdvancedFluentInterface
     */
    protected function fluent(): AdvancedFluentInterface {
        return new AdvancedFluent(
            $this,
            $this->getClass(),
            $this->memberTypeBindings
        );
    }

    /**
     * Gets the value from the database based on the primary key
     *
     * @api
     *
     * @param int|string $id     Value of the primary key
     * @param array      $select name of the selected fields
     *
     * @example "../../docs/Database/find.php"
     * @return static|null If there is no record in database null is returned
     */
    protected function find($id, $select = ['*']): ?ModelOLD
    {
        $data = static::$database
            ->sql($this->doSelect($select))
            ->bindParam(':' . $this->primaryKey, $id)
            ->bindToClass($this->getClass())
            ->execute();
        if (count($data) === 1) {
            return $data[0];
        }
        return NULL;
    }

    private function doSelect($select): string
    {
        return 'SELECT ' . $this->joinArrayByComma($select) .
            ' FROM ' . $this->getTable() .
            " WHERE {$this->primaryKey}=:id LIMIT 1";
    }
}
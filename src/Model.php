<?php


namespace Dusan\MicroORM;


use ArgumentCountError;
use Dusan\MicroORM\FluentApi\AdvancedFluent;
use Dusan\MicroORM\FluentApi\AdvancedFluentInterface;
use Dusan\MicroORM\FluentApi\Fluent;
use Dusan\MicroORM\FluentApi\FluentInterface;
use Dusan\MicroORM\Relations\BelongsTo;
use Dusan\MicroORM\Relations\HasMany;

/**
 * @method setExists(\Dusan\MicroORM\Drivers\MySqlDatabase $param, bool $true)
 */
abstract class Model extends DatabaseModel implements HasManyInterface, BelongsToInterface
{
    /**
     * @param string $table
     * @param string $foreignKey
     * @api
     * @return \Dusan\MicroORM\Relations\HasMany
     */
    public final function hashMany(string $table, string $foreignKey): HasMany
    {
        return new HasMany($this->getTable(), self::PRIMARY_KEY, $table, $foreignKey);
    }

    /**
     * @param string $table
     * @param string $foreignKey
     * @api
     * @return \Dusan\MicroORM\Relations\BelongsTo
     */
    public final function belongsTo(string $table, string $foreignKey): BelongsTo
    {
        return new BelongsTo($this->getTable(), self::PRIMARY_KEY, $table, $foreignKey);
    }


    public function __call($name, $arguments)
    {
        if(strcmp($name, 'delete')) {
            return $this->deleteOnInstance();
        }
        return parent::__call($name, $arguments);
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
                return $instance->find( $arguments[0], $arguments[1] ?? ['*']);
            case 'fluent':
                return $instance->fluent();
            case 'delete':
                if(count($arguments) !== 1) {
                    throw new ArgumentCountError('Not enough arguments passed to the method');
                }
                return self::deleteOnStatic($instance, $arguments[0]);
            default:
                return parent::__callStatic($name, $arguments);
        }
    }

    /**
     * Returns all records from the database based on the given filtering
     *
     * @api
     * @return \Dusan\MicroORM\FluentApi\AdvancedFluentInterface
     * @example "../../docs/Database/query.php"
     * @see     Fluent
     */
    protected final function query() : AdvancedFluentInterface
    {
        return new AdvancedFluent(
            $this,
            $this->getClass()
        );
    }

    /**
     * @api
     * @see \Dusan\MicroORM\Model::query()
     * @return \Dusan\MicroORM\FluentApi\FluentInterface
     */
    protected function fluent() : FluentInterface {
        return new Fluent(
            $this,
            $this->getClass()
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
     * @return static|null If there is no record in database null is returned
     * @example "../../docs/Database/find.php"
     */
    protected function find( $id, $select = ['*']): ?Model
    {
        return $this->query()
            ->where(self::PRIMARY_KEY, '=', $id)
            ->select($select)
            ->get();
    }

//    private function doSelect($select): string
//    {
//        $key = self::PRIMARY_KEY;
//        return 'SELECT ' . $this->joinArrayByComma($select) .
//            ' FROM ' . $this->getTable() .
//            " WHERE {$key}=:id LIMIT 1;";
//    }
}

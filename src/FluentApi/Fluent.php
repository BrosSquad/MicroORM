<?php

namespace Dusan\PhpMvc\Database\FluentApi;

use Dusan\PhpMvc\Collections\Collection;
use Dusan\PhpMvc\Database\Driver;
use Dusan\PhpMvc\Database\ModelOLD;
use Dusan\PhpMvc\Database\PdoConstants;
use Dusan\PhpMvc\Database\Relations\Relation;
use Dusan\PhpMvc\Database\Traits\JoinArrayByComma;
use stdClass;

/**
 * Simple SQL Generation tool with Fluent API Design Pattern
 * Fluent is activated by calling the query() method on DatabaseModelOLD class
 * It supports INNER,LEFT and RIGHT Sql Joins, complex WHERE Statements, ORDER BY,
 * GROUP BY
 * Mysql syntax is only supported
 *
 * @package Dusan\PhpMvc\Database\FluentApi
 * @author  Dusan Malusev
 * @see     \Dusan\PhpMvc\Database\DatabaseModelOLD
 * @version 2.0
 */
class Fluent implements PdoConstants, FluentInterface
{
    use JoinArrayByComma;

    /**
     * @internal
     * @var string
     */
    protected $select = '';

    /**
     * @internal
     * @var string
     */
    protected $join = '';

    /**
     * @internal
     * @var string
     */
    protected $where = '';

    /**
     * @internal
     * @var string
     */
    protected $limit = '';

    /**
     * @internal
     * @var string
     */
    protected $orderBy = '';

    /**
     * @internal
     * @var string
     */
    protected $groupBy = '';

    /**
     * @var Driver
     * @internal
     */
    protected static $database;

    /**
     * @var string
     * @internal
     */
    protected $sql;

    /**
     * @var string
     * @internal
     */
    protected $bind;

    /**
     * @var string
     * @internal
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var ModelOLD
     */
    protected $model;

    /**
     * @var array
     */
    protected $typeBindings;

    /**
     * @var null|array|Collection
     */
    protected $data = NULL;

    protected $alias = '';

    /**
     * Fluent constructor.
     *
     * @param ModelOLD $model
     * @param string   $class
     * @param array    $typeBindings
     */
    public function __construct(
        ModelOLD & $model,
        string $class = stdClass::class,
        array & $typeBindings = []
    )
    {
        $this->model = &$model;
        $this->table = $model->getTable() . ' ' . $model->getAlias();
        $this->select = 'SELECT * FROM ' . $this->table;
        $this->sql = 'SELECT * FROM ' . $model->getTable();
        $this->bind = $class;
        $this->typeBindings = &$typeBindings;
    }


    /**
     * @param $column
     * @param $operator
     * @param $value
     *
     * @internal
     * @return string
     */
    protected function whereGenerator($column, $operator, $value): string
    {
        $bind = ':' . strtolower($column);
        $this->bindings[$bind] = [
            'type' => $this->typeBindings[$column] ?? self::STRING,
            'value' => $value,
        ];
        return "{$column} $operator {$bind} ";
    }

    /**
     * @api
     *
     * @param array $select
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\Fluent
     */
    public function select($select = ['*']): Fluent
    {
        $this->select = 'SELECT ' . $this->joinArrayByComma($select) . ' FROM ' . $this->table . ' ';
//        $this->sql = 'SELECT ' . $this->joinArrayByComma($select) . ' FROM ' .$this->table . ' ';
        return $this;
    }

    public function selectDistinct($select = ['*']): Fluent
    {
        $this->select = 'SELECT DISTINCT' .
            $this->joinArrayByComma($select) .
            'FROM ' .
            $this->table . ' ';
        return $this;
    }

    /**
     * @return string
     * @internal
     */
    public function getSql(): string
    {
        $this->sql = trim($this->select) . ' ' .
            trim($this->join) . ' ' .
            trim($this->where) . ' ' .
            trim($this->groupBy) . ' ' .
            trim($this->orderBy) . ' ' .
            trim($this->limit);
        return trim($this->sql);
    }

    /**
     * @api
     *
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return Where
     */
    public function where($column, $operator, $value): Where
    {
        $generated = $this->whereGenerator($column, $operator, $value);
        $this->where = "WHERE {$generated}";
        return $this->newWhere();
    }

    /**
     * @api
     *
     * @param $column
     *
     * @return Where
     */
    public function whereNull($column): Where
    {
        $this->where = "WHERE {$column} IS NULL ";
        return $this->newWhere();
    }

    /**
     * @api
     *
     * @param $column
     *
     * @return Where
     */
    public function whereNotNull($column): Where
    {
        $this->where = "WHERE {$column} IS NOT NULL ";
        return $this->newWhere();
    }

    private function newWhere(): Where
    {
        return new Where(
            $this->model,
            $this->select,
            $this->join,
            $this->where,
            $this->limit,
            $this->orderBy,
            $this->groupBy,
            $this->bind,
            $this->typeBindings,
            $this->bindings
        );
    }

    /**
     * @api
     *
     * @param        $field
     * @param string $order
     *
     * @return $this
     */
    public function orderBy($field, $order = 'ASC'): Fluent
    {
        $this->orderBy = "ORDER BY {$field} {$order} ";
        return $this;
    }

    /**
     * @api
     *
     * @param $field
     *
     * @return Fluent
     */
    public function orderByDesc($field): Fluent
    {
        return $this->orderBy($field, 'DESC');
    }

    /**
     * @api
     *
     * @param string $table reference table or class
     *
     * @return Fluent
     */
    public function innerJoin($table): Fluent
    {
        return $this->generateJoin(JoinInterface::INNER, $this->model->{$table}());
    }

    /**
     * @api
     *
     * @param $items
     *
     * @return $this
     */
    public function limit($items): Fluent
    {
        $this->limit .= "LIMIT {$items};";
        return $this;
    }

    /**
     * @param      $table
     * @param null $foreignKey
     *
     * @return Fluent
     */
    public function leftJoin($table, $foreignKey = NULL): Fluent
    {
        return $this->generateJoin(JoinInterface::LEFT, $this->model->{$table}());
    }

    /**
     * @param      $table
     * @param null $foreignKey
     *
     * @return Fluent
     */
    public function rightJoin($table, $foreignKey = NULL): Fluent
    {
        return $this->generateJoin(JoinInterface::RIGHT, $this->model->{$table}());
    }

    public function groupBy(array $columns): GroupBy
    {
        $this->groupBy = 'GROUP BY ' . $this->joinArrayByComma($columns) . ' ';
        return $this->newGroupBy();
    }

    private function newGroupBy(): GroupBy
    {
        return new GroupBy(
            $this->model,
            $this->select,
            $this->join,
            $this->where,
            $this->limit,
            $this->orderBy,
            $this->groupBy,
            $this->bind,
            $this->typeBindings,
            $this->bindings
        );
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @return $this
     */
    public function paginate(int $page, int $perPage)
    {
        $page--;
        $startItem = $page * $perPage;
        $this->limit = 'LIMIT :start, :end;';
        $this->bindings[':start'] = [
            'type' => self::INTEGER,
            'value' => $startItem,
        ];
        $this->bindings[':end'] = [
            'type' => self::INTEGER,
            'value' => $perPage,
        ];
        return $this;
    }

    /**
     * Gets the data from database by previous called methods
     *
     * @return Collection
     * @throws \PDOException
     */
    public function get()
    {
        if ($this->data === NULL) {
            return $this->asCollection();
        }
        return $this->data;
    }

    /**
     * Return data as Collection class
     *
     * @uses Collection
     * @see  Collection
     * @return \Dusan\PhpMvc\Collections\Collection
     */
    private function asCollection(): Collection
    {
        $this->data = Collection::fromArray($this->asArray());
        return $this->data;
    }

    /**
     * Returns data as ReactiveX Observable
     *
     * @return $this
     * @throws \PDOException
     * @throws \Exception
     */
//    public function asObservable()
//    {
//        $this->data = Observable::fromArray($this->asArray()->get());
//        return $this;
//    }

    /**
     * Returns data as ordinary php array
     * This method is not needed to be used, the shorter version will be
     * ->get() without calling any of as* methods
     *
     * @return array
     * @throws \PDOException
     */
    private function asArray(): array
    {
        $this->data = static::$database->sql($this->getSql())
            ->bindToClass($this->bind)
            ->binding($this->bindings)
            ->execute();
        return $this->data;
    }

    /**
     * @param string   $joinType
     * @param Relation $relation
     *
     * @return Fluent
     */
    protected function generateJoin(string $joinType, Relation $relation): Fluent
    {
        $this->join = "{$joinType} JOIN {$relation->getForeignTable()} ON {$relation->getRelation()} ";
        return $this;
    }

    public static function setDatabase(Driver $database): void
    {
        self::$database = $database;
    }
}

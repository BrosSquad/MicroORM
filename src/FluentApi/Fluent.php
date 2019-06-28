<?php

namespace BrosSquad\MicroORM\FluentApi;

use BrosSquad\MicroORM\{Driver, Model, Relations\Relation, Traits\Random};
use Dusan\PhpMvc\Collections\Collection;
use PDO;
use stdClass;

/**
 * Simple SQL Generation tool with Fluent API Design Pattern
 * Fluent is activated by calling the query() method on DatabaseModelOLD class
 * It supports INNER,LEFT and RIGHT Sql Joins, complex WHERE Statements, ORDER BY,
 * GROUP BY
 * Mysql syntax is only supported
 * TODO: Check ? and : bind parameters in single sql statement
 *
 * @package BrosSquad\MicroORM\FluentApi
 * @author  Dusan Malusev
 * @see     \BrosSquad\MicroORM\DatabaseModelOLD
 * @version 2.0
 */
class Fluent implements FluentInterface
{

    use Random;

    /**
     * @var int
     */
    protected int $current = 0;

    /**
     * @internal
     * @var string
     */
    protected string $select = '';

    /**
     * @internal
     * @var string
     */
    protected string $join = '';

    /**
     * @internal
     * @var string
     */
    protected string $where = '';

    /**
     * @internal
     * @var string
     */
    protected string $limit = '';

    /**
     * @internal
     * @var string
     */
    protected string $orderBy = '';

    /**
     * @internal
     * @var string
     */
    protected string $groupBy = '';

    /**
     * @var Driver
     * @internal
     */
    protected static Driver $database;

    /**
     * @var string
     * @internal
     */
    protected string $sql;

    /**
     * @var string
     * @internal
     */
    protected string $bind;

    /**
     * @var string
     * @internal
     */
    protected string $table;

    /**
     * @var string
     */
    protected string $primaryKey;

    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var Model
     */
    protected Model $model;


    /**
     * @var null|array|Collection
     */
    protected $data = NULL;

    /**
     * @var string
     */
    protected string $alias = '';

    /**
     * Fluent constructor.
     *
     * @param Model  $model
     * @param string $class
     */
    public function __construct(
        Model & $model,
        string $class = stdClass::class
    )
    {
        $this->model = &$model;
        $this->table = $model->getTable() . ' ' . $model->getAlias();
        $this->select = 'SELECT * FROM ' . $this->table;
        $this->sql = 'SELECT * FROM ' . $model->getTable();
        $this->bind = $class;
    }


    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @internal
     * @return string
     */
    protected function whereGenerator(string $column, string $operator, $value): string
    {
        $bind = ':' . strtolower($column) . $this->randomString(5);
        $this->bindings[$bind] = [
            'type' => $this->typeBindings[$column] ?? PDO::PARAM_STR,
            'value' => $value,
        ];

        return "{$column} $operator {$bind} ";
    }

    /**
     * @api
     *
     * @param array $select
     *
     * @return \BrosSquad\MicroORM\FluentApi\Fluent
     */
    public function select(...$select): Fluent
    {
        if (is_array($select) && empty($select)) {
            $select = ['*'];
        }
        $this->select = 'SELECT ' . join(',', $select) . ' FROM ' . $this->table . ' ';
        return $this;
    }

    public function selectDistinct(...$select): Fluent
    {
        if (is_array($select) && empty($select)) {
            $select = ['*'];
        }
        $this->select = 'SELECT DISTINCT' .
            join(',', $select) .
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
        $this->where = 'WHERE ' . $generated;
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
        $this->where = 'WHERE' . $column . ' IS NULL ';
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
        $this->where = 'WHERE ' . $column . 'IS NOT NULL ';
        return $this->newWhere();
    }

    protected function newWhere(): Where
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
            $this->bindings,
            $this->current
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
        $this->groupBy = 'GROUP BY ' . join(',', $columns) . ' ';
        return $this->newGroupBy();
    }

    protected function newGroupBy(): GroupBy
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
            $this->bindings,
            $this->current
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
            'type' => PDO::PARAM_INT,
            'value' => $startItem,
        ];
        $this->bindings[':end'] = [
            'type' => PDO::PARAM_INT,
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
            $this->data = $this->asCollection();
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
    protected function asCollection(): Collection
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
    protected function asArray(): array
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
        static::$database = $database;
    }
}

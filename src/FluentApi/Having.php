<?php

namespace Dusan\PhpMvc\Database\FluentApi;


use Dusan\PhpMvc\Database\Model;
use stdClass;

class Having extends Fluent
{
    /**
     * @var string
     */
    protected $having = '';

    public function __construct(
        Model& $model,
        string $having,
        string $select,
        string $join,
        string $where,
        string $limit,
        string $orderBy,
        string $groupBy,
        string $class = stdClass::class,
        array& $typeBindings = [],
        array& $bindings = []
    )
    {
        parent::__construct($model, $class, $typeBindings);
        $this->select = $select;
        $this->join = $join;
        $this->where = $where;
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->groupBy = $groupBy;
        $this->having = $having;
        $this->bindings = $bindings;
    }

    /**
     * @return $this
     */
    public function or() {
        $this->having .= '';
        return $this;
    }

    /**
     * @return $this
     */
    public function and() {
        $this->having .= '';
        return $this;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getSql(): string
    {
        $this->sql = trim($this->select) . ' ' .
            trim($this->join) .
            trim($this->where) .
            trim($this->orderBy) .
            trim($this->groupBy) . ' ' .
            trim($this->having) .
            trim($this->limit);
        return trim($this->sql);
    }
}
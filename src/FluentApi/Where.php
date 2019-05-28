<?php

namespace Dusan\PhpMvc\Database\FluentApi;

use Dusan\PhpMvc\Database\Model;
use stdClass;

class Where extends Fluent
{
    protected $sql;

    public function __construct(
        Model& $model,
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
        $this->bindings = $bindings;
    }

    public function orWhere($column, $operator, $value) {
        $generated = $this->whereGenerator($column, $operator, $value);
        $this->where .= 'OR ' . $generated;
        return $this;
    }

    public function andWhere($column, $operator, $value) {
        $generated = $this->whereGenerator($column, $operator, $value);
        $this->where .= 'AND ' . $generated;
        return $this;
    }
}
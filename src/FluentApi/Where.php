<?php

namespace BrosSquad\MicroORM\FluentApi;

use BrosSquad\MicroORM\Model;
use stdClass;

class Where extends Fluent
{
    protected string $sql;

    public function __construct(
        Model& $model,
        string $select,
        string $join,
        string $where,
        string $limit,
        string $orderBy,
        string $groupBy,
        string $class = stdClass::class,
        array& $bindings = [],
        int $current = 0
    )
    {
        parent::__construct($model, $class);
        $this->select = $select;
        $this->join = $join;
        $this->where = $where;
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->groupBy = $groupBy;
        $this->bindings = $bindings;
        $this->current = $current;
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

<?php

namespace Dusan\MicroORM\FluentApi;


use Dusan\MicroORM\Model;

class Join extends Fluent implements JoinInterface
{
    public function __construct(
        Model& $model,
        string $select,
        string $join,
        string $where,
        string $limit,
        string $orderBy,
        string $groupBy,
        string $class = \stdClass::class,
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
}

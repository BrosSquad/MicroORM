<?php


namespace Dusan\PhpMvc\Database\FluentApi;


class AdvancedFluent extends Fluent implements FluentInterface, AdvancedFluentInterface
{

    /**
     * @inheritDoc
     *
     * @param string $column
     * @param string $term
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\FluentInterface
     */
    public function search(string $column, string $term): FluentInterface
    {
        return $this->where($column, 'LIKE', $term);
    }

    /**
     * @inheritDoc
     *
     * @param string $term
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\FluentInterface
     */
    public function fullTextSearch(string $term): FluentInterface
    {
        return $this;
    }


    /**
     * @inheritDoc
     *
     * @param string                $column
     * @param float|int|string|null $value
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\FluentInterface
     */
    public function whereEquals(string $column, $value): FluentInterface
    {
        return $this->where($column, '=', $value);
    }

    public function whereBetween(string $column, $start, $end): FluentInterface
    {
        $this->where = 'WHERE ' . $column . 'BETWEEN :start, :end ';

        $this->bindings[':start'] = [
            'type' => $this->typeBindings[$column] ?? self::STRING,
            'value' => $start,
        ];
        $this->bindings[':end'] = [
            'type' => $this->typeBindings[$column] ?? self::STRING,
            'value' => $end,
        ];
        return $this->newWhere();
    }

    public function whereIn(string $column, array $values): FluentInterface
    {
        $this->where = "WHERE {$column} IN (";
        foreach($values as $val) {
            $this->where .= '?,';
            $this->bindings[++$this->current] = [
                'type' => $this->typeBindings[$column] ?? self::STRING,
                'value' => $val,
            ];
        }

        trim($this->where, ',');

        $this->where .= ') ';
        return $this->newWhere();
    }


    public function wherePrimaryKey(array $values): FluentInterface
    {
        return $this->whereIn($this->primaryKey, $values);
    }

    public function whereNotIn(string $column, array $values): FluentInterface
    {
        $this->where = "WHERE {$column} NOT IN (";
        foreach($values as $val) {
            $this->where .= '?,';
            $this->bindings[++$this->current] = [
                'type' => $this->typeBindings[$column] ?? self::STRING,
                'value' => $val,
            ];
        }

        trim($this->where, ',');

        $this->where .= ') ';
        return $this->newWhere();
    }

    public function whereNotPrimaryKey(array $values): FluentInterface
    {
        return $this->whereNotIn($this->primaryKey, $values);
    }

    /**
     * @inheritDoc
     *
     * @param string                $column
     * @param float|int|string|null $value
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\FluentInterface
     */
    public function whereNotEquals(string $column, $value): FluentInterface
    {
        return $this->where($column, '<>', $value);
    }
}

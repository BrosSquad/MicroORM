<?php


namespace Dusan\PhpMvc\Database\FluentApi;


class AdvancedFluent extends Fluent implements FluentInterface, AdvancedFluentInterface
{
    /**
     * @inheritDoc
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
     * @param string                $column
     * @param float|int|string|null $value
     *
     * @return \Dusan\PhpMvc\Database\FluentApi\FluentInterface
     */
    public function whereEquals(string $column, $value): FluentInterface
    {
        return $this->where($column, '=', $value);
    }

    /**
     * @inheritDoc
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

<?php

namespace Dusan\MicroORM\FluentApi;

interface AdvancedFluentInterface extends FluentInterface
{

    /**
     * @param string $column
     * @param string $term
     *
     * @return FluentInterface
     */
    public function search(string $column, string $term): FluentInterface;

    /**
     * @param string $term
     *
     * @return FluentInterface
     */
    public function fullTextSearch(string $term): FluentInterface;

    /**
     * @param string                       $column
     * @param int|string|null|float|double $value
     *
     * @return FluentInterface
     */
    public function whereEquals(string $column, $value): FluentInterface;

    /**
     * @param string                       $column
     * @param int|string|null|float|double $value
     *
     * @return FluentInterface
     */
    public function whereNotEquals(string $column, $value): FluentInterface;

    public function wherePrimaryKey(array $values): FluentInterface;

    public function whereNotPrimaryKey(array $values): FluentInterface;

    public function whereIn(string $column, array $values): FluentInterface;

    public function whereNotIn(string $column, array $values): FluentInterface;
}

<?php

namespace Dusan\PhpMvc\Database\Traits;


use Dusan\PhpMvc\Exceptions\NullPointerException;

trait Paginator
{
    protected $select = ['*'];
    protected $where = null;
    protected $orderBy = null;
    protected $groupBy = null;
    protected $join = null;
    protected $NumberOfPages;
    protected $alias = '';

    /**
     * @return mixed
     */
    public function getNumberOfPages()
    {
        return $this->NumberOfPages;
    }

    /**
     * @param mixed $NumberOfPages
     */
    public function setNumberOfPages($NumberOfPages): void
    {
        $this->NumberOfPages = $NumberOfPages;
    }



    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @param array $select
     */
    public function setSelect(array $select): void
    {
        $this->select = $select;
    }

    /**
     * @return null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param null $where
     */
    public function setWhere($where): void
    {
        $this->where = $where;
    }

    /**
     * @return null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param null $orderBy
     */
    public function setOrderBy($orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return null
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param null $groupBy
     */
    public function setGroupBy($groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }



    /**
     * @return null
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @param null $join
     */
    public function setJoin($join): void
    {
        $this->join = $join;
    }

    public function numOfPages() {
        return "SELECT ROUND(COUNT({$this->table}.ID) / :pages) + 1 as NumberOfPages FROM {$this->table};";
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return string
     * @throws \Exception
     */
    public function paginate(int $page = 1, int $perPage = 5) {
        $page--;
        if($this->table === null) {
            throw new NullPointerException('Table cannot be null');
        }
        if($page < 0) {
            throw new \Exception('Page is less then 0');
        }
        $joined = rtrim(join(',', $this->select), ',');
        $join = $this->join !== null ? $this->join : '';
        $where = $this->where !== null ? ' WHERE ' . $this->where : '';
        $orderBy = $this->orderBy !== null ? ' ORDER BY ' . $this->orderBy : '';
        $groupBy = $this->groupBy !== null ? ' GROUP BY ' . $this->groupBy : '';
        $start = $page * $perPage;
        $finish = $start + $perPage;
        return "SELECT {$joined} FROM {$this->table} {$this->alias} {$join} {$where} {$groupBy} {$orderBy} LIMIT {$start}, {$finish}";
    }
}
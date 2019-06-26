<?php


namespace BrosSquad\MicroORM\Relations;

/**
 * Class OneToMany
 * @author Dusan Malusev<dusan.998@outlook.com>
 * @package BrosSquad\MicroORM\Relations
 * @internal
 */
abstract class OneToMany implements Relation
{
    protected $referenceTable;
    protected $referenceKey;
    protected $foreignKey;
    protected $foreignTable;

    /**
     * OneToMany constructor.
     *
     * @param $referenceTable
     * @param $referenceKey
     * @param $foreignKey
     * @param $foreignTable
     */
    public function __construct($referenceTable, $referenceKey, $foreignTable, $foreignKey)
    {
        $this->referenceTable = $referenceTable;
        $this->referenceKey = $referenceKey;
        $this->foreignKey = $foreignKey;
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return mixed
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * @return mixed
     */
    public function getReferenceKey()
    {
        return $this->referenceKey;
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @return mixed
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
    }

}

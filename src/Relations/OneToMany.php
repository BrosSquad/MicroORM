<?php


namespace BrosSquad\MicroORM\Relations;

/**
 * Class OneToMany
 *
 * @author  Dusan Malusev<dusan.998@outlook.com>
 * @package BrosSquad\MicroORM\Relations
 * @internal
 */
abstract class OneToMany implements Relation
{
    /**
     * @var string
     */
    protected string $referenceTable;

    /**
     * @var string
     */
    protected string $referenceKey;

    /**
     * @var string
     */
    protected string $foreignKey;

    /**
     * @var string
     */
    protected string $foreignTable;


    /**
     * OneToMany constructor.
     *
     * @param string $referenceTable
     * @param string $referenceKey
     * @param string $foreignTable
     * @param string $foreignKey
     */
    public function __construct(string $referenceTable, string $referenceKey, string $foreignTable, string $foreignKey)
    {
        $this->referenceTable = $referenceTable;
        $this->referenceKey = $referenceKey;
        $this->foreignKey = $foreignKey;
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return string
     */
    public function getReferenceTable(): string
    {
        return $this->referenceTable;
    }

    /**
     * @return string
     */
    public function getReferenceKey(): string
    {
        return $this->referenceKey;
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

}

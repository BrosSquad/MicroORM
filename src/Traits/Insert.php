<?php


namespace Dusan\PhpMvc\Database\Traits;


trait Insert
{

    /**
     * Generated the insert sql statement with values that are added in $fillable array
     *
     * @internal
     * @return string
     */
    protected final function insert(): string
    {
        if(count($this->fillable) === 0) {
            $insert = [];
            $variables = $this->getVariables();
            foreach( $variables as $item => $value ) {
                if(!key_exists($item, $this->protected)) {
                    $insert[] = $item;
                }
            }
        } else {
            $insert = $this->fillable;
        }

        $sql = 'INSERT INTO ' . $this->getTable() . '(' . join(',', $insert) . ') VALUES (' .
            array_reduce($insert, function ($total, $item) {
                if(empty($total)) {
                    return ':' .$item;
                } else {
                    return $total . ',:' . $item;
                }
            }, '');
        return $sql . ')';
    }

}

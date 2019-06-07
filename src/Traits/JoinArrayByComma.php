<?php

namespace Dusan\PhpMvc\Database\Traits;


trait JoinArrayByComma
{
    /**
     * Joins the array with ',' and trims the last ',' from the string
     * @see DatabaseModelOLD::find()
     * @param array $arr array to be joined by ','
     * @return string
     */
    protected final function joinArrayByComma($arr): string
    {
        $glued = implode(', ', $arr);
        return rtrim($glued, ', ');
    }
}

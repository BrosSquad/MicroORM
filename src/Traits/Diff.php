<?php

namespace Dusan\PhpMvc\Database\Traits;


trait Diff
{
    protected function diff($vars, array $search)
    {
        $returnArr = [];
        $search = array_flip($search);
        foreach($vars as $item => $value) {
            if(!isset($search[$item])) {
                $returnArr[$item] = $vars[$item];
            }
        }
        return $returnArr;
    }
}

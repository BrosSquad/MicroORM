<?php


namespace Dusan\PhpMvc\Database\Traits;


trait MemberWithDash
{
    /**
     * @param string $name
     *
     * @return string
     */
    protected function memberWithDash(string $name) {
        $member = explode('_', $name);
        if(count($member) === 2) {
            $name = ucfirst($member[0]) . ucfirst($member[1]);
        }
        return $name;
    }

}

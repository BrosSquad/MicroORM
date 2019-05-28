<?php

namespace Dusan\PhpMvc\Database\Traits;


trait UpdatedAt
{
    protected $updated_at;

    public function getUpdatedAt()
    {
        return $this->getDateTime('updated_at');
    }

    protected function setUpdatedAt($updated_at)
    {
        $this->updated_at = $this->parseCarbon($updated_at);
    }
}

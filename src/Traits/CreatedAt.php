<?php


namespace Dusan\PhpMvc\Database\Traits;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Dusan\PhpMvc\Models\Traits\DateTimeParse;
use Exception;

trait CreatedAt
{
    /**
     * @var CarbonImmutable
     */
    protected $created_at;


    public function __construct()
    {
        parent::__construct();
        $this->created_at = CarbonImmutable::now();
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param CarbonImmutable|string $created_at
     * @throws \Exception
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $this->parseCarbon($created_at);
    }
}

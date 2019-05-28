<?php

namespace Dusan\PhpMvc\Tests\Database\FluentApi;


use Dusan\PhpMvc\Database\Drivers\MySqlDatabase;
use Dusan\PhpMvc\Tests\Models\User;
use Dusan\PhpMvc\Tests\PhpMvcTestCase;
use Exception;

class FluentApiGroupByTest extends PhpMvcTestCase
{
    public function testGroupBy() {
        try {
            $sql = User::query($this->container->get(MySqlDatabase::class))
                ->select(['name'])
                ->groupBy(['name'])
                ->having('name', 'LIKE', 'DUSAN')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name FROM users GROUP BY name HAVING name LIKE :name', $sql);
        } catch (Exception $e) {
            $this->fail(sprintf("Message:{$e->getMessage()}\n Line: {$e->getFile()}\n Trace: {$e->getTraceAsString()}\n"));
        }
    }
}
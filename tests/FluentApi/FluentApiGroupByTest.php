<?php

namespace Dusan\PhpMvc\Tests\Database\FluentApi;


use Dusan\PhpMvc\Tests\Database\DatabaseTest;
use Dusan\PhpMvc\Tests\Database\Models\User;
use Exception;
use PHPUnit\Framework\AssertionFailedError;

class FluentApiGroupByTest extends DatabaseTest
{
    public function testGroupBy() {
        try {
            $sql = User::query()
                ->select(['name'])
                ->groupBy(['name'])
                ->having('name', 'LIKE', 'DUSAN')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name FROM users GROUP BY name HAVING name LIKE :name', $sql);
        } catch (Exception $e) {
            try {
                $this->fail(sprintf("Message:{$e->getMessage()}\n Line: {$e->getFile()}\n Trace: {$e->getTraceAsString()}\n"));
            } catch (AssertionFailedError $e) {
            }
        }
    }
}

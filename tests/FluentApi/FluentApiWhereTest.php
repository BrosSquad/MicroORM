<?php

namespace Dusan\PhpMvc\Tests\Database\FluentApi;


use Dusan\PhpMvc\Tests\Database\DatabaseTest;
use Dusan\PhpMvc\Tests\Database\Models\User;
use Exception;

class FluentApiWhereTest extends DatabaseTest
{
    public function testAllWhere() {
        try {
            $sql = User::query()
                ->select(['name', 'surname'])
                ->where('name', 'LIKE', 'Dusan')
                ->orWhere('surname', '=', 'Malusev')
                ->andWhere('id', '=', 1)
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users  WHERE name LIKE :name OR surname = :surname AND id = :id', $sql);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

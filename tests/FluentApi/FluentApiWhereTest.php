<?php

namespace Dusan\PhpMvc\Tests\Database\FluentApi;


use Dusan\PhpMvc\Database\Drivers\MySqlDatabase;
use Dusan\PhpMvc\Tests\Models\User;
use Dusan\PhpMvc\Tests\PhpMvcTestCase;

class FluentApiWhereTest extends PhpMvcTestCase
{
    public function testAllWhere() {
        try {
            $sql = User::query($this->container->get(MySqlDatabase::class))
                ->select(['name', 'surname'])
                ->where('name', 'LIKE', 'Dusan')
                ->orWhere('surname', '=', 'Malusev')
                ->andWhere('id', '=', 1)
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users  WHERE name LIKE :name OR surname = :surname AND id = :id', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
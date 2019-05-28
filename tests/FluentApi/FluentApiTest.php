<?php

namespace Dusan\PhpMvc\Tests\Database\FluentApi;


use Dusan\PhpMvc\Database\Drivers\MySqlDatabase;
use Dusan\PhpMvc\Tests\Models\User;
use Dusan\PhpMvc\Tests\PhpMvcTestCase;

class FluentApiTest extends PhpMvcTestCase
{
    /**
     * @test
     */
    public function paginationTest() {
        try {
            $sql = User::query()
                ->paginate(1, 2)
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users     LIMIT :start, :end;', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     */
    public function selectTest() {
        try {
            $sql = User::query()
                ->select(['name', 'surname'])
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

    }

    public function testWhere() {
        try {
            $sql = User::query()
                ->select(['name', 'surname'])
                ->where('name', 'LIKE', 'Dusan')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users  WHERE name LIKE :name', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testWhereNull() {
        try {
            $sql = User::query()
                ->select(['name', 'surname'])
                ->whereNull('name')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users  WHERE name IS NULL', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    public function testWhereNotNull() {
        try {
            $sql = User::query()
                ->select(['name', 'surname'])
                ->whereNotNull('name')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users  WHERE name IS NOT NULL', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testOrderBy() {
        try {
            $sql = User::query()
                ->orderBy('name')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users    ORDER BY name ASC', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testOrderByDesc() {
        try {
            $sql = User::query()
                ->orderByDesc('name')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users    ORDER BY name DESC', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGroupBy() {
        try {
            $sql = User::query()
                ->select(['name'])
                ->groupBy(['name'])
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name FROM users   GROUP BY name', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testInnerJoin() {
        try {
            $sql = User::query()
                ->innerJoin('comments', 'user_id')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users INNER JOIN comments ON comments.user_id = users.id', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testLeftJoin() {
        try {
            $sql = User::query()
                ->leftJoin('comments', 'user_id')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users LEFT JOIN comments ON comments.user_id = users.id', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testInnerJoinBelongsTo() {
        try {
            $sql = User::query()
                ->innerJoin('role')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users INNER JOIN roles ON users.role_id = roles.id', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testRightJoin() {
        try {
            $sql = User::query()
                ->rightJoin('comments', 'user_id')
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT * FROM users RIGHT JOIN comments ON comments.user_id = users.id', $sql);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testLike() {
        $sql = User::query()
            ->where('name', '=', 'Dusan')
            ->limit(10)
            ->getSql();
        $this->assertNotNull($sql);
        $this->assertEquals('SELECT * FROM users  WHERE name = :name   LIMIT 10;', $sql);
    }

    public function testAll() {
        try {
            $sql = User::query()
                ->where('name', '=', 'Dusan')
                ->andWhere('surname', '=', 'Malusev')
                ->select(['name', 'surname'])
                ->innerJoin('comments')
                ->orderByDesc('name')
                ->groupBy(['name', 'surname'])
                ->limit(10)
                ->getSql();
            $this->assertNotNull($sql);
            $this->assertEquals('SELECT name, surname FROM users INNER JOIN comments ON comments.user_id = users.id WHERE name = :name AND surname = :surname GROUP BY name, surname ORDER BY name DESC LIMIT 10;', $sql);
        } catch (\ReflectionException $e) {
            $this->fail($e->getMessage());
        }
    }
}
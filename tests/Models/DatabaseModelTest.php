<?php

namespace Dusan\PhpMvc\Tests\Database\Models;

use Dusan\PhpMvc\Tests\Database\DatabaseTest;
use Exception;

class DatabaseModelTest extends DatabaseTest
{
    public function testInsertForUser()
    {
        try {
            $user = new User();
            $user->name = 'name';
            $user->surname = 'surname';
            $user->email = 'email';
            $user->password = 'password';
            $sql = $user->testInsert();
            $this->assertEquals(
                'INSERT INTO users(name, surname, email, password) VALUES (:name, :surname, :email, :password)',
                $sql,
                'Insert is ok'
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    public function testUpdateForUser()
    {
        try {
            $user = new User(['id' => 1]);
            $user->name = 'SomeOtherName';
            $user->surname = 'SomeOtherSurname';
            $user->email = 'SomeOtherEmail';
            $user->password = 'somePassw@rd';
            $sql = $user->testUpdate();
            $this->assertEquals(
                'UPDATE users SET  name=:name, surname=:surname, email=:email, password=:password WHERE id=:id',
                $sql,
                'Update is ok'
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    public function testInitializerSyntax() {
        $user = new User([
            'name' => 'Dusan',
            'surname' => 'Malusev',
            'email' => 'test@test.com'
        ]);
        $this->assertEquals('Dusan', $user->getName());
        $this->assertEquals('Malusev', $user->getSurname());
        $this->assertEquals('test@test.com', $user->getEmail());
    }
    /**
     * @test
     */
    public function sqlWithInitializerSyntax() {
        $user = new User([
            'name' => 'Dusan',
            'surname' => 'Malusev',
            'email' => 'test@test.com'
        ]);
        $sql = $user->testInsert();
        $this->assertEquals(
            'INSERT INTO users(name, surname, email, password) VALUES (:name, :surname, :email, :password)',
            $sql,
            'Insert is ok'
        );
    }
}

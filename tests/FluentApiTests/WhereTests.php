<?php


namespace BrosSquad\MicroORM\Tests\FluentApiTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\User;
use Dusan\PhpMvc\Collections\Collection;

class WhereTests extends MicroORMTestCase
{
    public function test_whereEquals()
    {
        /** @var User|null $user */
        $user = User::query()->whereEquals('email', 'test@test.com')->get()->firstOrDefault();

        if($user === NULL) {
            $this->assertTrue(true, 'No user has been found');
        }
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@test.com', $user->email);
    }

    public function test_no_model_found()
    {
        /** @var User|null $user */
        $user = User::query()->whereEquals('email', 'test@test2.com')->get()->firstOrDefault();
        $users = User::query()->whereEquals('email', 'test@test2.com')->get();

        $this->assertNull($user);
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertIsArray($users->getArray());
    }

}

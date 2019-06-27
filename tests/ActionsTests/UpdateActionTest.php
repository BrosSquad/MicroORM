<?php


namespace BrosSquad\MicroORM\Tests\ActionsTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\User;

class UpdateActionTest extends MicroORMTestCase
{
    public function test_update()
    {
        /** @var User|null $user */
        $user = User::query()->whereEquals('email', 'test@test.com')->get()->firstOrDefault();


        $user->name = 'Dusan';

        $this->assertTrue($user->update());
    }
}

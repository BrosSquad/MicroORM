<?php


namespace BrosSquad\MicroORM\Tests\ActionsTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\User;

class DeleteActionTest extends MicroORMTestCase
{
    public function test_delete()
    {
        /** @var User|null $user */
        $user = User::query()->whereEquals('email', 'test@test.com')->get()->firstOrDefault();



        if($user !== null)
        {
            $this->assertTrue($user->delete(), 'Model deleted');
        }

    }

    public function test_delete_on_static() {

    }
}

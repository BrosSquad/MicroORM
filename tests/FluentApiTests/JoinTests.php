<?php


namespace BrosSquad\MicroORM\Tests\FluentApiTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\User;

class JoinTests extends MicroORMTestCase
{
    public function test_inner_join()
    {
        $users = User::query()->innerJoin('posts')->get();

        $this->assertIsArray($users->getArray());
    }
}

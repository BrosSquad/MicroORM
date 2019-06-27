<?php


namespace BrosSquad\MicroORM\Tests\ActionsTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\User;

class InsertActionTest extends MicroORMTestCase
{
    /**
     *
     */
    public function test_insert_into()
    {
        $user = new User([
            'name' => 'Test',
            'surname' => 'Test',
            'email' => 'test@test.com',
            'password' => 'test123'
        ]);

        $hasInserted = $user->insert();

        $this->assertTrue($hasInserted);
    }

    public function test_insert_with_save() {
        $user = new User([
            'name' => 'Test',
            'surname' => 'Test',
            'email' => 'test@testwithsave.com',
            'password' => 'test123'
        ]);

        $hasInserted = $user->save();

        $this->assertTrue($hasInserted);
    }
}

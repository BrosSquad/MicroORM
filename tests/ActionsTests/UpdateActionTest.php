<?php


namespace BrosSquad\MicroORM\Tests\ActionsTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\Actor;

class UpdateActionTest extends MicroORMTestCase
{
    public function test_update()
    {
        /** @var Actor $actor */
        $actor = Actor::query()->whereEquals('first_name', 'Test')->get();

        $actor = $actor[0] ?? NULL;

        if ($actor === NULL) {
            $this->fail('Run first insert action test');
        }

        $actor->first_name = 'Dusan';

        $this->assertTrue($actor->update());
    }

    public function test_update_with_save_method()
    {

        /** @var Actor $actor */
        $actor = Actor::query()->whereEquals('first_name', 'Test')->get()->firstOrDefault();

        if ($actor === NULL) {
            $this->fail('Run first insert action test');
        }

        $actor->first_name = 'Dusan';

        $this->assertTrue($actor->save());
    }
}

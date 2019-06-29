<?php


namespace BrosSquad\MicroORM\Tests\ActionsTests;


use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use BrosSquad\MicroORM\Tests\Models\Actor;
use Carbon\CarbonImmutable;

class InsertActionTest extends MicroORMTestCase
{
    /**
     *
     */
    public function test_insert_into()
    {
        $actor = new Actor([
            'first_name' => 'Test',
            'last_name' => 'Test',
            'last_update' => CarbonImmutable::now()
        ]);

        $hasInserted = $actor->insert();

        $this->assertTrue($hasInserted);
    }

    public function test_insert_with_save() {

        $actor = new Actor([
            'first_name' => 'Test2',
            'last_name' => 'Test2',
            'last_update' => CarbonImmutable::now()
        ]);

        $hasInserted = $actor->save();

        $this->assertTrue($hasInserted);
    }
}

<?php
namespace Dusan\PhpMvc\Tests\Database;

use Dusan\PhpMvc\Database\Driver;
use Dusan\PhpMvc\Database\Drivers\MySqlDatabase;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected $dbConfiguration;

    public function testDatabaseConnection()
    {
        try {
            $database = $this->container->get(MySqlDatabase::class);
            $this->assertNotNull($database);
            $this->assertInstanceOf(Driver::class, $database);
            $this->assertInstanceOf(MySqlDatabase::class, $database);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

<?php
namespace Dusan\PhpMvc\Tests\Database;

use Dusan\PhpMvc\Database\Driver;
use Dusan\PhpMvc\Database\Drivers\MySqlDatabase;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected $dbConfiguration;
    protected $driver;
    /**
     * @before
     */
    public function getInstanceOfMySqlDatabase() {
        $pdo = new PDO('mysql:dbname=cpack_dev;host=localhost;charset=utf8', 'root', 'Pa$$w0rd');
        $this->driver = new MySqlDatabase($pdo);
    }

    public function testDatabaseConnection()
    {
        try {
            $this->assertNotNull($this->driver);
            $this->assertInstanceOf(Driver::class, $this->driver);
            $this->assertInstanceOf(MySqlDatabase::class, $this->driver);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}

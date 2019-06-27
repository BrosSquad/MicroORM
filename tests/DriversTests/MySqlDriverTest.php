<?php


namespace BrosSquad\MicroORM\Tests\DriversTests;


use BrosSquad\MicroORM\Driver;
use BrosSquad\MicroORM\Drivers\MySqlDatabase;
use BrosSquad\MicroORM\Tests\MicroORMTestCase;
use PDO;
use PDOException;

class MySqlDriverTest extends MicroORMTestCase
{
    public function __construct()
    {
        parent::__construct(false);
    }

    public function test_connection_to_database()
    {
        try {
            $this->pdo = new PDO(
                'mysql:dbname='.static::DATABASE_NAME.';host='.static::HOST . ';charset=utf8',
                static::USER,
                static::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $this->driver = new MySqlDatabase($this->pdo);
            $this->assertNotNull($this->driver);
            $this->assertInstanceOf(Driver::class, $this->driver);
        }catch (PDOException $e) {
            $this->fail($e->getMessage());
        }
    }
}

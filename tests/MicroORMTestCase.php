<?php


namespace BrosSquad\MicroORM\Tests;
define('DEBUG', true);

use BrosSquad\MicroORM\Actions\Action;
use BrosSquad\MicroORM\Drivers\MySqlDatabase;
use BrosSquad\MicroORM\FluentApi\AdvancedFluent;
use PDO;
use PHPUnit\Framework\TestCase;

class MicroORMTestCase extends TestCase
{
    const DRIVER = 'mysql';
    const DATABASE_NAME = 'sakila';
    const HOST = 'localhost';
    const USER = 'microorm';
    const PASSWORD = '';

    /** @var \PDO */
    protected $pdo;

    protected $driver;

    public function __construct(bool $connect = true)
    {
        parent::__construct(NULL, [], '');

        if($connect) {
            $this->pdo = new PDO(
                static::DRIVER.':dbname='.static::DATABASE_NAME.';host='.static::HOST . ';charset=utf8',
                static::USER,
                static::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $this->driver = new MySqlDatabase($this->pdo);
            AdvancedFluent::setDatabase($this->driver);
            Action::setDatabaseDriver($this->driver);
        }

    }


}

<?php
declare(strict_types=1);

namespace test\labo86\rdtas\app;

use labo86\battle_royale\app\Config;
use labo86\battle_royale\app\DataAccessMySql;
use labo86\rdtas\app\DataAccessDb;
use labo86\rdtas\app\DataAccessDbConfig;
use labo86\rdtas\app\User;
use PHPUnit\Framework\TestCase;

class DataAccessDbTest extends TestCase
{

    private $path;

    public function setUp(): void
    {
        $this->path = tempnam(__DIR__, 'demo');

        unlink($this->path);
        mkdir($this->path, 0777);
    }

    public function tearDown(): void
    {
        exec('rm -rf ' . $this->path);
    }



    public function getDao() {
        file_put_contents($this->path . '/schema', User::DDL_TABLE_SESSIONS . User::DDL_TABLE_USERS);


        $config = new DataAccessDbConfig([
            'type' => 'sqlite',
            'name' => ':memory:',
            'schema'=> $this->path . '/schema'
        ]);
        $dao = new DataAccessDb($config);

        $dao->createTables();
        $pdo = $dao->getPDO();


        $password_hash = User::createPasswordHash('pass');
        User::createUser($pdo, 'test', 'test', $password_hash);
        return $dao;
    }

    public function test()
    {
        $dao = $this->getDao();
        $pdo = $dao->getPDO();

        $response = User::createSession($pdo, 'test', 'pass');
        $session_id = $response['session_id'];
        $this->assertEquals('ACTIVE', $response['status']);

        $this->assertEquals('test', User::getUserIdFromSessionId($pdo, $session_id));

        User::closeSession($pdo, $session_id);


    }
}

<?php
declare(strict_types=1);

namespace test\labo86\rdtas\pdo;

use labo86\exception_with_data\ExceptionWithData;
use PHPUnit\Framework\TestCase;
use function labo86\rdtas\pdo\selectAll;
use function labo86\rdtas\pdo\selectRow;
use function labo86\rdtas\pdo\sqliteDns;
use function labo86\rdtas\pdo\update;

class FunctionsTest extends TestCase
{

    public function testSelectRowFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = new \PDO(sqliteDns(":memory:"));
        update($pdo, "CREATE TABLE test (id INTEGER)");
        selectRow($pdo, "SELECT id FROM test");

    }

    public function testSelectRowOk()
    {
        $pdo = new \PDO(sqliteDns(":memory:"));
        update($pdo, "CREATE TABLE test (id INTEGER)");
        update($pdo, "INSERT INTO test VALUES(1)");
        $row  = selectRow($pdo, "SELECT id FROM test");
        $this->assertEquals(['id' => 1], $row);
    }

    public function testSelectAllEmpty()
    {
        $pdo = new \PDO(sqliteDns(":memory:"));
        update($pdo, "CREATE TABLE test (id INTEGER)");
        $result = selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([], $result);
    }

    public function testSelectAllBasic()
    {
        $pdo = new \PDO(sqliteDns(":memory:"));
        update($pdo, "CREATE TABLE test (id INTEGER)");
        update($pdo, "INSERT INTO test VALUES(1)");
        update($pdo, "INSERT INTO test VALUES(2)");
        update($pdo, "INSERT INTO test VALUES(3)");
        $result = selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], $result);
    }

    public function testExecuteFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = new \PDO(sqliteDns(":memory:"));
        update($pdo, "adfadfasdf");

    }
}

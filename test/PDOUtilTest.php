<?php
declare(strict_types=1);

namespace test\labo86\rdtas;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\PDOUtil;
use PHPUnit\Framework\TestCase;

class PDOUtilTest extends TestCase
{

    public function testSelectRowFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = new \PDO(PDOUtil::sqliteDns(":memory:"));
        PDOUtil::update($pdo, "CREATE TABLE test (id INTEGER)");
        PDOUtil::selectRow($pdo, "SELECT id FROM test");

    }

    public function testSelectRowOk()
    {
        $pdo = new \PDO(PDOUtil::sqliteDns(":memory:"));
        PDOUtil::update($pdo, "CREATE TABLE test (id INTEGER)");
        PDOUtil::update($pdo, "INSERT INTO test VALUES(1)");
        $row  = PDOUtil::selectRow($pdo, "SELECT id FROM test");
        $this->assertEquals(['id' => 1], $row);
    }

    public function testSelectAllEmpty()
    {
        $pdo = new \PDO(PDOUtil::sqliteDns(":memory:"));
        PDOUtil::update($pdo, "CREATE TABLE test (id INTEGER)");
        $result = PDOUtil::selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([], $result);
    }

    public function testSelectAllBasic()
    {
        $pdo = new \PDO(PDOUtil::sqliteDns(":memory:"));
        PDOUtil::update($pdo, "CREATE TABLE test (id INTEGER)");
        PDOUtil::update($pdo, "INSERT INTO test VALUES(1)");
        PDOUtil::update($pdo, "INSERT INTO test VALUES(2)");
        PDOUtil::update($pdo, "INSERT INTO test VALUES(3)");
        $result = PDOUtil::selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], $result);
    }

    public function testExecuteFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = new \PDO(PDOUtil::sqliteDns(":memory:"));
        PDOUtil::update($pdo, "adfadfasdf");

    }
}

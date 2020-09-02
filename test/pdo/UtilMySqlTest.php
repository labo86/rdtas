<?php
declare(strict_types=1);

namespace test\labo86\rdtas\pdo;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\pdo\Util;
use PHPUnit\Framework\TestCase;
class UtilMySqlTest extends TestCase
{

    public function getPDO() {
        $pdo = new \PDO(Util::mysqlDns(""), "phpunit_test_user", "phpunit_test_password");
        Util::update($pdo, "DROP DATABASE IF EXISTS phpunit_test_db; CREATE DATABASE phpunit_test_db CHARACTER SET utf8 COLLATE utf8_general_ci; USE phpunit_test_db");
        return $pdo;
    }

    public function testSelectRowFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = $this->getPDO();
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        Util::selectRow($pdo, "SELECT id FROM test");

    }

    public function testSelectRowOk()
    {
        $pdo = $this->getPDO();
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        Util::update($pdo, "INSERT INTO test VALUES(1)");
        $row = Util::selectRow($pdo, "SELECT id FROM test");
        $this->assertEquals(['id' => 1], $row);
    }

    public function testSelectAllEmpty()
    {
        $pdo = $this->getPDO();
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        $result = Util::selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([], $result);
    }

    public function testSelectAllBasic()
    {
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        Util::update($pdo, "INSERT INTO test VALUES(1)");
        Util::update($pdo, "INSERT INTO test VALUES(2)");
        Util::update($pdo, "INSERT INTO test VALUES(3)");
        $result = Util::selectAll($pdo, "SELECT id FROM test");
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], $result);
    }

    public function testExecuteFail()
    {
        $this->expectException(ExceptionWithData::class);
        $this->expectExceptionMessage('error executing query');
        $pdo = $this->getPDO();
        Util::update($pdo, "adfadfasdf");

    }

    public function testExecuteUpdate() {
        $table = <<<EOF
CREATE TABLE tournament_data (
tournament_id VARCHAR(36),
name TEXT,
data TEXT,
state TEXT,
current_round INTEGER,
PRIMARY KEY (tournament_id)
)
EOF;
        $pdo = $this->getPDO();
        Util::update($pdo, $table);
        $tournament_id = 'id';
        $data ="1"; //"{\n    \"id\": \"test_5f501882e7abc\",\n    \"name\": \"hola\",\n    \"current_round\": -1,\n    \"state\": \"LOBBY\",\n    \"challenger_list\": [],\n    \"round_list\": []\n}";

        Util::update($pdo,"INSERT INTO tournament_data (tournament_id) VALUES (:tournament_id)", ['tournament_id' => $tournament_id]);
        Util::update($pdo,
            "UPDATE tournament_data SET data = :data, current_round = :current_round WHERE tournament_id = :tournament_id",
            [':data' => $data, ':current_round' => -1, ':tournament_id' =>$tournament_id]);
        $row = Util::selectRow($pdo, 'SELECT data, current_round, tournament_id FROM tournament_data WHERE tournament_id = :tournament_id', [':tournament_id' => $tournament_id]);
        $this->assertEquals(['data' => $data, 'current_round' => -1, 'tournament_id' => 'id'], $row);

    }
}

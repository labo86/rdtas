<?php
declare(strict_types=1);

namespace test\labo86\rdtas\pdo;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\pdo\Util;
use PHPUnit\Framework\TestCase;
class UtilTest extends TestCase
{

    public function testSelectRowFail()
    {
        $this->expectException(ExceptionWithData::class);
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        Util::selectRow($pdo, "SELECT id FROM test");

    }

    public function testSelectRowOk()
    {
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
        Util::update($pdo, "CREATE TABLE test (id INTEGER)");
        Util::update($pdo, "INSERT INTO test VALUES(1)");
        $row = Util::selectRow($pdo, "SELECT id FROM test");
        $this->assertEquals(['id' => 1], $row);
    }

    public function testSelectAllEmpty()
    {
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
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
        $this->expectExceptionMessage('error preparing exception');
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
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
        $pdo = new \PDO(Util::sqliteDns(":memory:"));
        Util::update($pdo, $table);
        $tournament_id = 'id';
        Util::update($pdo,'INSERT INTO tournament_data (tournament_id) VALUES (:tournament_id)', $tournament_id);
        Util::update($pdo,
            "UPDATE tournament_data SET data = :data, current_round = :current_round WHERE tournament_id = :tournament_id",
            'some data',
            2,
            $tournament_id);
        $row = Util::selectRow($pdo, 'SELECT data, current_round, tournament_id FROM tournament_data WHERE tournament_id = :tournament_id', $tournament_id);
        $this->assertEquals(['data' => 'some data', 'current_round' => 2, 'tournament_id' => 'id'], $row);

    }
}

<?php
declare(strict_types=1);

namespace labo86\rdtas\pdo;


use labo86\exception_with_data\ExceptionWithData;
use labo86\exception_with_data\Util as UtilExcep;
use labo86\rdtas\ErrMsg;
use PDO;
use PDOStatement;

class Util
{

    public static function sqliteDns(string $filename)
    {
        return sprintf('sqlite:%s', $filename);
    }

    /**
     * @param string $db_name
     * @param string|string $host
     * @return string
     */
    public static function mysqlDns(string $db_name, string $host = 'localhost')
    {
        return sprintf('mysql:host=%s;dbname=%s;charset=utf8', $host, $db_name);
    }

    /**
     * @param PDOStatement $stmt
     * @param mixed ...$args
     * @return PDOStatement
     * @throws ExceptionWithData
     */
    public static function execute(PDOStatement $stmt, array $args = []) : PDOStatement
    {

        if ($stmt->execute($args))
            return $stmt;

        throw new ExceptionWithData(ErrMsg::ERROR_EXECUTING_QUERY, [
            'pdo_error' => $stmt->errorInfo(),
            'query' => $stmt->queryString,
            'args' => $args
        ]);
    }

    public static function fetchAll(PDOStatement $stmt, array $args = []): array
    {
        self::execute($stmt, $args);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function fetchRow(PDOStatement $stmt, array $args = []): array
    {
        self::execute($stmt, $args);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        } else throw new ExceptionWithData(ErrMsg::SELECT_RETURNS_NO_ROWS, [
            'query' => $stmt->queryString,
            'args' => $args
        ]);
    }

    public static function prepare(PDO $pdo, string $query): PDOStatement
    {
        $stmt = $pdo->prepare($query);
        if ($stmt === FALSE) {
            throw new ExceptionWithData(ErrMsg::ERROR_PREPARING_EXCEPTION, [
                'pdo_error' => $pdo->errorInfo(),
                'query' => $query
            ]);
        }
        return $stmt;
    }

    public static function selectRow(PDO $pdo, string $query, array $args = []): array
    {   try {
            $stmt = self::prepare($pdo, $query);
            return self::fetchRow($stmt, $args);
        } catch ( ExceptionWithData $exception ) {
            throw UtilExcep::rethrow(ErrMsg::ERROR_AT_SELECTING_ROW, [
                'query' => $query,
                'args' => $args ], $exception);
        }
    }

    public static function selectAll(PDO $pdo, string $query, array $args = []): array
    {
        try {
            $stmt = self::prepare($pdo, $query);
            return self::fetchAll($stmt, $args);
        } catch ( ExceptionWithData $exception ) {
            throw UtilExcep::rethrow(ErrMsg::ERROR_AT_SELECTING_ALL_ROWS, [
                'query' => $query,
                'args' => $args ], $exception);
        }
    }

    public static function update(PDO $pdo, string $query, array $args = []) : PDOStatement
    {
        try {
            $stmt = self::prepare($pdo, $query);
            return self::execute($stmt, $args);
        } catch ( ExceptionWithData $exception ) {
            throw UtilExcep::rethrow(ErrMsg::ERROR_AT_UPDATING, [
                'query' => $query,
                'args' => $args ], $exception);
        }
    }

    public static function updateOne(PDO $pdo, string $query, array $args = []) : PDOStatement {
        $stmt = self::update($pdo, $query, $args);
        $rowCount = $stmt->rowCount();
        if ( $rowCount < 0 ) {
            throw new ExceptionWithData(ErrMsg::NO_ROW_HAS_CHANGED, [
                'query' => $query,
                'args' => $args,
                'row_count' => $rowCount
            ]);
        }
        return $stmt;
    }

}
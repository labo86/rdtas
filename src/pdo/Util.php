<?php
declare(strict_types=1);

namespace labo86\rdtas\pdo;


use labo86\exception_with_data\ExceptionWithData;
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
    public static function execute(PDOStatement $stmt, array $args = [])
    {

        if ($stmt->execute($args))
            return $stmt;

        throw new ExceptionWithData('error executing query', [
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
        } else throw new ExceptionWithData('select return no rows', [
            'query' => $stmt->queryString,
            'args' => $args
        ]);
    }

    public static function prepare(PDO $pdo, string $query): PDOStatement
    {
        $stmt = $pdo->prepare($query);
        if ($stmt === FALSE) {
            throw new ExceptionWithData('error preparing exception', [
                'pdo_error' => $pdo->errorInfo(),
                'query' => $query
            ]);
        }
        return $stmt;
    }

    public static function selectRow(PDO $pdo, string $query, array $args = []): array
    {
        $stmt = self::prepare($pdo, $query);
        return self::fetchRow($stmt, $args);
    }

    public static function selectAll(PDO $pdo, string $query, array $args = []): array
    {
        $stmt = self::prepare($pdo, $query);
        return self::fetchAll($stmt, $args);
    }

    public static function update(PDO $pdo, string $query, array $args = [])
    {
        $stmt = self::prepare($pdo, $query);
        self::execute($stmt, $args);
    }

}
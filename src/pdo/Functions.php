<?php
declare(strict_types=1);

namespace labo86\rdtas\pdo;


use labo86\exception_with_data\ExceptionWithData;
use PDO;
use PDOStatement;

function sqliteDns(string $filename) {
    return sprintf('sqlite:%s', $filename);
}

function mysqlDsn(string $db_name, string $host = 'localhost') {
    return sprintf('mysql:host=%s;dbname=%s;charset=utf8', $host, $db_name);
}

/**
 * @param PDOStatement $stmt
 * @param mixed ...$args
 * @return PDOStatement
 * @throws ExceptionWithData
 */
function execute(PDOStatement $stmt, ...$args) {
    if ( $stmt->execute($args) )
        return $stmt;

    throw new ExceptionWithData('error executing query', [
        'pdo_error' => $stmt->errorInfo(),
        'query' => $stmt->queryString,
        'args' => $args
    ]);
}

function fetchAll(PDOStatement $stmt, ...$args) : array {
    execute($stmt, ...$args);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

function fetchRow(PDOStatement $stmt, ...$args) : array {
    execute($stmt);

    if ( $row = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
        return $row;
    } else throw new ExceptionWithData('select return no rows', [
        'query' => $stmt->queryString,
        'args' => $args
    ]);
}

function prepare(PDO $pdo, string $query) : PDOStatement {
    $stmt = $pdo->prepare($query);
    if ( $stmt === FALSE ) {
        throw new ExceptionWithData('error preparing exception', [
            'pdo_error' => $pdo->errorInfo(),
            'query' => $query
        ]);
    }
    return $stmt;
}

function selectRow(PDO $pdo, string $query, ...$args) : array {
    $stmt = prepare($pdo, $query);
    return fetchRow($stmt, ...$args);
}

function selectAll(PDO $pdo, string $query, ...$args) : array {
    $stmt = prepare($pdo, $query);
    return fetchAll($stmt, ...$args);
}

function update(PDO $pdo, string $query, ...$args) {
    $stmt = prepare($pdo, $query);
    execute($stmt, ...$args);
}
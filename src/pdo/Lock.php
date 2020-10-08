<?php
declare(strict_types=1);

namespace labo86\rdtas\pdo;

use PDO;

abstract class Lock
{
    protected string $name;

    protected PDO $pdo;

    public function __construct(PDO $pdo, string $name) {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    abstract function acquire() : bool;


}
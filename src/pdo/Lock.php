<?php
declare(strict_types=1);

namespace labo86\rdtas\pdo;

use PDO;

class Lock
{
    protected string $name;

    protected PDO $pdo;

    public function __construct(PDO $pdo, string $name) {
        $this->pdo = $pdo;
        $this->name = $name;
    }

    public function acquire() : bool {
        return true;
    }


}
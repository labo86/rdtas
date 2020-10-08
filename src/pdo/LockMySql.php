<?php
declare(strict_types=1);


namespace labo86\rdtas\pdo;


use PDO;

/**
 * Los lock funcionan a nivel de session mysql
 * Session es cada vez que se logea un usuario sql.
 * Se liberan cuando se cierra la sessión
 * Class LockMySql
 * @package labo86\rdtas\pdo
 */
class LockMySql extends Lock
{
    protected bool $acquired = false;

    public function __construct(PDO $pdo, string $name) {
        parent::__construct($pdo, $name);
    }

    public function acquire() : bool {
        if ( $this->acquired )
            return $this->acquired;
        /**
         * Returns 1 if the lock was obtained successfully,
         * 0 if the attempt timed out (for example, because another client has previously locked the name),
         * or NULL if an error occurred (such as running out of memory or the thread was killed with mysqladmin kill).
         */
        $stmt = Util::prepare($this->pdo, 'SELECT GET_LOCK(:name,2)');
        $stmt = Util::execute($stmt, ['name' => $this->name]);
        $status = $stmt->fetchColumn(0);
        if ( $status === "1" )
            $this->acquired = true;
        return $this->acquired;
    }

    public function __destruct() {
        if ( $this->acquired ) {
            Util::update($this->pdo, "SELECT RELEASE_LOCK(:name)", ['name' => $this->name]);
            $this->acquired = false;
        }
    }
}
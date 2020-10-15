<?php
declare(strict_types=1);

namespace labo86\rdtas\app;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ErrMsg;
use labo86\rdtas\pdo\Lock;
use labo86\rdtas\pdo\LockMySql;
use labo86\rdtas\pdo\Util as UtilPDO;
use PDO;

class DataAccessDb
{

    const TYPE_MYSQL = 'mysql';
    const TYPE_SQLITE = 'sqlite';

    protected DataAccessDbConfig $config;

    private PDO $pdo;

    public function __construct(DataAccessDbConfig $config) {
        $this->config = $config;
    }

    public function getConfig() : DataAccessDbConfig {
        return $this->config;
    }

    public function getPDO() : PDO {
        if ( !isset($this->pdo) ) {
            $config = $this->getConfig();
            $database_type = $config->getType();
            if ( $database_type === self::TYPE_MYSQL ) {
                $database_name = $config->getName();
                $database_user = $config->getUser();
                $database_password = $config->getPassword();
                $this->pdo = new PDO(UtilPDO::mysqlDns($database_name), $database_user, $database_password);
            } else if ( $database_type === self::TYPE_SQLITE ) {
                $database_name = $config->getName();
                $this->pdo = new PDO(UtilPDO::sqliteDns($database_name));
            }
        }
        return $this->pdo;
    }

    /**
     * Solo para UBUNTU
     */
    public function createCredentials() {
         if ( $this->getConfig()->getType() !== 'mysql')
             return;

         $database_name = $this->getConfig()->getName();
         $database_user = $this->getConfig()->getUser();
         $database_password = $this->getConfig()->getPassword();

        $script_contents = sprintf(<<<'EOF'
CREATE USER '%s'@'localhost' IDENTIFIED BY '%s';
GRANT ALL PRIVILEGES ON %s.* TO '%s'@'localhost';
EOF,
        $database_user,
        $database_password,
        $database_name,
        $database_user);

        $script_filename = tempnam("/tmp", "credentials");
        if ( $script_filename === FALSE )
            throw new ExceptionWithData(ErrMsg::ERROR_CREATING_USER, [
                'filename' => $script_filename,
                'content' => $script_contents
            ]);
        file_put_contents($script_filename, $script_contents);


        passthru(sprintf('mysql --defaults-file=/etc/mysql/debian.cnf < %s', $script_filename));
        unlink($script_filename);
    }

    public function createDatabase() {
        if ( $this->getConfig()->getType() !== 'mysql')
            return;

        $database_name = $this->getConfig()->getName();
        $script_contents = sprintf(<<<'EOF'
CREATE DATABASE %s CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF,
            $database_name);

        $sql_script = tempnam("/tmp", "sql");
        if ( $sql_script === FALSE )
            throw new ExceptionWithData(ErrMsg::ERROR_CREATING_DATABASE, [
                'filename' => $sql_script,
                'content' => $script_contents
            ]);
        file_put_contents($sql_script, $script_contents);


        passthru(sprintf('mysql --defaults-file=/etc/mysql/debian.cnf < %s', $sql_script));
        unlink($sql_script);
    }

    public function createTables() {
        $config = $this->getConfig();
        $schema_filename = $config->getSchema();
        $pdo = $this->getPDO();
        $schema = file_get_contents($schema_filename);
        $pdo->exec($schema);
        $user_id = uniqid();
        User::createUser($pdo, $user_id, 'admin', User::createPasswordHash('pass'));
        User::setUserType($pdo, $user_id, 'ADMIN');
    }

    public function getLock(string $name) : Lock {

        $config = $this->getConfig();
        $database_type = $config->getType();
        if ( $database_type === self::TYPE_MYSQL ) {
            return new LockMySql($this->getPDO(), $name);
        } else {
            return new Lock($this->getPDO(),$name);
        }

    }
}
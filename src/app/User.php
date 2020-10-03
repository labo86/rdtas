<?php
declare(strict_types=1);


namespace labo86\rdtas\app;


use DateInterval;
use DateTime;
use labo86\exception_with_data\ExceptionWithData;
use labo86\exception_with_data\Util;
use labo86\rdtas\pdo\Util as UtilPDO;
use PDO;
use Throwable;

class User
{

    const SESSIONS_TABLE_DDL = <<<EOF
create table sessions
(
	session_id varchar(36) not null
		primary key,
	user_id varchar(36) null,
	creation_date datetime null,
	expiration_date datetime null,
	state varchar(36) null
);
EOF;

    const USERS_TABLE_DDL = <<<EOF
create table users
(
	user_id varchar(36) not null
		primary key,
	name varchar(36) null,
	password_hash varchar(255) null,
	email varchar(100) null,
	type varchar(10) null
);
EOF;


    const DATE_FORMAT = "Y-m-d H:i:s";

    public static function getSession(PDO $pdo, string $session_id) : array {

        try {
            $row = UtilPDO::selectRow($pdo, 'SELECT user_id, creation_date, expiration_date, state FROM sessions WHERE session_id = :session_id', [
                'session_id' => $session_id
            ]);

            return $row;
        } catch ( Throwable $exception ) {
            throw Util::rethrow('SESSION_DOES_NOT_EXIST', [
                'session_id' => $session_id
            ], $exception);

        }
    }

    public static function getUser(PDO $pdo, string $user_id) : array {
        try {
            $row = UtilPDO::selectRow($pdo, 'SELECT user_id, name, type FROM users WHERE user_id = :user_id', [
                'user_id' => $user_id
            ]);

            return $row;
        } catch ( Throwable $exception ) {
            throw Util::rethrow('USER_DOES_NOT_EXIST', [
                'user_id' => $user_id
            ], $exception);
        }

    }

    public static function getUserByName(PDO $pdo, string $name) : array {
        try {
            $row = UtilPDO::selectRow($pdo, 'SELECT user_id, name, type FROM users WHERE name = :name', [
                'name' => $name
            ]);

            return $row;
        } catch ( Throwable $exception ) {
            throw Util::rethrow('USER_DOES_NOT_EXIST', [
                'name' => $name
            ], $exception);
        }

    }

    public static function createUser(PDO $pdo, string $user_id, string $username, string $password_hash) : array {
        UtilPDO::updateOne($pdo,"INSERT INTO users (user_id, name, password_hash, type) VALUES (:user_id, :name, :password_hash, :type)", [
            'user_id' => $user_id,
            'name' => $username,
            'password_hash' => $password_hash,
            'type' => 'REGISTERED'
        ]);
        return [];
    }

    public static function validateUserType(PDO $pdo, string $user_id, string $required_type) : array {
        $user = self::getUser($pdo, $user_id);
        $type = $user['type'];
        if ( $user['type'] !== $required_type ) {
            throw new ExceptionWithData('USER_DOES_NOT_HAVE_PERMISSION', [
                'user_id' =>  $user_id,
                'required_type' => $required_type,
                'user_type' => $type
            ]);
        }
        return $user;
    }

    public static function createSession(PDO $pdo, string $username, string $password) : array {
        try {
            $row = UtilPDO::selectRow($pdo, "SELECT user_id, name, password_hash FROM users WHERE name = :name", [
                'name' => $username
            ]);
        } catch ( Throwable $exception ) {
            throw Util::rethrow('USER_DOES_NOT_EXIST', [
                'username' => $username
            ], $exception);
        }

        $session_id = md5(microtime());
        $password_hash = $row['password_hash'];
        if ( !password_verify($password, $password_hash) ) {
            throw new ExceptionWithData('WRONG_PASSWORD', [
                'username' => $username,
                'password' => $password
            ]);
        }

        $user_id = $row['user_id'];
        $date = new DateTime();
        $creation_date = $date->format(self::DATE_FORMAT);
        $date->add(new DateInterval('P1D'));
        $expiration_date =  $date->format(self::DATE_FORMAT);
        UtilPDO::updateOne($pdo, 'INSERT INTO sessions (session_id, user_id, creation_date, expiration_date, state) VALUES (:session_id, :user_id, :creation_date, :expiration_date, :state)',
            [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'creation_date' => $creation_date,
                'expiration_date' => $expiration_date,
                'state' => 'ACTIVE'
            ]);

        return [
            'session_id' => $session_id,
            'user_id' => $user_id,
            'creation_date' => $creation_date,
            'expiration_date' => $expiration_date,
            'state' => 'ACTIVE'
        ];
    }

    /**
     * Funcion de utilidad.
     * Siempre se me olvida que utilizar.
     * Ahora usa BCRYPT
     * Usa la funcion {@see password_hash()}
     * Para validar un password use {@see password_verify()}
     * @param string $password
     * @return string
     */
    public static function createPasswordHash(string $password) : string {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function validateSession(PDO $pdo, string $session_id) : array {
        $session = self::getSession($pdo, $session_id);
        $user_id = $session['user_id'];
        $state = $session['state'];
        if ( $state !== 'ACTIVE' )
            throw new ExceptionWithData('SESSION_INACTIVE', [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'state' => $state
            ]);


        $expiration_date = DateTime::createFromFormat(self::DATE_FORMAT, $session['expiration_date']);
        $current_date = new DateTime();
        if ( $expiration_date < $current_date ) {
            throw new ExceptionWithData('SESSION_EXPIRED', [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'state' => $state,
                'current_date' => $current_date->format(self::DATE_FORMAT),
                'expiration_date' => $expiration_date->format(self::DATE_FORMAT)
            ]);
        }

        return $session;
    }

    /**
     * Esta funcion {@see validateSession() valida} y obtiene el user id
     * @param PDO $pdo
     * @param string $session_id
     * @return string
     * @throws ExceptionWithData
     */
    public static function getUserIdFromSessionId(PDO $pdo, string $session_id) : string {
        $session = self::validateSession($pdo, $session_id);
        return $session['user_id'];
    }

    /**
     * @param PDO $pdo
     * @param string $session_id
     * @return array la informacion del usuario
     * @throws ExceptionWithData
     */
    public static function validateAdminFromSessionId(PDO $pdo, string $session_id) : array {
        $user_id = self::getUserIdFromSessionId($pdo, $session_id);
        return self::validateUserType($pdo, $user_id, 'ADMIN');
    }

    public static function closeSession(PDO $pdo, string $session_id) {
        UtilPDO::updateOne($pdo, 'UPDATE sessions SET state = :state WHERE session_id = :session_id',
            [
                'state' => 'CLOSED',
                'session_id' => $session_id
        ]);
    }

    public static function setUserType(PDO $pdo, string $user_id, string $type) {
        UtilPDO::updateOne($pdo, 'UPDATE users SET type = :type WHERE user_id = :user_id',
            [
                'type' => $type,
                'user_id' => $user_id
            ]);
    }


    public static function setUserPassword(PDO $pdo, string $user_id, string $password_hash) {
        UtilPDO::updateOne($pdo, 'UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id',
            [
                'password_hash' => $password_hash,
                'user_id' => $user_id
            ]);
    }

}
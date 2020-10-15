<?php
declare(strict_types=1);

namespace test\labo86\rdtas\app;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\app\User;
use labo86\rdtas\ErrMsg;
use labo86\rdtas\pdo\Util;
use PDO;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{


    public function getPDO() : PDO {
        $pdo = new PDO(Util::sqliteDns(":memory:"));
        Util::update($pdo, User::DDL_TABLE_USERS);
        Util::update($pdo,  User::DDL_TABLE_SESSIONS);
        $user_id = 'test';
        $username = 'test';
        $password_hash = password_hash('pass',  PASSWORD_DEFAULT);
        User::createUser($pdo, $user_id, $username, $password_hash);
        return $pdo;
    }

    public function testGetSession()
    {
        $pdo = $this->getPDO();
        $session = User::createSession($pdo,'test', 'pass');
        $session_again = User::getSession($pdo, $session['session_id']);
        unset($session['session_id']);
        $this->assertEquals($session, $session_again);
    }

    public function testGetUserByName()
    {
        $pdo = $this->getPDO();
        $user = User::getUserByName($pdo,'test');
        $this->assertEquals([
            'name' => 'test',
            'type' => 'REGISTERED',
            'user_id' => 'test'
        ], $user);
    }

    public function testGetUserByNameUnexistent()
    {
        $this->expectExceptionMessage('USER_DOES_NOT_EXIST');
        try {
            $pdo = $this->getPDO();
            User::getUserByName($pdo,'unexistent');
            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertEquals('unexistent',$data['name']);
            throw $exception;
        }
    }

    public function testGetSessionUnexistent()
    {
        $this->expectExceptionMessage('SESSION_DOES_NOT_EXIST');
        try {
            $pdo = $this->getPDO();
            User::getSession($pdo,'test');

            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertEquals('test', $data['session_id']);
            throw $exception;
        }
    }

    public function testValidateSessionClosed()
    {
        $this->expectExceptionMessage('SESSION_INACTIVE');
        try {
            $pdo = $this->getPDO();
            $session = User::createSession($pdo,'test', 'pass');
            User::closeSession($pdo, $session['session_id']);

            User::validateSession($pdo, $session['session_id']);

            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertArrayHasKey('session_id', $data);
            unset($data['session_id']);
            $this->assertEquals([
                'user_id' => 'test',
                'status' => 'CLOSED'
            ], $data);
            throw $exception;
        }
    }

    public function testValidateSession()
    {
        $pdo = $this->getPDO();
        $session = User::createSession($pdo,'test', 'pass');
        $result = User::validateSession($pdo, $session['session_id']);
        unset($session['session_id']);
        $this->assertEquals($session, $result);
    }

    public function testGetUserIdFromSessionId()
    {
        $pdo = $this->getPDO();
        $session = User::createSession($pdo,'test', 'pass');
        $user_id = User::getUserIdFromSessionId($pdo, $session['session_id']);
        $this->assertEquals('test', $user_id);
    }

    public function testValidateUserType()
    {
        $pdo = $this->getPDO();

        $user = User::validateUserType($pdo, 'test', 'REGISTERED');
        $this->assertEquals([
            'user_id' => 'test',
            'name' => 'test',
            'nickname' => 'test',
            'type' => 'REGISTERED'
        ], $user);
    }

    public function testValidateUserTypeInvalid() {
        $this->expectExceptionMessage(ErrMsg::USER_DOES_NOT_HAVE_PERMISSION);
        try {
            $pdo = $this->getPDO();
            User::validateUserType($pdo, 'test', 'ADMIN');

            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertEquals([
                'user_id' => 'test',
                'user_type' => 'REGISTERED',
                'required_type' => 'ADMIN'
            ], $data);
            throw $exception;
        }

    }

    public function testCreateSession()
    {
        $pdo = $this->getPDO();
        $session = User::createSession($pdo,'test', 'pass');
        $this->assertEquals('test', $session['user_id']);
        $user = User::getUser($pdo, $session['user_id']);
        $this->assertEquals('test', $user['user_id']);
        $this->assertEquals('test', $user['name']);
    }

    public function testCreateSessionWrongPassword()
    {
        $this->expectExceptionMessage('WRONG_PASSWORD');
        try {
            $pdo = $this->getPDO();
            User::createSession($pdo,'test', 'wrong');
            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertEquals([
                'username' => 'test',
                'password' => 'wrong'
            ], $data);
            throw $exception;
        }
    }

    public function testValidateAdminFromSessionId()
    {
        $pdo = $this->getPDO();
        User::setUserType($pdo, 'test', 'ADMIN');

        $session = User::createSession($pdo, 'test', 'pass');
        $user = User::validateAdminFromSessionId($pdo, $session['session_id']);
        $this->assertEquals([
            'user_id' => 'test',
            'nickname' => 'test',
            'name' => 'test',
            'type' => 'ADMIN'
        ], $user);
    }

    public function testValidateAdminFromSessionIdNotValid()
    {
        $this->expectExceptionMessage(ErrMsg::USER_DOES_NOT_HAVE_PERMISSION);
        try {
            $pdo = $this->getPDO();

            $session = User::createSession($pdo, 'test', 'pass');
            User::validateAdminFromSessionId($pdo, $session['session_id']);

            $this->fail('This should fail');
        } catch ( ExceptionWithData $exception ) {
            $data = $exception->getData();
            $this->assertEquals([
                'required_type'=> 'ADMIN',
                'user_type' => 'REGISTERED',
                'user_id' => 'test'
            ], $data);
            throw $exception;
        }

    }

    public function testCreateUser()
    {
        $pdo = $this->getPDO();
        $user_id = 'a';
        $username = 'test2';
        $password_hash = password_hash('pass',  PASSWORD_DEFAULT);

        User::createUser($pdo, $user_id, $username, $password_hash);

        $user = User::getUser($pdo, $user_id);
        $this->assertEquals($user['name'], $username);
    }

    public function testUserPassword() {
        $pdo = $this->getPDO();
        $session = User::createSession($pdo, 'test', 'pass');
        $password_hash = password_hash('pass2',  PASSWORD_DEFAULT);
        User::setUserPassword($pdo, 'test', $password_hash);

        $session2 = User::createSession($pdo, 'test', 'pass2');

        $this->assertEquals($session['user_id'], $session2['user_id']);

    }
}

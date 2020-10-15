<?php
declare(strict_types=1);

namespace labo86\rdtas\app;

use DateInterval;
use DateTime;
use labo86\hapi\Controller;
use labo86\hapi\Request;
use labo86\hapi\Response;
use labo86\hapi\ResponseJson;

abstract class ServicesBasic
{
    /**
     * Implementar para obtener el data access del usuario
     * @return DataAccessDb
     */
    abstract public function getDataAccessUser() : DataAccessDb ;

    /**
     * Implementar para obtener el data acces de error
     * @return DataAccessFolder
     */
    abstract public function getDataAccessError() : DataAccessError;

    function registerServicesUser(Controller $controller) {
        $controller->getServiceMap()
            ->registerService('create_session', function(Request  $request) : Response {
                $username = $request->getStringParameter('username');
                $password = $request->getStringParameter('password');
                return $this->create_session($username, $password);
            })
            ->registerService('close_session', function(Request  $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                return $this->close_session($session_id);
            })
            ->registerService('get_user_by_session_id', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                return $this->get_user_by_session_id($session_id);
            })
            ->registerService('create_session_guest', function(Request $request) : Response {
                $nickname = $request->getStringParameter('nickname');
                return $this->create_session_guest($nickname);
            });
    }

    function registerServicesUserAdmin(Controller $controller) {
        $self = $this;
        $controller->getServiceMap()
            ->registerService('create_user', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                $username = $request->getStringParameter('username');
                $password = $request->getStringParameter('password');
                return $this->create_user($session_id, $username, $password);
            })
            ->registerService('set_user_type', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                $username = $request->getStringParameter('username');
                $type = $request->getStringParameter('type');
                return $this->set_user_type($session_id, $username, $type);
            })
            ->registerService('set_user_password', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                $username = $request->getStringParameter('username');
                $password = $request->getStringParameter('password');
                return $this->set_user_password($session_id, $username, $password);
            });

    }

    function registerServicesServer(Controller $controller) {
        $controller->getServiceMap()
            ->registerService('get_error_by_error_id', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                $error_id = $request->getStringParameter('error_id');
                return $this->get_error_by_error_id($session_id, $error_id);
            })
            ->registerService('get_error_list', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                return $this->get_error_list($session_id);
            })
            ->registerService('get_php_server_info', function(Request $request) : Response {
                $session_id = $_COOKIE['session_id'] ?? $request->getStringParameter('session_id');
                return $this->get_php_server_info($session_id);
            });
    }


    function get_error_by_error_id(string $session_id, string $error_id) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $error = $this->getDataAccessError()->getError($error_id);
        return new ResponseJson($error);

    }

    function get_error_list(string $session_id) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $error_list = $this->getDataAccessError()->getErrorList();

        return new ResponseJson($error_list);
    }

    function get_php_server_info(string $session_id) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $response = [
            'post_max_size' => ini_get ( 'post_max_size'),
            'upload_max_filesize' => ini_get ( 'upload_max_filesize'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'ini_file' => php_ini_loaded_file()
        ];

        return new ResponseJson($response);

    }

    function create_user(string $session_id, string $username, string $password) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $user_id = uniqid();
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $response = User::createUser($pdo, $user_id, $username, $password_hash);
        return new ResponseJson($response);
    }

    function set_user_nickname(string $session_id, string $username, string $nickname) : Response {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $user = User::getUserByName($pdo, $username);
        User::setUserNickname($pdo, $user['user_id'], $nickname);
        return new Response();
    }

    function set_user_type(string $session_id, string $username, string $type) : Response {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $user = User::getUserByName($pdo, $username);
        User::setUserType($pdo, $user['user_id'], $type);
        return new Response();
    }

    function set_user_password(string $session_id, string $username, string $password) : Response {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user = User::validateAdminFromSessionId($pdo, $session_id);

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $user = User::getUserByName($pdo, $username);
        User::setUserPassword($pdo, $user['user_id'], $password_hash);

        return new Response();
    }

    function create_session(string $username, string $password) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $result = User::createSession($pdo, $username, $password);
        $response = new ResponseJson($result);
        $response->setCookie('session_id', $result['session_id']);
        return $response;
    }

    function get_user_by_session_id(string $session_id) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $session = User::validateSession($pdo, $session_id);
        $user_id = $session['user_id'];
        $user = User::getUser($pdo, $user_id);
        return new ResponseJson($user);

    }

    function close_session(string $session_id) : Response
    {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        User::closeSession($pdo, $session_id);
        return new Response();
    }

    function create_session_guest(string $nickname) : ResponseJson {
        $dao = $this->getDataAccessUser();
        $pdo = $dao->getPDO();

        $user_id = uniqid();
        $name = 'guest_' . $user_id;
        \labo86\rdtas\pdo\Util::updateOne($pdo,"INSERT INTO users (user_id, name, nickname, type) VALUES (:user_id, :name, :nickname, :type)", [
            'user_id' => $user_id,
            'name' => $name,
            'nickname' => $nickname,
            'type' => 'GUEST'
        ]);

        $session_id = md5(microtime());
        $date = new DateTime();
        $creation_date = $date->format(User::DATE_FORMAT);
        $date->add(new DateInterval('P10D'));
        $expiration_date =  $date->format(User::DATE_FORMAT);
        \labo86\rdtas\pdo\Util::updateOne($pdo, 'INSERT INTO sessions (session_id, user_id, creation_date, expiration_date, status) VALUES (:session_id, :user_id, :creation_date, :expiration_date, :status)',
            [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'creation_date' => $creation_date,
                'expiration_date' => $expiration_date,
                'status' => 'ACTIVE'
            ]);

        $result = [
            'session_id' => $session_id,
            'user_id' => $user_id,
            'name' => $name,
            'nickname' => $nickname
        ];

        $response = new ResponseJson($result);
        $response->setCookie('session_id', $result['session_id']);
        return $response;
    }

}
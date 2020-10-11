<?php
declare(strict_types=1);

namespace test\labo86\rdtas\app;

use Exception;
use labo86\exception_with_data\MessageMapperArray;
use labo86\hapi\Controller;
use labo86\hapi\Request;
use labo86\rdtas\app\DataAccessDb;
use labo86\rdtas\app\DataAccessDbConfig;
use labo86\rdtas\app\DataAccessFolder;
use labo86\rdtas\app\DataAccessFolderConfig;
use labo86\rdtas\app\ServicesBasic;
use labo86\rdtas\app\User;
use labo86\rdtas\ErrMsg;
use PHPUnit\Framework\TestCase;

class ServicesBasicTest extends TestCase
{
    private array $service_record = [];

    public function setUp(): void
    {
        $this->path = tempnam(__DIR__, 'demo');

        unlink($this->path);
        mkdir($this->path, 0777);
    }

    public function tearDown(): void
    {
        exec('rm -rf ' . $this->path);
    }

    public function getController() : Controller {
        file_put_contents($this->path . '/schema', User::DDL_TABLE_SESSIONS . User::DDL_TABLE_USERS);

        $services = new class extends ServicesBasic {

            public string $path;

            public function getDataAccessUser(): DataAccessDb
            {
               return new DataAccessDb(new DataAccessDbConfig([
                    'type' => 'sqlite',
                    'name' => $this->path . '/db.sql',
                    'schema' => $this->path . '/schema'
                ]));
            }

            public function getDataAccessError(): DataAccessFolder
            {
                return new DataAccessFolder(new DataAccessFolderConfig([
                    'dir' => $this->path . '/log'
                ]));
            }
        };
        $services->path = $this->path;
        $services->getDataAccessUser()->createTables();
        $services->getDataAccessError()->createDirectory();

        $controller =  new Controller();
        $controller->setMessageMapper(new MessageMapperArray([]));
        $controller->setErrorLogFilename($services->getDataAccessError()->getFilename('error_log'));
        $services->registerServicesUser($controller);
        $services->registerServicesUserAdmin($controller);
        $services->registerServicesServer($controller);

        return $controller;

    }

    public function areNoErrors() : bool {
        $error_log_file = $this->path . '/log/error_log';
        return !file_exists($error_log_file);
    }

    public function getError() : array {
        $error_log_file = $this->path . '/log/error_log';
        if ( file_exists($error_log_file) ) {
            return json_decode(file_get_contents($error_log_file), true);
        }
        throw new Exception("Error retrieving error log");
    }

    public function makeRequest(Controller $controller, array $parameters, array $file_parameters = [])  {
        $request = new Request();
        $request->setParameterList($parameters);
        $request->setFileParameterList($file_parameters);
        $response = $controller->handleRequest($request);
        $data = $response->getData() ?? [];

        $this->service_record[] = [
            'request' => [
                'params' => $parameters,
                'file' => $file_parameters
            ],
            'response' => $data
        ];
        return $data;
    }

    /**
     */
    public function testLoginLogoutWorkFlow() {
        $controller = $this->getController();

        $user = $this->makeRequest($controller, [
                'method' => 'create_session', 'username' => 'admin' , 'password' => 'pass'
            ]
        );
        $this->assertArrayHasKey('session_id', $user);

        $session_id = $user['session_id'];

        $user = $this->makeRequest($controller, [
                'method' => 'close_session', 'session_id' => $session_id
            ]
        );

        $this->assertTrue($this->areNoErrors());

    }

    /**
     * Crear un usuario y no permitir funciones de administrador
     */
    public function testCreateNormalUserWithSession() {
        $controller = $this->getController();

        {
            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'admin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $this->assertArrayHasKey('post_max_size', $result);

            $result = $this->makeRequest($controller, [
                    'method' => 'create_user', 'session_id' => $session_id, 'username' => 'edwin', 'password' => 'pass'
                ]
            );

            $this->assertTrue($this->areNoErrors());
        }

        {

            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'edwin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $error = $this->getError();
            $this->assertEquals(ErrMsg::USER_DOES_NOT_HAVE_PERMISSION, $error['p']['m']);

        }


    }


    /**
     * Crear un usuario y no permitir funciones de administrador
     */
    public function testCreateNormalUserWithSessionChangeType() {
        $controller = $this->getController();

        {
            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'admin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $this->assertArrayHasKey('post_max_size', $result);

            $result = $this->makeRequest($controller, [
                    'method' => 'create_user', 'session_id' => $session_id, 'username' => 'edwin', 'password' => 'pass'
                ]
            );


            $result = $this->makeRequest($controller, [
                    'method' => 'set_user_type', 'session_id' => $session_id, 'username' => 'edwin', 'type' => 'ADMIN'
                ]
            );

            $this->assertTrue($this->areNoErrors());
        }

        {

            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'edwin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $this->assertArrayHasKey('post_max_size', $result);

            $this->assertTrue($this->areNoErrors());
        }

    }

    /**
     * @runInSeparateProcess
     */
    public function testLoginSessionExpired() {
        $controller = $this->getController();

        {
            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'admin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);


            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $this->assertArrayHasKey('post_max_size', $result);

            $result = $this->makeRequest($controller, [
                    'method' => 'close_session', 'session_id' => $session_id
                ]
            );


            $result = $this->makeRequest($controller, [
                    'method' => 'get_php_server_info', 'session_id' => $session_id
                ]
            );

            $error = $this->getError();
            $this->assertEquals(ErrMsg::SESSION_INACTIVE, $error['p']['m']);
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testChangePassword() {
        $controller = $this->getController();

        {
            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'admin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                    'method' => 'create_user', 'session_id' => $session_id, 'username' => 'edwin', 'password' => 'pass'
                ]
            );

            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'edwin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $result = $this->makeRequest($controller, [
                    'method' => 'set_user_password', 'session_id' => $session_id, 'username' => 'edwin', 'password' => 'wachulin'
                ]
            );

            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'edwin', 'password' => 'wachulin'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $this->assertTrue($this->areNoErrors());

        }
    }

    /**
     */
    public function testErrors() {
        $controller = $this->getController();

        {
            $result = $this->makeRequest($controller, [
                    'method' => 'create_session', 'username' => 'admin', 'password' => 'pass'
                ]
            );
            $this->assertArrayHasKey('session_id', $result);

            $session_id = $result['session_id'];

            $result = $this->makeRequest($controller, [
                'method' => 'unexistant'
            ]);

            $this->assertArrayHasKey('i', $result);
            $error_id = $result['i'];

            $result = $this->makeRequest($controller, [
               'method' => 'get_error_by_error_id',
               'error_id' => $error_id, 'session_id' => $session_id
            ]);

            $this->assertEquals(\labo86\hapi\ErrMsg::SERVICE_NOT_REGISTERED, $result['p']['m']);

            $result = $this->makeRequest($controller, [
                'method' => 'get_error_list',
                'session_id' => $session_id
            ]);

            $this->assertCount(1,$result);
            $this->assertEquals(\labo86\hapi\ErrMsg::SERVICE_NOT_REGISTERED, $result[0]['p']['m']);

        }
    }


}

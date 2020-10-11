<?php
declare(strict_types=1);

namespace test\labo86\rdtas\app;

use Exception;
use labo86\exception_with_data\MessageMapperArray;
use labo86\hapi\Controller;
use labo86\hapi\Request;
use labo86\hapi\ResponseJson;
use labo86\rdtas\app\Config;
use labo86\rdtas\app\ConfigDefault;
use labo86\rdtas\app\ControllerInstaller;
use labo86\rdtas\app\DataAccessDb;
use labo86\rdtas\app\DataAccessDbConfig;
use labo86\rdtas\app\DataAccessError;
use labo86\rdtas\app\DataAccessFolder;
use labo86\rdtas\app\DataAccessFolderConfig;
use labo86\rdtas\app\ServicesBasic;
use labo86\rdtas\app\User;
use labo86\rdtas\ErrMsg;
use labo86\rdtas\testing\TestFolderTrait;
use PHPUnit\Framework\TestCase;

class ServicesBasicTest extends TestCase
{
    private array $service_record = [];

    use TestFolderTrait;

    public function setUp(): void
    {
        $this->setUpTestFolder(__DIR__);

    }

    public function tearDown(): void
    {
        $this->tearDownTestFolder();
    }

    public function getController() : Controller {
        file_put_contents($this->getTestFolder() . '/schema', User::DDL_TABLE_SESSIONS . User::DDL_TABLE_USERS);

        ConfigDefault::setDefaultData([
            'db' => [
                'type' => 'sqlite',
                'name' => $this->getTestFolder() . '/db.sql',
                'schema' => $this->getTestFolder() . '/schema'
            ],
            'error' => [
                'dir' => $this->getTestFolder() . '/error'
            ]
        ]);

        $installer = new class(new ConfigDefault()) extends ControllerInstaller {
            function prepareDataStores()
            {
                $this->prepareDataAccessDb(new DataAccessDb($this->getConfig()->getDatabase('db')));
                $this->prepareDataAccessFolder(new DataAccessError($this->getConfig()));
            }
        };

        $services = new class extends ServicesBasic {

            public Config $config;

            public function getDataAccessUser(): DataAccessDb
            {
                $config = $this->config->getDatabase('db');
               return new DataAccessDb($config);
            }

            public function getDataAccessError(): DataAccessError
            {
                return new DataAccessError($this->config);
            }
        };
        $services->config = $installer->getConfig();

        $controller =  new Controller();
        $controller->setMessageMapper(new MessageMapperArray([]));
        $controller->setErrorLogFilename($services->getDataAccessError()->getLogFilename());
        $services->registerServicesUser($controller);
        $services->registerServicesUserAdmin($controller);
        $services->registerServicesServer($controller);
        $installer->prepareDataStores();

        return $controller;

    }

    public function getDataAccessError() : DataAccessError {
        return new DataAccessError(new ConfigDefault());
    }

    public function assertNoErrorLogged() {
        $dao = new DataAccessError(new ConfigDefault());
        $this->assertEmpty($dao->getErrorList(), 'some error happened');
    }

    public function getError(string $error_id) : array {
        $dao = new DataAccessError(new ConfigDefault());
        return $dao->getError($error_id);
    }

    public function makeRequest(Controller $controller, array $parameters, array $file_parameters = [])  {
        $request = new Request();
        $request->setParameterList($parameters);
        $request->setFileParameterList($file_parameters);
        $response = $controller->handleRequest($request);
        $data = $response instanceof ResponseJson ? $response->getData() : [];


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

        $this->assertNoErrorLogged();

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
                'method' => 'get_user_by_session_id', 'session_id' => $session_id
            ]);
            $this->assertArrayhasKey('name', $result);
            $this->assertEquals('admin', $result['name']);



            $result = $this->makeRequest($controller, [
                    'method' => 'create_user', 'session_id' => $session_id, 'username' => 'edwin', 'password' => 'pass'
                ]
            );

            $this->assertNoErrorLogged();
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

            $error = $this->getError($result['i']);
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

            $this->assertNoErrorLogged();
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

            $this->assertNoErrorLogged();
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

            $error = $this->getError($result['i']);
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

            $this->assertNoErrorLogged();

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

<?php
declare(strict_types=1);

namespace labo86\rdtas\testing;


use labo86\hapi\Controller;
use labo86\hapi\Request;
use labo86\hapi\ResponseJson;
use labo86\rdtas\app\ConfigDefault;
use labo86\rdtas\app\DataAccessError;

trait TestControllerTrait
{
    private array $service_record = [];

    use TestFolderTrait;

    public function tearDown(): void
    {
        $this->tearDownTestFolder();
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
}
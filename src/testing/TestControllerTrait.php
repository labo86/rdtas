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

    public function makeRequest(Controller $controller, array $parameters, array $file_parameters = []) : array {
        $data = $this->makeRequestComplete($controller, $parameters, $file_parameters);
        return $data['response'];
    }

    public function makeRequestComplete(Controller $controller, array $parameters, array $file_parameters = []) : array {
        $request = new Request();
        $request->setHttpMethod('GET');
        $request->setParameterList($parameters);
        $request->setFileParameterList($file_parameters);
        $response = $controller->handleRequest($request);
        $data = $response instanceof ResponseJson ? $response->getData() : [];

        $complete_data = [
            'request' => [
                'params' => $parameters,
                'file' => $file_parameters
            ],
            'response' => $data,
            'more' => [
                'http_code' => $response->getHttpResponseCode(),
                'mime_type' => $response->getMimeType(),
                'headers' => $response->getHeaderList(),
                'error' => []
            ]
        ];

        if ( $response->getHttpResponseCode() >= 400 ) {
            $complete_data['more']['error'] = $this->getError($data['i']);
        }

        $this->service_record[] = $complete_data;
        return $complete_data;
    }
}
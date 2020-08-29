<?php
declare(strict_types=1);

namespace labo86\rdtas\hapi;

use labo86\exception_with_data\ExceptionWithData;
use labo86\hapi\Controller;
use labo86\hapi\ServiceFunctionReflector;
use labo86\hapi_core\Request;
use labo86\hapi_core\ResponseJson;

/**
 * Obtiene una lista automatizada de metodos
 * @param string $filename
 * @return array
 * @throws ExceptionWithData
 * @throws ReflectionException
 */
function getAutomaticMethodList(string $filename) : array {

    $function_info_list = [];
    $function_name_list = get_defined_functions()['user'];
    foreach ( $function_name_list as $function_name ) {
        $reflection_function = new ReflectionFunction($function_name);
        if ( $reflection_function->getFileName() === $filename ) {
            $function_info_list[] = [
                'method' => $function_name,
                'parameter_list' => ServiceFunctionReflector::getParameterInfoList($reflection_function)
            ];
        }
    }
    return $function_info_list;
}

/**
 * Registra un metodo llamado get_automatic_method_list en donde se puede obtener información de las servicios que son generados en base a un archivo.
 * @param Controller $controller
 * @param string $filename
 * @return Controller
 * @throws ReflectionException
 * @throws ExceptionWithData
 */
function registerAutomaticMethodService(Controller $controller, string $filename) : Controller
{
    $controller->registerFunctionsInFile($filename);
    $controller->getServiceMap()
        ->registerService('get_automatic_method_list', function (Request $request) use ($controller) {
            return new ResponseJson(getAutomaticMethodList(__FILE__));
        });
}
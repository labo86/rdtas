<?php
declare(strict_types=1);

namespace labo86\rdtas\hapi;

use Generator;
use labo86\exception_with_data\ExceptionWithData;
use labo86\hapi\Controller;
use labo86\hapi\Request;
use labo86\hapi\ResponseJson;
use ReflectionException;
use ReflectionFunction;

class Util
{

    /**
     * Obtiene una lista automatizada de metodos
     * @param string $filename
     * @param string $endpoint
     * @return array
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public static function getAutomaticMethodList(string $filename, string $endpoint = '/controller/ws.php'): array
    {
        $function_info_list = [];
        foreach ( self::iterateFunctionsInFile($filename) as $function_name => $reflection_function ) {
            $function_info_list[] = [
                'method' => $function_name,
                'parameter_list' => ServiceFunctionReflector::getParameterInfoList($reflection_function),
                'endpoint' => $endpoint
            ];
        }
        usort($function_info_list, function($a, $b) { return $a['method'] <=> $b['method']; });
        return $function_info_list;
    }

    /**
     * @param string $filename
     * @return Generator|ReflectionFunction[]
     * @throws ReflectionException
     */
    public static function iterateFunctionsInFile(string $filename) {
        $filename = realpath($filename);
        $function_name_list = get_defined_functions()['user'];
        foreach ($function_name_list as $function_name) {
            $reflection_function = new ReflectionFunction($function_name);
            if ($reflection_function->getFileName() === $filename) {
                yield $function_name => $reflection_function;
            }
        }
    }

    /**
     * @param Controller $controller
     * @param string $filename
     * @return Controller
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public static function registerFunctionsInFile(Controller $controller, string $filename) : Controller {
        $filename = realpath($filename);
        /** @noinspection PhpIncludeInspection */
        include($filename);

        foreach ( self::iterateFunctionsInFile($filename) as $function_name => $function ) {
            self::registerFunction($controller, $function_name);
        }
        return $controller;
    }

    /**
     * Registra un metodo llamado get_automatic_method_list en donde se puede obtener información de las servicios que son generados en base a un archivo.
     * Se puede cambiar el nombre del servico con la variable service.
     * También se puede setear el endpoint
     * @param Controller $controller
     * @param string $filename
     * @param string $service_name
     * @param string $endpoint
     * @return Controller
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public static function registerAutomaticMethodService(Controller $controller, string $filename, string $service_name = 'get_automatic_method_list', string $endpoint = '/controller/ws.php'): Controller
    {
        self::registerFunctionsInFile($controller, $filename);
        $controller->getServiceMap()
            ->registerService($service_name, function (Request $request) use ($filename, $endpoint) {
                return new ResponseJson(Util::getAutomaticMethodList($filename, $endpoint));
            });
        return $controller;
    }

    /**
     * @param Controller $controller
     * @param string $function_name
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public static function registerFunction(Controller $controller, string $function_name)
    {
        $reflection_function = new ReflectionFunction($function_name);
        $controller->getServiceMap()->registerService($function_name, ServiceFunctionReflector::createServiceCallback($reflection_function));
    }
}
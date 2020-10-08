<?php
declare(strict_types=1);

namespace labo86\rdtas\hapi;


use Closure;
use labo86\exception_with_data\ExceptionWithData;
use labo86\exception_with_data\ThrowableList;
use labo86\exception_with_data\Util;
use labo86\hapi\InputFile;
use labo86\hapi\InputFileList;
use labo86\hapi\Request;
use labo86\hapi\Response;
use labo86\hapi\ResponseJson;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;

class ServiceFunctionReflector
{

    /**
     * Obtiene la información de los parámetros de una función.
     * Son un arreglo de arreglos con la siguiente forma:
     * <code>
     * [
     *  ['name' => 'param_1' , 'type' => 'int'],
     *  ['name' => 'param_2' , 'type' => 'string']
     * ]
     * </code>
     * @param ReflectionFunctionAbstract $reflection_function
     * @return array
     * @throws ExceptionWithData
     */
    public static function getParameterInfoList(ReflectionFunctionAbstract $reflection_function) : array {

        $reflection_parameter_list = $reflection_function->getParameters();

        try {
            return Util::foreachTry(function (ReflectionParameter $reflection_parameter) {
                return self::getParameterInfo($reflection_parameter);
            }, $reflection_parameter_list);

        } catch ( ThrowableList $exception ) {
            throw Util::rethrow('SOME_SERVICES_PARAMETER_TYPES_ARE_NOT_SUPPORTED',
            [
               'function' => $reflection_function->getName(),
               'filename' => $reflection_function->getFileName(),
               'line' => $reflection_function->getStartLine()
            ], $exception);
        }
    }


    /**
     * Obtiene la información de los parámetros de una función.
     * Es un arreglo de la siguiente forma:
     * <code>
     *  ['name' => 'param_1' , 'type' => 'int'],
     * </code>
     * @param ReflectionParameter $parameter
     * @return array
     * @throws ExceptionWithData
     */
    public static function getParameterInfo(ReflectionParameter $parameter) : array {

        $name = $parameter->getName();

        $reflection_type = $parameter->getType();

        try {
            $type = is_null($reflection_type) ? 'string' : self::getParameterType($reflection_type);
        } catch ( ExceptionWithData $exception ) {
            throw Util::rethrow("", [
                'name' => $name
            ], $exception);
        }

        return [
            'name' => $name,
            'type' => $type
        ];
    }

    /**
     * Obtiene un tipo desde un tipo de un parámetro.
     * El resultado es un string que dice como se debe tratar dicha entrada o que se debe hacer con el o validar.
     * @param ReflectionType|null $type
     * @return string
     * @throws ExceptionWithData
     */
    public static function getParameterType(ReflectionType $type) : string {
        if ( $type instanceof ReflectionNamedType ) {
                 if ( $type->getName() === 'string') return 'string';
            else if ( $type->getName() === 'array') return 'array';
            else if ( $type->getName() === 'int' ) return 'int';
            else if ( $type->getName() === InputFile::class ) return InputFile::class;
            else if ( $type->getName() === InputFileList::class ) return InputFileList::class;
            else if ( $type->getName() === Request::class ) return Request::class;
            else throw new ExceptionWithData('SERVICE_PARAMETER_TYPE_IS_NOT_SUPPORTED', [
                'type' => $type->getName()
            ]);
        } else {
            return 'string';
        }
    }

    /**
     * Obtiene un valor los valores de un parametro desde el request.
     * En concreto busca un valor de parametro con un nombre y lo trata de convertir a un tipo especifico.
     * Lanza excepciones si no se encuentra el parametro o si no corresponde al tipo especificado
     * @param Request $request
     * @param string $name
     * @param string $type
     * @return array|int|InputFile|InputFileList|Request|string
     * @throws ExceptionWithData
     */
    public static function getParameterValueFromRequest(Request $request, string $name, string $type) {
        if ( $type === 'array') {
            return $request->getArrayParameter($name);
        } else if ( $type === 'int' ) {
            return $request->getIntParameter($name);
        } else if ( $type === 'string' ) {
            if ( $name === 'session_id')
                return $_COOKIE['session_id'] ?? $request->getStringParameter($name);
            else
                return $request->getStringParameter($name);
        } else if ( $type == InputFile::class ) {
            return $request->getFileParameter($name);
        } else if ( $type == InputFileList::class ) {
            return $request->getFileListParameter($name);
        } else if ( $type === Request::class ) {
           return $request;
        } else {
            throw new ExceptionWithData('UNSUPPORTED_TYPE_IN_REQUEST', [
                'name' => $name,
                'type' => $type,
            ]);
        }
    }

    /**
     * Devuelve un arreglo con todos los parámetros  que se quieren recuperar.
     *
     * @param Request $request
     * @param array $parameter_info_list Debe cumplir con el formato establecido en {@see getParameterInfoList()}, es decir, se puede usar la salida directa de ese método o un array compatible con la salida.
     * @return array
     * @throws ExceptionWithData
     */
    public static function getParameterValueListFromRequest(Request $request, array $parameter_info_list) : array {
        try {
            return Util::foreachTry(function (array $parameter_info) use ($request) {
                return self::getParameterValueFromRequest($request, $parameter_info['name'], $parameter_info['type']);
            }, $parameter_info_list);

        } catch ( ThrowableList $exception ) {
            throw Util::rethrow('ERROR_OBTAINING_PARAMETER_VALUE_LIST',
                [
                    'parameter_info_list' => $parameter_info_list,
                ], $exception);
        }


    }

    /**
     * @param ReflectionFunctionAbstract $reflection_function
     * @return Closure
     * @throws ExceptionWithData
     */
    public static function createServiceCallback(ReflectionFunctionAbstract $reflection_function) {
        $parameter_info_list = ServiceFunctionReflector::getParameterInfoList($reflection_function);

        return function(Request $request) use ($reflection_function, $parameter_info_list) {
            $parameter_value_list = ServiceFunctionReflector::getParameterValueListFromRequest($request, $parameter_info_list);
            $response = $reflection_function->invoke(...$parameter_value_list);

            if ( !$response instanceof Response )
                $response = new ResponseJson($response);

            return $response;
        };
    }
}
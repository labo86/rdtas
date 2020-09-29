<?php
declare(strict_types=1);

namespace test\labo86\rdtas\hapi;

use labo86\exception_with_data\ExceptionWithData;
use labo86\hapi\Request;
use labo86\hapi\ResponseJson;
use labo86\rdtas\hapi\ServiceFunctionReflector;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;

class ServiceFunctionReflectorTest extends TestCase
{

    /**
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public function testGetParameterTypeBasic()
    {
        $callback = function(string $param_0, float $param_1, Request $param_2, array $param_3, int $param_4) {
            return 1;
        };

        $reflection_function = new ReflectionFunction($callback);
        $parameters = $reflection_function->getParameters();
        $this->assertEquals('string', ServiceFunctionReflector::getParameterType($parameters[0]->getType()));
        $this->assertEquals(Request::class, ServiceFunctionReflector::getParameterType($parameters[2]->getType()));
        $this->assertEquals('array', ServiceFunctionReflector::getParameterType($parameters[3]->getType()));
        $this->assertEquals('int', ServiceFunctionReflector::getParameterType($parameters[4]->getType()));

        try {
            ServiceFunctionReflector::getParameterType($parameters[1]->getType());
            $this->fail("should throw");

        } catch (ExceptionWithData $exception) {
            $this->assertEquals("service parameter type is not supported", $exception->getMessage());
            $this->assertEquals([ 'type' => 'float'], $exception->getData());
        }
    }

    /**
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public function testGetParameterInfo() {
        $callback = function(string $param_0, $param_1, Request $param_2, array $param_3, bool $param_4, int $param_5) {
            return 1;
        };

        $reflection_function = new ReflectionFunction($callback);
        $parameters = $reflection_function->getParameters();
        $this->assertEquals(['name' => 'param_0', 'type' => 'string'], ServiceFunctionReflector::getParameterInfo($parameters[0]));
        $this->assertEquals(['name' => 'param_1', 'type' => 'string'], ServiceFunctionReflector::getParameterInfo($parameters[1]));
        $this->assertEquals(['name' => 'param_2', 'type' => Request::class], ServiceFunctionReflector::getParameterInfo($parameters[2]));
        $this->assertEquals(['name' => 'param_3', 'type' => 'array'], ServiceFunctionReflector::getParameterInfo($parameters[3]));
        $this->assertEquals(['name' => 'param_5', 'type' => 'int'], ServiceFunctionReflector::getParameterInfo($parameters[5]));

    }

    /**
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public function testGetParameterListInfo() {
        $callback = function(string $param_0, $param_1, Request $param_2, array $param_3, int $param_5) {
            return 1;
        };

        $reflection_function = new ReflectionFunction($callback);
        $result = ServiceFunctionReflector::getParameterInfoList($reflection_function);

        $this->assertEquals([
            [ 'name' => 'param_0', 'type' => 'string' ],
            [ 'name' => 'param_1', 'type' => 'string' ],
            [ 'name' => 'param_2', 'type' => Request::class ],
            [ 'name' => 'param_3', 'type' => 'array' ],
            [ 'name' => 'param_5', 'type' => 'int' ]

            ], $result);

    }

    /**
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public function testGetParameterListInfoFail() {
        //eso es para obtener la lina de la función
        $line = __LINE__ + 1;
        $callback = function(string $param_0, float $param_1) {
            return 1;
        };

        $reflection_function = new ReflectionFunction($callback);
        try {
            $result = ServiceFunctionReflector::getParameterInfoList($reflection_function);
            $this->fail('debería fallar porque float no es valido');
        } catch ( ExceptionWithData $exception ) {

            $data = $exception->toArray(false);

            $this->assertCount(2, $data['d']['element_list']);
            unset($data['d']['element_list']);

            $this->assertEquals([
                'm' => "some services parameter types are not supported",
                'd' => [
                    'filename' => __FILE__,
                    'function' => 'test\labo86\rdtas\hapi\{closure}',
                    'line' => $line
                    ],
                'p' => [ 'm' => 'service parameter type is not supported', 'd' => ['name' => 'param_1', 'type' => 'float'], 'p' => [ 'm' => 'service parameter type is not supported', 'd' => ['type' => 'float']]],
                'pl' => [[ 'm' => 'service parameter type is not supported', 'd' => ['name' => 'param_1', 'type' => 'float'], 'p' => [ 'm' => 'service parameter type is not supported', 'd' => ['type' => 'float']]]]
            ],$data);

        }
    }

    public function getParameterValueFromRequestProvider()
    {
        return [
            ["value_a", "value_a", "string"],
            ["10", "10", "string"],
            [10, "10", "int"],
            [11, 11, "int"],
            [10, "10", "int"]
        ];
    }

    /**
     * @dataProvider getParameterValueFromRequestProvider
     * @param $expected
     * @param $value
     * @param string $type
     * @throws ExceptionWithData
     */
    public function testGetParameterValueFromRequest($expected, $value, string $type) {
        $stub = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getParameterList'])
            ->getMock();

        $stub->expects($this->any())
            ->method('getParameterList')
            ->willReturn(['value' => $value]);

        $this->assertEquals($expected, ServiceFunctionReflector::getParameterValueFromRequest($stub, 'value', $type));

    }

    public function getParameterValueListFromRequestProvider()
    {
        return [
            ["value_a", "value_a", "string"],
            ["10", "10", "string"],
            [10, "10", "int"],
            [11, 11, "int"],
            [10, "10", "int"]
        ];
    }

    /**
     * @dataProvider getParameterValueListFromRequestProvider
     * @param $expected
     * @param $value
     * @param string $type
     * @throws ExceptionWithData
     */
    public function testGetParameterValueListFromRequest($expected, $value, string $type) {
        $stub = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getParameterList'])
            ->getMock();

        $stub->expects($this->any())
            ->method('getParameterList')
            ->willReturn(['value' => $value]);

        $this->assertEquals([$expected], ServiceFunctionReflector::getParameterValueListFromRequest($stub, [['name' => 'value', 'type' => $type]]));
    }

    /**
     * @runInSeparateProcess
     * @throws ReflectionException
     * Esto prueba las function callback de servicio
     */
    public function testCreateServiceCallback() {
        $callback = function(int $a, int $b) {
            return $a + $b;
        };

        $reflection_function = new ReflectionFunction($callback);
        $service_callback = ServiceFunctionReflector::createServiceCallback($reflection_function);

        $stub = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getParameterList'])
            ->getMock();

        $stub->expects($this->any())
            ->method('getParameterList')
            ->willReturn(['a' => '2', 'b' => '5']);

        $response = $service_callback($stub);
        $this->assertInstanceOf(ResponseJson::class, $response);
        ob_start();
        $response->send();
        $json_data = ob_get_clean();
        $recovered_data = json_decode($json_data, true);
        $this->assertEquals(7, $recovered_data);

    }

}

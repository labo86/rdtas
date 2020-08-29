<?php
declare(strict_types=1);

namespace test\labo86\rdtas\hapi;

use labo86\exception_with_data\ExceptionWithData;
use labo86\hapi\Controller;
use labo86\hapi_core\Request;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use function labo86\rdtas\hapi\getAutomaticMethodList;
use function labo86\rdtas\hapi\registerAutomaticMethodService;

class FunctionsTest extends TestCase
{
    private $path;

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

    /**
     * @throws ExceptionWithData
     * @throws ReflectionException
     */
    public function testGetAutomaticMethodList()  {

        file_put_contents($this->path . '/file.php', <<<'EOF'
<?php
function f1_get_test1(string $a, string $b) { return $a . $b; }
function f1_get_test2(int $a, $b) { return $a + $b; }
EOF);

        include($this->path. '/file.php');
        $method_list = getAutomaticMethodList($this->path . '/file.php');
        $this->assertEquals([[
            'method' => 'f1_get_test1',
            'parameter_list' => [
                ['name' => 'a', 'type' => 'string'], ['name' => 'b', 'type' => 'string']
            ]
        ], [
            'method' => 'f1_get_test2',
            'parameter_list' => [
                ['name' => 'a', 'type' => 'int'], ['name' => 'b', 'type' => 'string']
            ]
        ]], $method_list);
    }

    /**
     * Registra un metodo llamado get_automatic_method_list en donde se puede obtener información de las servicios que son generados en base a un archivo.
     * @param Controller $controller
     * @param string $filename
     * @return Controller
     * @throws ReflectionException
     * @throws ExceptionWithData
     */
    function testRegisterAutomaticMethodService()
    {
        file_put_contents($this->path . '/file.php', <<<'EOF'
<?php
function f2_get_test1(string $a, string $b) { return $a . $b; }
function f2_get_test2(int $a, $b) { return $a + $b; }
EOF);

        $controller = new Controller();
        registerAutomaticMethodService($controller, $this->path . '/file.php');
        $callback = $controller->getServiceMap()->getService('get_automatic_method_list');
        $this->assertIsCallable($callback);

        $response = $callback(new Request());


        $this->assertEquals([[
            'method' => 'f2_get_test1',
            'parameter_list' => [
                ['name' => 'a', 'type' => 'string'], ['name' => 'b', 'type' => 'string']
            ]
        ], [
            'method' => 'f2_get_test2',
            'parameter_list' => [
                ['name' => 'a', 'type' => 'int'], ['name' => 'b', 'type' => 'string']
            ]
        ]], $response->getData());
    }

}

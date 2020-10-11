<?php
declare(strict_types=1);

namespace test\labo86\rdtas\staty;

use labo86\rdtas\staty\Component;
use labo86\rdtas\staty\Module;
use labo86\rdtas\testing\TestFolderTrait;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    use TestFolderTrait;

    public function setUp(): void
    {
        $this->setUpTestFolder(__DIR__);
        $this->path = $this->getTestFolder();

    }

    public function tearDown(): void
    {
        $this->tearDownTestFolder();
    }


    public function setupDir(string $contents) {
        mkdir($this->path . '/module');
        file_put_contents($this->path .'/module/config.json',<<<EOF
[
  {
    "id" : "some",
    "label" : "SOME"
  }
]
EOF);
        file_put_contents($this->path. '/module/some.html', $contents);

    }

    public function getDummyModule(string $contents) {
        $this->setupDir($contents);
        return new Module($this->path, 'module');
    }

    public function testBasic() {

        $component = $this->getDummyModule('CONTENTS')->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        ob_start();
        $component->import('html');
        $this->assertEquals('CONTENTS', ob_get_clean());
    }

    public function testFailComponent() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_EXIST');
        $component = new Component($this->getDummyModule('CONTENTS'), [
            'id' => 'test',
            'label' => 'label'
        ]);


        $component->import('html');
    }

    public function testFailId() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_HAVE_ID');
        $component = new Component($this->getDummyModule('CONTENTS'), [
            'label' => 'label'
        ]);


        $component->getId();
    }


    public function testFailLabel() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_HAVE_LABEL');
        $component = new Component($this->getDummyModule('CONTENTS'), [
            'id' => 'label'
        ]);


        $component->getLabel();
    }

    public function testGetData() {

        $component = new Component($this->getDummyModule('CONTENTS'), [
            'label' => 'label',
            'custom_key' => 'custom_value'
        ]);


        $this->assertEquals('custom_value', $component['custom_key']);
    }


    function testInjectedVars() {
        $component = $this->getDummyModule('<?php echo $inject ?? "NOT_EXISTS";')->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        ob_start();
        $component->import('html');
        $this->assertEquals('NOT_EXISTS', ob_get_clean());
    }

    function testInjectedVars2() {
        $component = $this->getDummyModule('<?php echo $inject ?? "NOT_EXISTS";')->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        $inject = 'hola';
        ob_start();
        $component->import('html', ['inject' => $inject]);
        $this->assertEquals('hola', ob_get_clean());
    }

    function testInjectedVars3() {
        $component = $this->getDummyModule('<?php echo $inject ?? "NOT_EXISTS"; $inject = "MODIFIED";')->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        $inject = 'hola';
        ob_start();
        $component->import('html', ['inject' => $inject]);
        $this->assertEquals('hola', ob_get_clean());
        $this->assertEquals("hola", $inject);
    }

    function testInjectedVars4() {
        $component = $this->getDummyModule('<?php echo $inject ?? "NOT_EXISTS"; $inject = "MODIFIED";')->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        $inject = 'hola';
        ob_start();
        $component->import('html', ['inject' => &$inject]);
        $this->assertEquals('hola', ob_get_clean());
        $this->assertEquals("hola", $inject);
    }

}

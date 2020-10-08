<?php
declare(strict_types=1);

namespace test\labo86\rdtas\staty;

use labo86\rdtas\staty\Component;
use labo86\rdtas\staty\Module;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    public function getDummyModule() {
        $dir = __DIR__ . '/../resources';
        return new Module($dir, 'module');
    }

    public function testBasic() {

        $component = $this->getDummyModule()->getComponentList()[0];

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());

        ob_start();
        $component->import('html');
        $this->assertEquals('CONTENTS', ob_get_clean());
    }

    public function testFailComponent() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_EXIST');
        $component = new Component($this->getDummyModule(), [
            'id' => 'test',
            'label' => 'label'
        ]);


        $component->import('html');
    }

    public function testFailId() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_HAVE_ID');
        $component = new Component($this->getDummyModule(), [
            'label' => 'label'
        ]);


        $component->getId();
    }


    public function testFailLabel() {

        $this->expectExceptionMessage('COMPONENT_DOES_NOT_HAVE_LABEL');
        $component = new Component($this->getDummyModule(), [
            'id' => 'label'
        ]);


        $component->getLabel();
    }

    public function testGetData() {

        $component = new Component($this->getDummyModule(), [
            'label' => 'label',
            'custom_key' => 'custom_value'
        ]);


        $this->assertEquals('custom_value', $component['custom_key']);
    }
}

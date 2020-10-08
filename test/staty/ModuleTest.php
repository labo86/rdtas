<?php
declare(strict_types=1);

namespace test\labo86\rdtas\staty;

use labo86\rdtas\staty\Component;
use labo86\rdtas\staty\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testComponentBasic() {
        $dir = __DIR__ . '/../resources';
        $module = new Module($dir, 'module');
        $component_list = $module->getComponentList();
        $this->assertCount(1, $component_list);

        $component = $component_list[0];

        $this->assertEquals('module', $module->getName());
        $this->assertEquals($dir . '/module', $module->getDir());

        $this->assertEquals('some', $component->getId());
        $this->assertEquals('SOME', $component->getLabel());
    }

    public function testComponentDoesNotExist() {

        $this->expectExceptionMessage('MODULE_CONFIG_DOES_NOT_EXIST');
        $dir = __DIR__ . '/../resources';
        $module = new Module($dir, 'not exists');

    }
}

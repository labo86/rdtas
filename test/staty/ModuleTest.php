<?php
declare(strict_types=1);

namespace test\labo86\rdtas\staty;

use labo86\rdtas\staty\Component;
use labo86\rdtas\staty\Module;
use labo86\rdtas\testing\TestFolderTrait;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
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

    public function setupDir() {
        mkdir($this->path . '/module');
        file_put_contents($this->path .'/module/config.json',<<<EOF
[
  {
    "id" : "some",
    "label" : "SOME"
  }
]
EOF);
        file_put_contents($this->path. '/module/some.html', 'CONTENTS');

    }


    public function testComponentBasic() {
        $this->setupDir();
        $dir = $this->path;
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
        $dir = $this->path;
        $module = new Module($dir, 'not exists');

    }
}

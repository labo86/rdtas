<?php
declare(strict_types=1);

namespace test\labo86\rdtas\staty;

use labo86\rdtas\staty\BlockAutoServices;
use labo86\staty\Block;
use PHPUnit\Framework\TestCase;

class BlockAutoServicesTest extends TestCase
{

    public function testHtml() {
        $block = new BlockAutoServices(Block::thisPage());
        $block->setService('service');
        ob_start();
        $block->html();
        $string = ob_get_clean();
        $this->assertStringContainsString("const endpoint = 'service'", $string);
    }
}

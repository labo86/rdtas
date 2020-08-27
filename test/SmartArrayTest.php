<?php
declare(strict_types=1);

namespace test\labo86\rdtas;

use labo86\rdtas\SmartArray;
use PHPUnit\Framework\TestCase;

class SmartArrayTest extends TestCase
{
    public function testBasic() {
        $data = [
            'a1' => [
                'b1' => 1,
                'b2' => 2
            ],
            'a2' => [
                'b1'=> 3,
                'b2' => [
                    'c1' => 2
                ]
            ]
        ];

        $data = new SmartArray($data);

        $this->assertEquals($data('a1', 'b1'), $data('b1', 'a1'));
        $this->assertEquals(1, $data('b1', 'a1'));

        $this->assertEquals($data('a2', 'b1'), $data('b1', 'a2'));
        $this->assertEquals(3, $data( 'a2', 'b1'));

        $this->assertEquals(2, $data('a2', 'b2', 'c1'));
        $this->assertEquals(2, $data('c1', 'b2', 'a2'));
        $this->assertEquals(2, $data('b2', 'c1', 'a2'));
    }
}

<?php
declare(strict_types=1);

namespace test\labo86\rdtas;

use labo86\rdtas\ArrayWrapper;
use PHPUnit\Framework\TestCase;

class ArrayWrapperTest extends TestCase
{



    public function testOffsetSetBasic()
    {
        $array = new ArrayWrapper(['hola' => 'value']);
        $this->assertEquals('value', $array['hola']);
        $this->assertTrue(isset($array['hola']));
        unset($array['hola']);
        $this->assertFalse(isset($array['hola']));
        $array['hola'] = 'changed';
        $this->assertTrue(isset($array['hola']));
        $this->assertEquals('changed', $array['hola']);
    }

    public function testToArrayBasic()
    {
        $array = new ArrayWrapper(['hola' => 'value']);
        $this->assertEquals(['hola' => 'value'], $array->toArray());
    }

    public function testToString()
    {
        $array = new ArrayWrapper(['hola' => 'value']);
        $this->assertEquals(<<<EOF
{
    "hola": "value"
}
EOF, $array->toString());
    }

}

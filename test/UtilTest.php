<?php
declare(strict_types=1);

namespace test\labo86\rdtas;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\Util;
use PHPUnit\Framework\TestCase;
use function labo86\rdtas\arrayToFile;
use function labo86\rdtas\arrayToString;
use function labo86\rdtas\createDirectory;
use function labo86\rdtas\fileToArray;
use function labo86\rdtas\iterateFilesRecursively;
use function labo86\rdtas\readFileByLine;
use function labo86\rdtas\removeFileOrDir;
use function labo86\rdtas\resetDirectory;
use function labo86\rdtas\stringToArray;

class UtilTest extends TestCase
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


    function testArrayToString() {

        $expected_json = <<<EOF
{
    "a": "value"
}
EOF;
        $this->assertEquals($expected_json, Util::arrayToString(["a" => "value"]));

    }


    function testStringToArray() {
        $original_json = <<<EOF
{
    "a": "value"
}
EOF;
        $this->assertEquals(["a" => "value"], Util::stringToArray($original_json));
    }

    function testStringToArrayFail() {
        $this->expectException(ExceptionWithData::class);
        Util::stringToArray("something bad");
    }


    function testArrayToFileToArray()
    {
        $filename = $this->path . '/json';
        $array = ["hola" => "value"];
        Util::arrayToFile($filename, $array);
        $this->assertEquals($array, Util::fileToArray($filename));
    }


    function testIterateFilesRecursively()
    {

        touch ( $this->path . '/a1');
        touch ( $this->path . '/a2');
        touch ( $this->path . '/a3');

        $file_names = [];
        foreach ( Util::iterateFilesRecursively($this->path) as $files )
            $file_names[] = $files->getBasename();

        sort($file_names);

        $this->assertEquals(['.', '..', 'a1', 'a2', 'a3'], $file_names);
    }

    function testCreateDirectory()
    {
        $directory_path = $this->path . '/dir1';
        $this->assertDirectoryNotExists($directory_path);
        Util::createDirectory($directory_path);
        $this->assertDirectoryExists($directory_path);

    }

    function testResetDirectoryNotExistent()
    {
        $this->assertFileNotExists($this->path . '/a1');
        Util::resetDirectory($this->path . '/a1');

        $this->assertFileExists($this->path . '/a1');
    }

    function testResetDirectoryExistent()
    {
        touch($this->path . '/a1');
        $this->assertFileExists($this->path . '/a1');
        Util::resetDirectory($this->path . '/a1');

        $this->assertFileExists($this->path . '/a1');
    }

    function testRemoveFileOrDir()
    {
        touch($this->path . '/a1');
        $this->assertFileExists($this->path . '/a1');
        Util::removeFileOrDir($this->path . '/a1');

        $this->assertFileNotExists($this->path . '/a1');
    }

    function testReadFileByLine()
    {
        $filename = $this->path . '/a1';
        file_put_contents($filename, <<<EOF
hola
como
te
va
EOF
);
        $this->assertFileExists($filename);
        $lines = iterator_to_array(Util::readFileByLine($filename), false);
        $this->assertEquals(['hola', 'como', 'te', 'va'], $lines);
    }

    function testReadFileFailure()
    {
        $this->expectException(ExceptionWithData::class);
        $filename = $this->path . '/a1';

        $this->assertFileNotExists($filename);
        $lines = iterator_to_array(Util::readFileByLine($filename), false);
    }
}

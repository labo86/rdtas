<?php
declare(strict_types=1);

namespace labo86\rdtas\testing;


trait TestFolderTrait
{

    public string $test_folder_path;

    public function getTestFolder() : string {
        return $this->test_folder_path;
    }

    /**
     * Se recomienda usar en base_dir __DIR__
     * @param string $base_dir
     * @param string $prefix
     */
    public function setUpTestFolder(string $base_dir, string $prefix = 'demo'): void
    {
        $this->test_folder_path = tempnam($base_dir, $prefix);

        unlink($this->test_folder_path);
        mkdir($this->test_folder_path, 0777);
    }

    public function tearDownTestFolder(): void
    {
        exec('rm -rf ' . $this->test_folder_path);
    }

}
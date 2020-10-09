<?php
declare(strict_types=1);


namespace labo86\rdtas\app;


use labo86\rdtas\Util;

class DataAccessFolder
{

    protected DataAccessFolderConfig $config;

    protected string $directory;

    public function __construct(DataAccessFolderConfig $config) {
        $this->config = $config;
    }

    public function getConfig() : DataAccessFolderConfig {
        return $this->config;
    }

    public function getDirectory() : string {
        if ( !isset($this->directory) )
            $this->directory = $this->getConfig()->getFolder();

        return $this->directory;
    }

    public function getFilename(string $filename) : string {
        return $this->getDirectory() . '/' . $filename;
    }

    public function createDirectory() {
        return Util::createDirectory($this->getDirectory());
    }
}
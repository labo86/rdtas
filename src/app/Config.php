<?php
declare(strict_types=1);


namespace labo86\rdtas\app;


class Config
{
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getFolder(string $name) : DataAccessFolderConfig {
        return new DataAccessFolderConfig($this->data[$name]);
    }

    public function getDatabase(string $name) : DataAccessDbConfig {
        return new DataAccessDbConfig($this->data[$name]);
    }

}
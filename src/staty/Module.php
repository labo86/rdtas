<?php
declare(strict_types=1);

namespace labo86\rdtas\staty;


use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ArrayWrapper;
use labo86\rdtas\ErrMsg;
use labo86\rdtas\Util;

class Module
{
    protected string $dir;

    protected array $config;

    /**
     * Module constructor.
     * User la funcion {@see getComponentList()} para obtener los components
     * @param string $dir
     * @param string $name
     * @throws ExceptionWithData
     */
    public function __construct(string $dir, string $name) {
        $this->dir = $dir;
        $this->name = $name;
        $this->loadConfig($name);
    }

    public function getDir() : string {
        return $this->dir . '/' . $this->name;
    }

    public function getName() : string {
        return $this->name;
    }


    public function loadConfig(string $name) : array {
        $config_file = $this->getDir() . '/config.json';
        if ( !file_exists($config_file) ) {
            throw new ExceptionWithData(ErrMsg::MODULE_CONFIG_DOES_NOT_EXIST, [
                'config_file' => $config_file,
                'name' => $name
            ]);
        }

        $this->config = Util::fileToArray($config_file);
        return $this->config;
    }

    /**
     * @return Component[]
     */
    public function getComponentList() : array {
        $module = $this;
        return array_map(
            function(array $item) { return new Component($this, $item); },
            $this->config
        );

    }

}
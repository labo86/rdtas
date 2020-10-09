<?php
declare(strict_types=1);

namespace labo86\rdtas\staty;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ErrMsg;
use labo86\staty_core\PagePhp;

/**
 * Trait BlockPageModule
 * @package labo86\rdtas\staty
 * @property PagePhp $page
 */
trait BlockPageModuleTrait
{
    /**
     * Obtiene el directorio en que los modulos se encuentran. Por convención estarán en una carpeta modulo.
     * @return string
     */
    public function getModulesDir() : string {
        return $this->page->getBaseDir() . '/../modules';
    }

    /**
     * @param string $name
     * @return Component[]
     * @throws ExceptionWithData
     */
    public function loadModuleComponents(string $name) : array {
        $module = new Module($this->getModulesDir(), $name);
        return $module->getComponentList();
    }

    public function import($file) {

        $module_file = $this->getModulesDir() . '/' . $file;
        if ( !file_exists($module_file) )
            throw new ExceptionWithData(ErrMsg::MODULE_DOES_NOT_EXIST, [
                'module_file' => $module_file,
                'file' => $file
            ]);
        include($module_file);

    }

    public function importComponent(...$components) {
        foreach ( $components as $component ) {
            try {
                $this->import('components/local/' . $component . '.js');
            } catch ( ExceptionWithData $exception) {
                $this->import('components/remote/' . $component . '.js');
            }
        }
    }
}
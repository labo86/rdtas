<?php
declare(strict_types=1);

namespace labo86\rdtas\staty;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ArrayWrapper;

class Component extends ArrayWrapper {

    private Module $module;

    public function __construct(Module $module, array $config_item) {
        $this->module = $module;
        $this->data = $config_item;
    }

    public function getId() : string {
        if ( !isset($this->data['id'])) {
            throw new ExceptionWithData('COMPONENT_DOES_NOT_HAVE_ID', [
                'data' => $this->data
            ]);
        }
        return $this->data['id'];
    }

    public function getLabel() : string {
        if ( !isset($this->data['label'])) {
            throw new ExceptionWithData('COMPONENT_DOES_NOT_HAVE_LABEL', [
                'data' => $this->data
            ]);
        }
        return $this->data['label'];
    }

    public function import(string $type) {

        $component_file = $this->module->getDir() . '/' . $this->getId() . '.' . $type;
        if ( !file_exists($component_file) )
            throw new ExceptionWithData('COMPONENT_DOES_NOT_EXIST', [
                'module_name' => $this->module->getName(),
                'module_dir' => $this->module->getDir(),
                'component_file' => $component_file,
                'type' => $type
            ]);
        include($component_file);

    }

}

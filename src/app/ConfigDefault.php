<?php
declare(strict_types=1);

namespace labo86\rdtas\app;

use labo86\rdtas\Util;

/**
 * Class ConfigDefaultTrait
 *
 * Debe implementar static string $default_data_file
 * @package labo86\rdtas\app
 */
class ConfigDefault extends Config
{
    protected static array $default_data;

    public function  __construct() {
        parent::__construct(self::getDefaultData());
    }

    public static function getDefaultData() : array {
        return self::$default_data;

    }

    public static function setDefaultData(array $data) {
        self::$default_data = $data;
    }

    public static function loadDataFromFile(string $default_data_file) {
        self::setDefaultData(Util::fileToArray($default_data_file));
    }
}
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

    /**
     * Estos son los tipos de datos de ejemplo:
     * <code>
     * 'folder' => [
     *  'dir' => '/path/to/dir'
     * ],
     * 'sqlite' => [
     *  'type' => 'sqlite',
     *  'name' => ':memory:',
     *  'schema' => '/path/to/schema.sql'
     * ],
     * 'mysql' => [
     *   'type' => 'mysql',
     *   'name' => 'db_name',
     *   'user' => 'db_user',
     *   'password' => 'db_pass',
     *   'schema' => '/path/to/schema.sql'
     * ]
     * </code>
     * @param array $data
     */
    public static function setDefaultData(array $data) {
        self::$default_data = $data;
    }

    /**
     * Solo funciona si no se ha seteado la data previamente.
     * En caso contrario mantiene la data ya establecida
     * @param string $default_data_file
     * @throws \labo86\exception_with_data\ExceptionWithData
     */
    public static function loadDataFromFile(string $default_data_file) {
        if ( !isset(self::$default_data))
            self::setDefaultData(Util::fileToArray($default_data_file));
    }
}
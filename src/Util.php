<?php
declare(strict_types=1);

namespace labo86\rdtas;

use FilesystemIterator;
use Generator;
use labo86\exception_with_data\ExceptionWithData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class Util
{

    public static function arrayToString(array $data): string
    {
        $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json_data === FALSE) {
            throw new ExceptionWithData(ErrMsg::DATA_IS_NOT_JSON_COMPATIBLE, ['data' => $data]);
        }
        return $json_data;
    }

    public static function stringToArray(string $json_data): array
    {
        $data = json_decode($json_data, true);
        if ($data === NULL) {
            throw new ExceptionWithData(ErrMsg::STRING_IS_NOT_A_VALID_JSON, ['data' => $json_data]);
        }
        return $data;
    }

    /**
     * Guardada los datos en un array en un archivo. La serializacion se hace en formato json
     * Usar {@see fileToArray()} para recuperar la información
     * @param string $filename
     * @param array $data
     * @throws ExceptionWithData
     */
    public static function arrayToFile(string $filename, array $data)
    {
        try {
            $json_data = self::arrayToString($data);
        } catch (Throwable $exception) {
            throw \labo86\exception_with_data\Util::rethrow(ErrMsg::FORMAT_ERROR_IN_FILE_CONTENTS, ['filename' => $filename], $exception);
        }

        if (file_put_contents($filename, $json_data) === FALSE) {
            throw new ExceptionWithData(ErrMsg::ERROR_WRITING_ARRAY_IN_FILE, ['filename' => $filename]);
        }
    }

    /**
     * Convierte los datos guardados en un archivo en un array.
     * Supone que los datos son guardados en formato json
     * @param string $filename
     * @return array
     * @throws ExceptionWithData
     */
    public static function fileToArray(string $filename): array
    {
        $json_data = file_get_contents($filename);
        if ($json_data === FALSE) {
            throw new ExceptionWithData(ErrMsg::ERROR_OPENING_ARRAY_FROM_FILE, ['filename' => $filename]);
        }

        try {
            return self::stringToArray($json_data);
        } catch (Throwable $exception) {
            throw \labo86\exception_with_data\Util::rethrow(ErrMsg::FORMAT_ERROR_IN_FILE_CONTENTS, ['filename' => $filename], $exception);
        }
    }

    /**
     * Itera sobre los archivos de un directorio.
     * Cada elemento ver los métodos de {@see DirectoryIterator}
     * @param string $directory_path
     * @return Generator|RecursiveDirectoryIterator[]
     */
    public static function iterateFilesRecursively(string $directory_path): Generator
    {
        $iterator = new RecursiveDirectoryIterator(
            $directory_path,
            FilesystemIterator::CURRENT_AS_SELF
        );

        /** @var $file RecursiveDirectoryIterator */
        foreach (new RecursiveIteratorIterator($iterator) as $file) {
            yield $file;
        }
    }

    /**
     * Intenta crear un directorio desde un path.
     * Si no existe lo creo recursivamente.
     * Si existe y no es un directorio entonces lanza excepcion.
     * Si existe y es un directorio no hace nada
     * @param string $directory_path
     * @return string
     * @throws ExceptionWithData
     */
    public static function createDirectory(string $directory_path): string
    {
        if (!file_exists($directory_path))
            mkdir($directory_path, 0777, true);

        if (!is_dir($directory_path))
            throw new ExceptionWithData(ErrMsg::TARGET_DIRECTORY_IS_NOT_A_DIRECTORY,
                [
                    'directory_path' => $directory_path,
                ]);

        return $directory_path;
    }

    /**
     * Es igual a {@see createDirectory()} pero antes borra el directorio si es que existe
     * @param string $directory_path
     * @return string
     * @throws ExceptionWithData
     */
    public static function resetDirectory(string $directory_path): string
    {
        self::removeFileOrDir($directory_path);

        return self::createDirectory($directory_path);
    }

    /**
     * Borrar un archivo o directorio si existe.
     * @param string $path
     * @return bool
     * @throws ExceptionWithData
     */
    public static function removeFileOrDir(string $path): bool
    {
        if (!file_exists($path))
            return false;

        $command = sprintf('rm -rf %s', $path);

        exec($command, $output, $return);
        if ($return !== 0)
            throw new ExceptionWithData(ErrMsg::ERROR_REMOVING_FILE_OR_DIR, ['path' => $path, 'command' => $command, 'output' => $output, 'return' => $return]);
        return true;
    }

    public static function readFileByLine(string $filename): Generator
    {
        $f = @fopen($filename, 'r');
        if ($f === FALSE) {
            throw new ExceptionWithData(ErrMsg::ERROR_AT_OPENING_FILE, ['filename' => $filename]);
        }

        while ($l = fgets($f)) {
            yield trim($l);
        }

        fclose($f);
    }

    public static function downloadJsComponentFiles(string $target_dir, string ...$component_list) {
        self::resetDirectory($target_dir);

        \labo86\exception_with_data\Util::foreachTry(function($component_name) use ($target_dir) {
           $contents = Util::downloadJsComponent($component_name);
           file_put_contents($target_dir . '/' . $component_name . '.js', $contents);
        }, $component_list);
    }

    public static function downloadJsComponent(string $component) : string {
        $source_file = 'https://raw.githubusercontent.com/labo86/rdtasjs/master/src/component/' . $component . '.js';
        $contents = file_get_contents($source_file);
        if ( $contents === FALSE ) {
            throw new ExceptionWithData(ErrMsg::ERROR_DOWNLOADING_JS_COMPONENT, ['component' => $component, 'source' => $source_file]);
        }
        return $contents;
    }

}


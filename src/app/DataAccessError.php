<?php
declare(strict_types=1);

namespace labo86\rdtas\app;


use labo86\exception_with_data\ExceptionWithData;
use labo86\hapi\ResponseJson;
use labo86\rdtas\ErrMsg;
use labo86\rdtas\Util;

class DataAccessError extends DataAccessFolder
{
    protected DataAccessFolderConfig $config;

    protected string $directory;

    public function __construct(Config $config) {
        $this->config = $config->getFolder('error');
    }

    public function getLogFilename() : string {
        return $this->getDirectory() . '/error.log';
    }

    public function createDirectory() {
        $directory = Util::createDirectory($this->getDirectory());
        $filename = $this->getLogFilename();
        if ( !file_exists($filename) )
            touch($filename);
    }

    /**
     * @param string $error_id
     * @return array
     * @throws ExceptionWithData
     */
    public function getError(string $error_id) : array {
        $filename = $this->getLogFilename();
        if ( file_exists($filename) ) {
            foreach (Util::readFileByLine($filename) as $line ) {

                $error = json_decode($line, true);
                if ($error['i'] === $error_id)
                    return $error;
            }
        }
        throw new ExceptionWithData(ErrMsg::ERROR_DOES_NOT_EXIST, [
            'error_id' => $error_id
        ]);
    }

    /**
     * @return array
     * @throws ExceptionWithData
     */
    public function getErrorList() : array {
        $filename = $this->getLogFilename();
        if ( !file_exists($filename) )
            return [];
        $error_list = [];
        foreach (Util::readFileByLine($filename) as $line ) {
            $error_list[] = json_decode($line, true);
        }
        return $error_list;
    }
}
<?php
declare(strict_types=1);

namespace labo86\rdtas\app;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ErrMsg;

class DataAccessFolderConfig
{
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getFolder() : string {
        $dir = $this->data['dir'] ?? null;
        if ( is_null($dir) )
            throw new ExceptionWithData(ErrMsg::NULL_FOLDER_DIR, []);

        return $dir;
    }

}
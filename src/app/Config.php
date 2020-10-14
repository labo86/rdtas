<?php
declare(strict_types=1);


namespace labo86\rdtas\app;


class Config
{
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * El usuario del servidor httpd, en general es www-data pero puede cambiar.
     * Se puede usar para cambiar el owner de ciertos archivos.
     * En la comfiguradior se debe llamar user_www
     * @param string $name
     * @return string|null
     */
    public function getUserWww() : ?string {
        return $this->data['user_www'] ?? null;
    }

    public function getFolder(string $name) : DataAccessFolderConfig {
        return new DataAccessFolderConfig($this->data[$name]);
    }

    public function getDatabase(string $name) : DataAccessDbConfig {
        return new DataAccessDbConfig($this->data[$name]);
    }

    /**
     * Esta es para poder crear nuevos tipos de datos de configuración
     * @param string $name
     * @return array
     */
    public function getValue(string $name) : array {
        return $this->data[$name];
    }

}
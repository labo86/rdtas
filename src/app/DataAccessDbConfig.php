<?php
declare(strict_types=1);

namespace labo86\rdtas\app;

use labo86\exception_with_data\ExceptionWithData;
use labo86\rdtas\ErrMsg;

class DataAccessDbConfig
{
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getType() : string {
        $type = $this->data['type'] ?? null;
        if ( $type !== 'mysql' && $type !== 'sqlite' ) {
            throw new ExceptionWithData(ErrMsg::INVALID_DATABASE_TYPE, [
                'type' => $type
            ]);
        }
        return $type;
    }

    public function getName() : string {
        $name = $this->data['name'] ?? null;
        if ( is_null($name) ) {
            throw new ExceptionWithData(ErrMsg::NULL_DATABASE_NAME, []);
        }
        return $name;
    }

    public function getUser() : string {
        $user = $this->data['user'] ?? null;
        if ( is_null($user) ) {
            throw new ExceptionWithData(ErrMsg::NULL_DATABASE_USER, []);
        }
        return $user;
    }

    public function getPassword() : string {
        $password = $this->data['password'] ?? null;
        if ( is_null($password) ) {
            throw new ExceptionWithData(ErrMsg::NULL_DATABASE_PASSWORD, []);
        }
        return $password;
    }

    public function getSchema() : string
    {
        $schema = $this->data['schema'] ?? null;
        if ( is_null($schema) )
            throw new ExceptionWithData(ErrMsg::NULL_DATABASE_SCHEMA, []);
        if ( !file_exists($schema) )
            throw new ExceptionWithData(ErrMsg::DATABASE_SCHEMA_FILE_DOES_NOT_EXIST, [
                'filename' => $schema
            ]);
        return $schema;
    }

}
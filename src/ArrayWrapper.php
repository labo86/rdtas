<?php
declare(strict_types=1);

namespace labo86\rdtas;

use ArrayAccess;
use labo86\exception_with_data\ExceptionWithData;

/**
 * Class ArrayWrapper
 * Esta clase sirve para que tener accesso a rreglos asociativos desde string.
 * Sirve para ahcer una transición suave. La estrategia es implementar un array con esta clase he ir agregandole metodos de a poco hasta finalmente tener toda la clase cubierta.
 * Finalmente debería dejar de utilizarse esta clase
 * @package labo86\rdtas
 */
class ArrayWrapper implements ArrayAccess
{
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function toArray() : array {
        return $this->data;
    }

    /**
     * @return string
     * @throws ExceptionWithData
     */
    public function toString() : string {
        return Util::arrayToString($this->data);
    }
}
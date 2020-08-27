<?php
declare(strict_types=1);

namespace labo86\rdtas;

/**
 * Class SmartArray
 * Un wrapper que sirve para acceder a propiedades anidadas de un array sin saber su orden.
 * Ejemplo
 * <code>
 * $array = new SmartArray([
 * 'a1' => [
 *   'b1' => 1,
 *   'b2' => 2
 *  ]
 * ];
 * $data('a1', 'b1')
 * $data('b1', 'a1');
 * </code>
 * @package labo86\rdtas
 */
class SmartArray {

    public $data = [];

    public function __construct(array $data = []) {
      $this->data = $data;
    }

    private static function searchInArray(array $data, $keys) {
      foreach ( $keys as $index => $key ) {
        if (isset($data[$key]))
          return $index;
      }
      return null;
    }

    public function __invoke(...$keys) {
      $data = $this->data;

      while ( !empty($keys) ) {
        $index = self::searchInArray($data, $keys);
        if ( is_null($index) ) return null;
        $data = $data[$keys[$index]];
        array_splice($keys, $index, 1);
      }

      return $data;
    }



}




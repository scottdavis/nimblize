<?php
class NimbleFinder implements NimbleRecordCommandInterface {
  public static $methods = array('find', 'find_all');
  public static function methods() {
    return static ::$methods;
  }
  /**
   * Processor for math functions
   * @param string $method
   * @param string $class
   * @param table $table
   * @param array $options
   * @return string
   */
  public static function do_method($method, $class, $table, $options = array()) {
    switch ($method) {
      case 'find':
      break;
      case 'find_all':
      break;
    }
  }
}

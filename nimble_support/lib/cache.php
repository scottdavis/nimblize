<?php

require_once('nimble_support/lib/inflector.php');

class Cache {
  static public $caches;
  static public $default_cache = 'globals';
  
  static public function get_cache($which = null) {
    $name = !is_null($which) ? $which : self::$default_cache;
    $name = preg_replace('#[^a-z_]#', '', $name);
    
    if (!isset(self::$caches[$name])) {
      $target = dirname(__FILE__) . "/caches/${name}_cache.php";
      if (file_exists($target)) {
        require_once($target);
        
        $class_name = Inflector::camelize($name, false) . 'Cache';
        
        if (class_exists($class_name)) {
          self::register_cache($name, $class_name::get_instance());
        }
      }
    }
    if (isset(self::$caches[$name])) {
      return self::$caches[$name]; 
    }
  }
  
  static public function register_cache($name, $object) {
    self::$caches[$name] = $object;
  }
}

?>
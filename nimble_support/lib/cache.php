<?php

class Cache {
  static public $caches;
  static public $default_cache = 'globals';
  
  static public function get_cache($which = null) {
    $name = !is_null($which) ? $which : self::$default_cache;
    $name = preg_replace('#[^a-z_]#', '', $name);
    
    if (!isset(self::$caches[$name])) {
      $target = dirname(__FILE__) . "/caches/${name}_cache.php";
      if (file_exists($target)) {
        $before_load = get_declared_classes();
        require_once($target);
        if (count($caching_classes = array_diff(get_declared_classes(), $before_load)) == 1) {
          $cache_class = reset($caching_classes);
          self::register_cache($name, $cache_class::get_instance());
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
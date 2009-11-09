<?php

require_once(dirname(__FILE__) . '/cache_interface.php');

class GlobalsCache implements CacheInterface {
  static private $instance;
  private $cache;
  
  public static function get_instance() {
    if (!isset(self::$instance)) {
      self::$instance = new GlobalsCache();
    } 
    return self::$instance;
  }
  
  private function __construct() {
    $this->cache = array(); 
  }
  
  public function set($key, $value, $ttl = 0) {
    $this->cache[$key] = $value;
  }
  
  public function get($key) {
    return $this->cache[$key]; 
  }
  
  public function exists($key) {
    return isset($this->cache[$key]); 
  }
  
  public function remove($key) {
    unset($this->cache[$key]); 
  }
  
  public function clear() {
    $this->cache = array(); 
  }
  
  public function stats() {
    return array(
      'count' => count($this->cache)
    ); 
  }
}

?>
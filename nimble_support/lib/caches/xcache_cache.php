<?php
/**
 * @package NimbleSupport
 */
require_once(dirname(__FILE__) . '/cache_interface.php');

class XcacheCache implements CacheInterface {
  static private $instance;
  
  public static function get_instance() {
    if (!isset(self::$instance)) {
      self::$instance = new XcacheCache();
    } 
    return self::$instance;
  }
  
  private function __construct() {}
  
  public function set($key, $value, $ttl = 0) {
    xcache_set($key, $value, $ttl);
  }
  
  public function get($key) {
    return xcache_get($key);
  }
  
  public function exists($key) {
    return xcache_isset($key);
  }
  
  public function remove($key) {
    xcache_unset($key);
  }
  
  public function clear() {}
  
  public function stats() {}
}

?>
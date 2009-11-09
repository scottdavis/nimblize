<?php

require_once(dirname(__FILE__) . '/cache_interface.php');

class ApcCache implements CacheInterface {
  static private $instance;

  public static function get_instance() {
    if (!isset(self::$instance)) {
      self::$instance = new ApcCache();
    }
    return self::$instance;
  }

  private function __construct() {}

  public function set($key, $value, $ttl = 0) {
    apc_store($key, $value, $ttl);
  }

  public function get($key) {
    return apc_fetch($key);
  }

  public function exists($key) {
  	$result = apc_fetch($key, $success);
    return $success;
  }

  public function remove($key) {
    apc_delete($key);
  }

  public function clear() {}

  public function stats() {}
}

?>
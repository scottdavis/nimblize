<?php

interface CacheInterface {
  public static function get_instance();  
  
  public function set($key, $value);  
  public function get($key);  
  public function exists($key);  
  public function remove($key);  
  public function clear();
  public function stats();
}

?>
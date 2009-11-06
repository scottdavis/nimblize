<?php

require_once('PHPUnit/Framework.php');
require_once('nimble_support/lib/cache.php');

class CacheTest extends PHPUnit_Framework_TestCase {
  function testRegisterCache() {
    $a = (object)array('test' => 'test2');
    Cache::register_cache('test', $a);
    $b = Cache::$caches['test'];
    
    $this->assertTrue($a === $b);
  }
}

?>
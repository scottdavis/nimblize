<?php

require_once('PHPUnit/Framework.php');
require_once('nimble_support/lib/caches/globals_cache.php');

class GlobalsCacheTest extends PHPUnit_Framework_TestCase {
  function setUp() {
    GlobalsCache::get_instance()->clear();
    $this->gc = GlobalsCache::get_instance(); 
  }
  
  /**
   * @covers GlobalsCache::get_instance
   */
  function testGetInstance() {
    $a = GlobalsCache::get_instance();
    $this->assertTrue($a === GlobalsCache::get_instance());
  }
  
  /**
   * @covers GlobalsCache::set
   * @covers GlobalsCache::get
   * @covers GlobalsCache::exists
   */
  function testSetGet() {
    $this->assertFalse($this->gc->exists('test'));
    $this->gc->set('test', 'test');
    $this->assertTrue($this->gc->exists('test'));
    $this->assertEquals('test', $this->gc->get('test'));
  }
  
  /**
   * @covers GlobalsCache::set
   * @covers GlobalsCache::exists
   * @covers GlobalsCache::remove
   * @depends testSetGet
   */
  function testRemove() {
    $this->gc->set('test', 'test');
    $this->assertTrue($this->gc->exists('test'));
    $this->gc->remove('test');
    $this->assertFalse($this->gc->exists('test'));
  }
  
  /**
   * @covers GlobalsCache::set
   * @covers GlobalsCache::exists
   * @covers GlobalsCache::remove
   * @depends testSetGet
   */
  function testClear() {
    $this->gc->set('test', 'test');
    $this->gc->set('test2', 'test2');
    $this->assertTrue($this->gc->exists('test'));
    $this->assertTrue($this->gc->exists('test2'));
    $this->gc->clear();
    $this->assertFalse($this->gc->exists('test'));
    $this->assertFalse($this->gc->exists('test2'));
  }
}

?>
<?php

require_once(dirname(__FILE__) . '/config.php');
/**
* @package FrameworkTest
*/
class InflectorTest extends PHPUnit_Framework_TestCase {
  function setUp() {
    Cache::get_cache()->clear(); 
  }
  
  function providerTestMagicMethods() {
    return array(
      array(false),
      array(true)
    ); 
  }
  
  /**
   * @dataProvider providerTestMagicMethods
   */
  function testMagicMethods($use_caching) {
    if ($use_caching) {
      $stats = Cache::get_cache()->stats();
      $this->assertEquals(0, $stats['count']);
    }
    $this->assertEquals('quizzes', Inflector::pluralize('quiz', $use_caching));
    if ($use_caching) {
      $stats = Cache::get_cache()->stats();
      $this->assertEquals(1, $stats['count']);
    }
  } 
}

?>
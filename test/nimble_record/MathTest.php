<?php 
 
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/config.php');
  /**
  * @package FrameworkTest
	* @todo add conditions checking
  */
  class MathTest extends PHPUnit_Framework_TestCase {
 
		public function setUp() {
			
		}
 		/**
		* @expectedException NimbleRecordException
		*/
		public function testMathFunctionFailsBadArgs() {
			User::max();
		}

		public function testMax() {
		  $this->assertEquals(10, User::max(array('column' => 'id')));
		}
 
		public function testMin() {
		  $this->assertEquals(1, User::min(array('column' => 'id')));
		}
 
		public function testCount() {
		  $this->assertEquals(10, User::count());
		}
 
		public function testSum() {
			$this->assertEquals(55, User::sum(array('column' => 'id')));
		}
		
		public function testSumWithArrayOfConditions() {
			$this->assertEquals(1, User::sum(array('column' => 'id', 'conditions' => array('id' => 1))));
		}
 
		public function testSumWithConditions() {
			$this->assertEquals(10, User::sum(array('column' => 'id', 'conditions' => 'id between 1 and 4')));
		}
 
		public function testCountWithConditions() {
			$this->assertEquals(4, User::count(array('column' => 'id', 'conditions' => 'id between 1 and 4', 'cache' => false)));
		}
 
		public function testMinWithConditions() {
			$this->assertEquals(1, User::min(array('column' => 'id', 'conditions' => 'id between 1 and 4')));
		}
 
		public function testMaxWithConditions() {
			$this->assertEquals(4, User::max(array('column' => 'id', 'conditions' => 'id between 1 and 4')));
		}
 
		public function tearDown() {

		}
 
	}
 
?>
<?php
	require_once(dirname(__FILE__) . '/test_config.php');

	class UsageTest extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
			
		}
		
		public function tareDown() {
			
		}
		
		public function testNewObject() {
			$obj = new User();
			$this->assertTrue(is_a($obj, 'User'));
		}
		
		public function testNewMassAssignment() {
			$obj = new User(array('name' => 'bob', 'my_int' => 1));
			$this->assertEquals($obj->name, 'bob');
			$this->assertEquals($obj->my_int, 1);
		}
		
		/**
		* @expectedException NimbleRecordException
		*/
		public function testMassAssignmentFailsNoProperty() {
			$obj = new User(array('foo' => 'bob', 'my_int' => 1));
		}
		/**
		* @expectedException NimbleRecordException
		*/
		public function testReadOnlyException() {
			$obj = new User(array('address' => 'foo'));
		}
		/**
		* @expectedException NimbleRecordException
		*/
		public function testWhitelist() {
			$obj = new Photo(array('user_id' => User::find('first')->id, 'title' => 'test', 'description' => 'foo'));
		}
		
	}
	
?>
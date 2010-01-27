<?php

	require_once(dirname(__FILE__) . '/test_config.php');
  /**
  * @package FrameworkTest
  */
	class FinderTest extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
			NimbleRecord::start_transaction();
		}
		
		public function tearDown() {
			NimbleRecord::rollback_transaction();
		}
		
		/**
		** find_all tests
		*/
		
		public function testFindAll() {
			$users = User::find_all();
			$this->assertEquals($users->length, 10);
		}
	
		
		public function testFindAllRange() {
			$users = User::find_all(array('conditions' => 'my_int BETWEEN 2 AND 6'));
			foreach($users as $user) {
				$this->assertTrue((int) $user->my_int >= 2 || (int) $user->my_int <= 6);
			}
		}
		/**
		  * @expectedException NimbleRecordNotFound
		  */
		public function findFailsBadInput() {
		  $users = User::find('foo');
		}
		
		public function findAllTestfromFind() {
			$users = User::find('all');
			$this->assertEquals($users->length, 10);
		}
		
		public function testFindAllLimit() {
			$users = User::find_all(array('limit' => '0,5'));
			$this->assertEquals($users->length, 5);
		}
		
		public function testFindAllOrder() {
			$users = User::find_all(array('order' => 'my_int DESC'));
			$test = array();
			$test_compair = range(10,1);
			foreach($users as $user) {
				$test[] = $user->my_int;
			}
			$this->assertEquals($test_compair, $test);
		}
		
		public function testFindAllOrderAndLimit() {
			$users = User::find_all(array('limit' => '0,5', 'order' => 'my_int DESC'));
			$test_compair = range(10,6);
			$test = array();
			foreach($users as $user) {
				$test[] = $user->my_int;
			}
			$this->assertEquals($test_compair, $test);
		}
		
		
		public function testFindAllConditionsOrderLimit() {
			$users = User::find_all(array('limit' => '0,5', 'order' => 'my_int DESC', 'conditions' => "name LIKE '%names%'"));
			$test_compair = range(10,6);
			$test = array();
			foreach($users as $user) {
				$test[] = $user->my_int;
			}
			$this->assertEquals($test_compair, $test);
		}
		
		
		/**
		* Find First tests
		*/
		
		public function testFindFirst() {
			$u = User::find('first');
			$this->assertTrue(is_a($u, 'User'));
			$this->assertEquals($u->id, '1');
		}
		
		public function testFindFirstMyInt() {
			$u = User::find('first', array('conditions' => array('my_int' => '3')));
			$this->assertEquals($u->name, 'names3');
			$this->assertEquals($u->my_int, 3);
		}
		
		public function testFindFirstMyIntAgain() {
			$u = User::find('first', array('conditions' =>"my_int = '3'"));
			$this->assertEquals($u->name, 'names3');
			$this->assertEquals($u->my_int, 3);
		}
		
		/**
		* regular find tests
		*/
		
		public function testFindWitharray() {
			$users = User::find(1,2,3,4,5);
			$this->assertEquals(5, $users->length);
		}
		
		public function testFindWitharrayAndConditions() {
			$users = User::find(1,2,3,4,5, array('conditions' =>"my_int = '3'"));
			$this->assertEquals(1, $users->length);
			$u = $users->first();
			$this->assertEquals(3, $u->id);
		}
		
		public function testFindSingle() {
			$user = User::find(1);
			$this->assertEquals(1, $user->id);	
		}
		
		public function testFindFirstLoadPhotos() {
			$user = User::find('first');
			$this->assertEquals(11, $user->photos->length);
		}
		
		
		/**
		* MAGIC FINDER TESTS
		*/
		
		public function testMagicFinder() {
			$obj = User::find_by_name('names1');
			$this->assertEquals($obj->name, 'names1');
			$this->assertEquals($obj->my_int, 1);
			$this->assertTrue(is_a($obj, 'User'));
		}
		
		public function testMagicFinderMulti() {
			$obj = User::find_by_name_and_my_int('names1', 1);
			$this->assertEquals($obj->name, 'names1');
			$this->assertEquals($obj->my_int, 1);
			$this->assertTrue(is_a($obj, 'User'));
		}
		
		public function testMagicFindAllFinder() {
			$id = User::find_by_name('names1')->id;
			$obj = Photo::find_all_by_user_id($id);
			$this->assertEquals($obj->length, 11);
			foreach($obj as $record) {
				$this->assertEquals($record->user_id, $id);
			}
		}
		
	}


?>
<?php
	require_once(dirname(__FILE__) . '/config.php');

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
		
		public function testSaveOnNewRecord() {
			$obj = new User(array('name' => 'bob5000', 'my_int' =>1));
			$this->assertTrue($obj->save());
			$this->assertTrue(User::exists('name', 'bob5000'));
			$this->assertEquals($obj->name, 'bob5000');
			$this->assertEquals($obj->my_int, 1);
			$obj->destroy();
		}
		
		public function testSaveOnNewRecordusingSetters() {
			$obj = new User();
			$obj->name = 'bob5000';
			$obj->my_int = 1;
			$this->assertTrue($obj->save());
			$this->assertTrue(User::exists('name', 'bob5000'));
			$this->assertEquals($obj->name, 'bob5000');
			$this->assertEquals($obj->my_int, 1);
			$obj->destroy();
		}
		
		public function testSavedOnExsistingRecord() {
			$obj = User::find('first', array('conditions' => array('name' => 'names1')));
			$this->assertEquals($obj->name, 'names1');
			$new_name = 'this is my new name';
			$obj->name = $new_name;
			$this->assertTrue($obj->save());
			$obj2 = User::find('first', array('conditions' => array('name' => $new_name)));
			$this->assertEquals($obj2->name, $new_name);
			$this->assertEquals($obj->new_record, false);
			$this->assertEquals($obj2->new_record, false);
		}
		
		public function testNullOnNewObjectColumnGet() {
			$user = new User();
			$this->assertEquals(NULL, $user->name);
		}
		
		public function testNullOnNewObjectColumnCall() {
			$user = new User();
			$this->assertEquals(NULL, $user->name());
		}
		/**
		* @expectedException NimbleRecordException
		*/
		public function testUnknownPropertyFails() {
			$user = new User();
			$user->foo;
		}
		
		/**
		* @expectedException NimbleRecordException
		*/
		public function tryToSetaBogusProperty() {
			$user = new User();
			$user->foo = 'bar';
		}
		
		public function testToString() {
			$user = User::find('first');
			ob_start();
			echo $user;
			$out = ob_get_clean();
			$this->assertEquals($out, (string) $user->id);
		}
		
		public function testToStringNewRecord() {
			$user = new User();
			$this->assertEquals('NULL', (string) $user);
		}
		
		
		public function testDelete() {
			$start = User::count(array('cache' => false));
			$user = User::_create(array('name' => 'test_user', 'my_int' => 1500));
			$this->assertTrue($user->saved);
			$this->assertEquals(User::count(array('cache' => false)), $start + 1);
			User::delete($user->id);
			try {
				User::find($user->id);
			} catch(NimbleRecordNotFound $e) {
				$this->assertTrue(is_a($e, 'NimbleRecordNotFound'));
				$this->assertEquals(User::count(array('cache' => false)), $start);
			}
		}
		
		public function testMassdelete() {
			
		}
		
	}
	
?>
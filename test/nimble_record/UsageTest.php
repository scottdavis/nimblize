<?php
	require_once(dirname(__FILE__) . '/config.php');
	/**
  * @package FrameworkTest
  */
	class UsageTest extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
			NimbleRecord::start_transaction();
		}
		
		public function tearDown() {
			NimbleRecord::rollback_transaction();
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
			$obj = new User(array('name' => 'bob5000', 'my_int' => 1000));
			$this->assertTrue($obj->save());
			$this->assertTrue(User::exists('name', 'bob5000'));
			$this->assertEquals($obj->name, 'bob5000');
			$this->assertEquals($obj->my_int, 1000);
			$obj->destroy();
		}
		
		public function testSaveOnNewRecordFails() {
			$obj = new User(array('name' => 'bob5000', 'my_int' => 1));
			$this->assertFalse($obj->save());
			$this->assertEquals(count($obj->errors), 1);
		}
		
		public function testSaveOnNewRecordusingSetters() {
			$obj = new User();
			$obj->name = 'bob5000';
			$obj->my_int = 10000;
			$this->assertTrue($obj->save());
			$this->assertTrue(User::exists('name', 'bob5000'));
			$this->assertEquals($obj->name, 'bob5000');
			$this->assertEquals($obj->my_int, 10000);
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
			$this->assertEquals(NULL, $user->name);
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
		
		public function testDeleteOnInstance() {
			$start = User::count(array('cache' => false));
			$user = User::_create(array('name' => 'test_user', 'my_int' => 1500));
			$this->assertTrue($user->saved);
			$this->assertEquals(User::count(array('cache' => false)), $start + 1);
			$id = $user->id;
			$user->delete();
			try {
				User::find($id);
			} catch(NimbleRecordNotFound $e) {
				$this->assertTrue(is_a($e, 'NimbleRecordNotFound'));
				$this->assertEquals(User::count(array('cache' => false)), $start);
			}
		}
		
		public function testMassdelete() {
			$start = User::count(array('cache' => false));
			$users = array();
			foreach(range(0,3) as $i) {
				$users[] = $u = User::_create(array('name' => 'test_name' . $i, 'my_int' => 12 + $i));
				$this->assertTrue($u->saved);
				unset($u);
			}
			$this->assertEquals(User::count(array('cache' => false)), $start + 4);
			$ids = collect(function($u) {return $u->id;}, $users);
			User::delete($ids);
			try {
				User::find($ids);
			} catch (NimbleRecordNotFound $e) {
					$this->assertTrue(is_a($e, 'NimbleRecordNotFound'));
					$this->assertEquals(User::count(array('cache' => false)), $start);
			}
		}
		
		public function testMassDeleteManyArgs() {
			$start = User::count(array('cache' => false));
			$users = array();
			foreach(range(0,3) as $i) {
				$users[] = $u = User::_create(array('name' => 'test_name' . $i, 'my_int' => 12 + $i));
				$this->assertTrue($u->saved);
				unset($u);
			}
			$this->assertEquals(User::count(array('cache' => false)), $start + 4);
			$ids = collect(function($u) {return $u->id;}, $users);
			call_user_func_array(array('User', 'delete'), $ids);
			try {
				User::find($ids);
			} catch (NimbleRecordNotFound $e) {
					$this->assertTrue(is_a($e, 'NimbleRecordNotFound'));
					$this->assertEquals(User::count(array('cache' => false)), $start);
			}
		}
		/**
		* @expectedException NimbleRecordEXception
		*/
		public function testTryToSetVarThatsNotAColumn() {
			$user = new User();
			$user->foo = 'bar';
		}
		
		public function testIsset() {
			$user = User::find(1);
			$this->assertTrue(isset($user->name));
		}
		
		public function testReloadColumns() {
			$old_col = User::columns();
			$new_col = User::columns(true);
			$this->assertEquals($old_col, $new_col);
		}
		
		public function testResetQueryCache() {
			$mem = memory_get_usage();
			$this->assertTrue(!empty(NimbleRecord::$query_cache));
			Nimblerecord::reset_cache();
			$this->assertTrue(empty(NimbleRecord::$query_cache));
			$this->assertTrue($mem > memory_get_usage());
		}
		
		
		public function testUpdateUsingUnderscore() {
			$user = User::find(1);
			User::_update($user->id, array('name' => 'foo'));
			$user2 = User::_find(1);
			$this->assertEquals($user2->name, 'foo');
		}
		
		/**
		* @expectedException NimbleRecordException
		*/
		public function testUpdateUsingUnderscoreFails() {
			$user = User::find(1);
			User::_update($user->id, array('my_int' => 2));
			$user2 = User::_find(1);
			$this->assertEquals($user2->my_int, $user->my_int);
		}
		
		public function testUpdateUsingFails() {
			$user = User::find(1);
			$u = User::update($user->id, array('my_int' => 2));
			$user2 = User::_find(1);
			$this->assertEquals($user2->my_int, $user->my_int);
			$this->assertEquals($u->errors['my_int'], "My int : 2 already exists try something else");
		}
		
		public function testUpdateUsingSameInt() {
			$user = User::find(1);
			$u = User::update($user->id, array('my_int' => $user->my_int));
			$user2 = User::_find(1);
			$this->assertFalse($user2->updated_at === $user->updated_at);
			$this->assertEquals($user2->my_int, $user->my_int);
		}
		
		
		public function testSanitize() {
			$sql = NimbleRecord::sanitize(array('name=? AND foo=?', 'bob', 'joe'));
			$this->assertEquals("name='bob' AND foo='joe'", $sql);
		}
		
		public function testSanitizeSingleValueManyQ() {
			$sql = NimbleRecord::sanitize(array('name LIKE ? OR summary LIKE ? OR description LIKE ?', 'foo'));
			$this->assertEquals("name LIKE 'foo' OR summary LIKE 'foo' OR description LIKE 'foo'", $sql);
		}
		
	}
	
?>
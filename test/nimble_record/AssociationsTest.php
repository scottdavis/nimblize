<?php
	require_once(dirname(__FILE__) . '/test_config.php');

	class AssociationsTest extends PHPUnit_Framework_TestCase {
	
		public function testBelongsTo() {
			$u = new User();
			$u->belongs_to('photo', 'cats', 'dogs');
			$ass = User::$associations;
			$vals = array('photo', 'cats', 'dogs');
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['belongs_to']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['belongs_to'][$i]);
			}
		}
		
		public function testHasMany() {
			$u = new User();
			$u->has_many('dogs', 'deer', 'ducks');
			$vals = array('photos', 'dogs', 'deer', 'ducks');
			$ass = User::$associations;
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['has_many']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['has_many'][$i]);
			}
		}
	
		public function testHasAndBelongsToMany() {
			$u = new User();
			$u->has_and_belongs_to_many('dogs', 'deer', 'ducks');
			$vals = array('dogs', 'deer', 'ducks');
			$ass = User::$associations;
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['has_and_belongs_to_many']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['has_and_belongs_to_many'][$i]);
			}
		}
	
		public function testHasManyPolyMorhic() {
			$u = new User();
			$u->has_many_polymorphic('dogs', 'deer', 'ducks');
			$vals = array('dogs', 'deer', 'ducks');
			$ass = User::$associations;
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['has_many_polymorphic']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['has_many_polymorphic'][$i]);
			}
		}
		public function testBelongsToPolyMorhic() {
			$u = new User();
			$u->belongs_to_polymorphic('dogs', 'deer', 'ducks');
			$vals = array('dogs', 'deer', 'ducks');
			$ass = User::$associations;
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['belongs_to_polymorphic']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['belongs_to_polymorphic'][$i]);
			}
		}
		
		
		public function testAssociationAutoLoad() {
			User::create(array('name' => 'bob', 'my_int' => 5));
			$u = User::find('first');
			$u->photos;
			$this->assertTrue(isset(User::$associations['User']['has_many']));
			$this->assertTrue(in_array('photos', User::$associations['User']['has_many']));
		}
		
		
		public function testForeignKeyfunction() {
			$key = User::association_foreign_key('User');
			$this->assertEquals('user_id', $key);
		}
		
		public function testForeignKeyCustomSuffix() {
			$old = NimbleRecord::$foreign_key_suffix;
			NimbleRecord::$foreign_key_suffix = 'foo';
			$key = User::association_foreign_key('User');
			$this->assertEquals('user_foo', $key);
			NimbleRecord::$foreign_key_suffix = $old;
		}
		
		public function testBelongsToFindsUser() {
			$photo = Photo::find(1);
			$this->assertEquals(1, $photo->user->id);
		}
		
		public function testAssociationModel() {
			$name = User::association_model('photos');
			$this->assertEquals('Photo', $name);
		}
		
		public function testAssociationLoadsData() {
			$users = User::find_all(array('include' => 'photos'));
			foreach($users as $user) {
				foreach($user->photos as $photo) {
					$this->assertEquals($user->id, $photo->user_id);
				}
			}
		}
		
		
		public function testTest() {
			$u = User::find('first', array('conditions' => 'my_int = 3'));
			$this->assertEquals($u->my_int, '3');
		}
		
		public function setUp() {
			NimbleRecord::start_transaction();
		}
		
		public function tearDown() {
			NimbleRecord::rollback_transaction();
		}
	}
?>
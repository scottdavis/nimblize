<?php
	require_once(dirname(__FILE__) . '/test_config.php');

	class AssociationsTest extends PHPUnit_Framework_TestCase {
	
		public function testBelongsTo() {
			$u = new User();
			$u->belongs_to('photo', 'cats', 'dogs');
			$ass = NimbleAssociation::$associations;
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
			$ass = NimbleAssociation::$associations;
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
			$ass = NimbleAssociation::$associations;
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
			$ass = NimbleAssociation::$associations;
			$this->assertTrue(isset($ass['User']));
			$this->assertTrue(isset($ass['User']['has_many_polymorphic']));
			for($i=0;$i<count($vals);$i++) {
				$this->assertTrue($vals[$i] == $ass['User']['has_many_polymorphic'][$i]);
			}
		}
		
		public function testAssociationAutoLoad() {
			User::create(array('name' => 'bob', 'my_int' => 5));
			$u = User::find('first');
			$u->photos;
			$this->assertTrue(isset(NimbleAssociation::$associations['User']['has_many']));
			$this->assertTrue(array_include('photos', NimbleAssociation::$associations['User']['has_many']));
		}
		
		
		public function testForeignKeyfunction() {
			$key = NimbleAssociation::foreign_key('User');
			$this->assertEquals('user_id', $key);
		}
		
		public function testForeignKeyCustomSuffix() {
			$old = 'id';
			NimbleRecord::$foreign_key_suffix = 'foo';
			$key = NimbleAssociation::foreign_key('User');
			$this->assertEquals('user_foo', $key);
			NimbleRecord::$foreign_key_suffix = $old;
		}
		
		public function testBelongsToFindsUser() {
			$photo = Photo::find(1);
			$this->assertEquals(1, $photo->user->id);
		}
		
		public function testAssociationModel() {
			$name = NimbleAssociation::model('photos');
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
		
		public function testPolymorphicHasManyLoad() {
			$photo = Photo::find(1);
			$this->assertEquals(1, $photo->comments->length);
		}
		
		public function testPolymorphicBelongsToLoad() {
			$comment = Comment::find(1);
			$this->assertEquals(1, $comment->photo->id);
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
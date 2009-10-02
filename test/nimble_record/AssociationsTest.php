<?php
	require_once(dirname(__FILE__) . '/config.php');

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
		
	}
?>
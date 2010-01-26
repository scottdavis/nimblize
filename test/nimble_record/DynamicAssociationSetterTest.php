<?php
	require_once(dirname(__FILE__) . '/config.php');

	class DynamicAssociationSetterTest extends PHPUnit_Framework_TestCase {
	
		/**
		* @expectedException NimbleRecordException
		*/
		public function testFailsBadAssocType() {
			$assoc = new NimbleAssociationBuilder('User', 'foo', 'dogs');
		}
		/**
		* @expectedException NimbleRecordException
		*/		
		public function testHasManyFailsBadOption() {
			$assoc = new NimbleAssociationBuilder('User', 'has_many', 'dogs');
			$assoc->dogs('cats');		
		}
	
		public function testHasMany() {
			$assoc = new NimbleAssociationBuilder(new User(), 'has_many', 'dogs');
			$this->assertEquals($assoc->type, 'has_many');
			$this->assertEquals($assoc->name, 'dogs');
			$this->assertEquals(INflector::classify('user'), $assoc->class);
		}
	
		public function testHasManyWithThrough() {
			$assoc = new NimbleAssociationBuilder('User', 'has_many', 'dogs');
			$assoc->through('cats');
			$this->assertTrue(isset($assoc->through));
			$this->assertEquals($assoc->through, 'cats');			
		}
		
		public function testHasManyWithAs() {
			$assoc = new NimbleAssociationBuilder('User', 'has_many', 'dogs');
			$assoc->as('cats');
			$this->assertTrue(isset($assoc->as));
			$this->assertEquals($assoc->as, 'cats');			
		}
		
		public function testHasManyWithAsThrough() {
			$assoc = new NimbleAssociationBuilder('User', 'has_many', 'dogs');
			$assoc->as('cats')->through('cows');
			$this->assertTrue(isset($assoc->as));
			$this->assertEquals($assoc->as, 'cats');	
			$this->assertTrue(isset($assoc->through));
			$this->assertEquals($assoc->through, 'cows');		
		}
		
	}
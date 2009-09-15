<?php
	require_once(dirname(__FILE__) . '/config.php');

	class ValidationsTest extends PHPUnit_Framework_TestCase {
		
		public function acceptance_false_provider() {
			$b = array(
					array('value' => 0, 'column_name' => 'col'),
					array('value' => '0', 'column_name' => 'col'),
				);
				return array($b);
		}
		
		public function acceptance_provider() {
			$a = array(
					array('value' => '1', 'column_name' => 'col'),
					array('value' => 1, 'column_name' => 'col')
				);

				return array($a);
		}
		
		/**
		* @dataProvider acceptance_provider
		*/
		public function test_acceptance_of($args) {
			$validation = NimbleValidation::acceptance_of($args);
			$this->assertTrue($validation[0]);
		}
		
		/**
		*	@dataProvider acceptance_false_provider
		*/
		public function test_acceptance_of_fails($args) {
			$validation = NimbleValidation::acceptance_of($args);
			$this->assertFalse($validation[0]);
			$this->assertEquals('col', $validation[1]);
			$this->assertEquals(Inflector::humanize('col') . ' must be accepted', $validation[2]);
		}
		
		public function confirmation_of_data() {
			$a = array(
					array('column_name' => 'col', 'value1' => 1, 'value2' => 1),
					array('column_name' => 'col', 'value1' => 1, 'value2' => '1'),
					array('column_name' => 'col', 'value1' => 0, 'vlaue2' => '0'),
					array('column_name' => 'col', 'value1' => 'poop', 'value2' => 'poop')
				);
				return array($a);
		}
		/**
		*	@dataProvider confirmation_of_data
		*/
		public function test_confirmation_of($args) {
			$valid = NimbleValidation::confirmation_of($args);
			$this->assertTrue($valid[0]);
		}
		
		public function confirmation_of_data_fail() {
			$a = array(
					array('column_name' => 'col', 'value1' => 1, 'value2' => 2),
					array('column_name' => 'col', 'value1' => 1, 'value2' => '0'),
					array('column_name' => 'col', 'value1' => 0, 'vlaue2' => 'poo')
				);
				return array($a);
		}
		/**
		*	@dataProvider confirmation_of_data_fail
		*/
		public function test_confirmation_of_fails($args) {
			$valid = NimbleValidation::confirmation_of($args);
			$this->assertFalse($valid[0]);
			$this->assertEquals(Inflector::humanize('col') . " doesn't match confirmation", $valid[2]);
		}
		
		
		public function exclusion_of_data() {
			$a = array(
						array('column_name' => 'col', 'value' => 5, 'in' => range(6, 50)),
						array('column_name' => 'col', 'value' => '5', 'in' => range(6, 50)),
					);
			return array($a);
		}
		
		/**
		* @dataProvider exclusion_of_data
		*/
		public function test_exclusion_of($args) {
			$valid = NimbleValidation::exclusion_of($args);
			$this->assertTrue($valid[0]);
		}
		
		public function exclusion_of_data_fail() {
			$a = array(
						array('column_name' => 'col', 'value' => 5, 'in' => range(1, 50)),
						array('column_name' => 'col', 'value' => '5', 'in' => range(1, 50)),
					);
			return array($a);
		}
		
		/**
		* @dataProvider exclusion_of_data_fail
		*/
		public function test_exclusion_of_fail($args) {
			$valid = NimbleValidation::exclusion_of($args);
			$this->assertFalse($valid[0]);
		}
		
		public function inclusion_of_data() {
			$a = array(
						array('column_name' => 'col', 'value' => 5, 'in' => range(1, 10)),
						array('column_name' => 'col', 'value' => '5', 'in' => range(1, 10)),
					);
			return array($a);
		}
		
		/**
		* @dataProvider inclusion_of_data
		*/
		public function test_inclusion_of($args) {
			$valid = NimbleValidation::inclusion_of($args);
			$this->assertTrue($valid[0]);
		}
		
		public function inclusion_of_data_fail() {
			$a = array(
						array('column_name' => 'col', 'value' => 5, 'in' => range(6, 10)),
						array('column_name' => 'col', 'value' => '5', 'in' => range(6, 10)),
					);
			return array($a);
		}
		
		/**
		* @dataProvider inclusion_of_data_fail
		*/
		public function test_inclusion_of_fail($args) {
			$valid = NimbleValidation::inclusion_of($args);
			$this->assertFalse($valid[0]);
		}
		
		
		
	}

?>
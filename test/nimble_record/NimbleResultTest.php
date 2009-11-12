<?php
require_once(dirname(__FILE__) . '/config.php');

	class NimbleResultTest extends PHPUnit_Framework_TestCase {
		
		
		public function testLengthMethod() {
			$users = User::find('all');
			$this->assertEquals($users->length(), 10);
			$this->assertEquals($users->length, 10);
		}

	}
?>
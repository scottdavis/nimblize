<?php
require_once(dirname(__FILE__) . '/config.php');

	class NimbleResultTest extends PHPUnit_Framework_TestCase {
		
		
		public function testLengthMethod() {
			$users = User::find('all');
			$this->assertEquals($users->length(), 10);
			$this->assertEquals($users->length, 10);
		}
		
		public function testKeys() {
			$users = User::find('all');
			$keys = $users->keys();
			$i = 0;
			foreach($keys as $key) {
				$this->assertEquals($i, $key);
				$i++;
			}
		}
		
		public function testToString() {
			$users = User::find('all');
			$s = (string) $users;
			$this->assertTrue(empty($s));
		}

	}
?>
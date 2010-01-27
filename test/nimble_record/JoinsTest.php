<?php
	require_once(dirname(__FILE__) . '/config.php');
  /**
  * @package FrameworkTest
  */
	class JoinsTest extends PHPUnit_Framework_TestCase {
	
		public function testJoinsBelongsTo() {
			$out = NimbleAssociation::process_join(new Photo, 'user');
			$match = 'INNER JOIN `users` ON (`photos`.user_id = `users`.id)';
			$this->assertEquals($match, $out);
		}
		
		public function testJoinsHasMany() {
			$out = NimbleAssociation::process_join(new User, array('photos'));
			$match = 'INNER JOIN `photos` ON (`users`.id = `photos`.user_id)';
			$this->assertEquals($match, $out);
		}
		
		public function testJoinsBelongsToString() {
			$out = NimbleAssociation::process_join('Photo', 'user');
			$match = 'INNER JOIN `users` ON (`photos`.user_id = `users`.id)';
			$this->assertEquals($match, $out);
		}
		
		public function testJoinsBelongsToStringLowerCase() {
			$out = NimbleAssociation::process_join('photo', 'user');
			$match = 'INNER JOIN `users` ON (`photos`.user_id = `users`.id)';
			$this->assertEquals($match, $out);
		}
		
		public function testJoinsBelongsToStringLowerCaseBelongsTo() {
			$out = NimbleAssociation::process_join('user', 'photos');
			$match = 'INNER JOIN `photos` ON (`users`.id = `photos`.user_id)';
			$this->assertEquals($match, $out);
		}
		
		
		
		
	}
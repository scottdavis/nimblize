<?php

	require_once(dirname(__FILE__) . '/config.php');
	
	class CallbackTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			$this->user_data = array('name' => 'bobby', 'my_int' => 33);
		}
	
	
		public function testCreateCallbacksSuccess() {
			$u = User::create($this->user_data);
			$this->assertTrue($u->test_before_create);
			$this->assertTrue($u->test_after_create);
			$this->assertTrue($u->test_before_save);
			$this->assertTrue($u->test_after_save);
			$this->assertTrue($u->test_before_validations);
			$this->assertTrue($u->test_after_validations);
			$this->assertFalse($u->test_before_update);
			$this->assertFalse($u->test_after_update);
		}
		
		public function testUpdateCallbacksSuccess() {
			$user = User::create($this->user_data);
			$u = User::update($user->id, $this->user_data);
			$this->assertFalse($u->test_before_create);
			$this->assertFalse($u->test_after_create);
			$this->assertTrue($u->test_before_save);
			$this->assertTrue($u->test_before_update);
			$this->assertTrue($u->test_after_update);
			$this->assertTrue($u->test_after_save);
			$this->assertTrue($u->test_before_validations);
			$this->assertTrue($u->test_after_validations);
		}
		
		public function testCreateFails() {
			$u = User::create(array());
			$this->assertFalse($u->test_before_create);
			$this->assertFalse($u->test_after_create);
			$this->assertFalse($u->test_before_save);
			$this->assertFalse($u->test_after_save);
			$this->assertTrue($u->test_before_validations);
			$this->assertTrue($u->test_after_validations);
			$this->assertFalse($u->test_before_update);
			$this->assertFalse($u->test_after_update);
		}
		
		public function testUpdateFails() {
			$user = User::create($this->user_data);
			$u = User::update($user->id, array('name' => NULL));
			$this->assertFalse($u->test_before_create);
			$this->assertFalse($u->test_after_create);
			$this->assertFalse($u->test_before_save);
			$this->assertFalse($u->test_after_save);
			$this->assertTrue($u->test_before_validations);
			$this->assertTrue($u->test_after_validations);
			$this->assertFalse($u->test_before_update);
			$this->assertFalse($u->test_after_update);
		}
		
		
		public function tareDown() {
			if(User::exists('name', $this->user_data['name'])) {
				$u = User::find_by_name_and_my_int($this->user_data['name'], $this->user_data['my_int']);
				$u->destroy();
			}
		}
		
		
		
	}

?>
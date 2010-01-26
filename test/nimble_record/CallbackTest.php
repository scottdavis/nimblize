<?php

	require_once(dirname(__FILE__) . '/config.php');
	
	class CallbackTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			NimbleRecord::start_transaction();
			$this->user_data = array('name' => 'bobby', 'my_int' => 3000);
		}
	
	
		public function testCreateCallbacksSuccess() {
			$u = User::_create($this->user_data);
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
			$user = User::_create($this->user_data);
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
			$user = User::_create($this->user_data);
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
		
		
		public function tearDown() {
			NimbleRecord::rollback_transaction();
		}
		
		
		
	}

?>
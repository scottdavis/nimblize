<?php

	class User extends NimbleRecord {
		
		var $test_before_create = false;
		var $test_before_save = false;
		var $test_before_update = false;
		var $test_after_create = false;
		var $test_after_save = false;
		var $test_after_update = false;
		var $test_before_validations = false;
		var $test_after_validations = false;
		
		
		public static $protected = array('last_name');
		public static $read_only = array('address');
		
		public function validations() {
			$this->validates_presence_of(array('name', 'my_int'));
			$this->validates_uniqueness_of('my_int');
		}
		
		public function associations() {
			$this->has_many('photos');
		}
		
		
		
		public function before_create() {
			$this->test_before_create = true;
		}
		
		public function before_update() {
			$this->test_before_update = true;
		}
		
		public function before_save() {
			$this->test_before_save = true;
		}
		
		public function after_save() {
			$this->test_after_save = true;
		}
		
		public function after_create() {
			$this->test_after_create = true;
		}
		
		public function after_update() {
			$this->test_after_update = true;
		}
		
		public function before_validation() {
			$this->test_before_validations = true;
		}
		
		public function after_validation() {
			$this->test_after_validations = true;
		}
		
		
	}

?>
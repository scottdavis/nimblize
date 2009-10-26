<?php

	class User extends NimbleRecord {
		
		public static $protected = array('last_name');
		public static $read_only = array('address');
		
		public function validations() {
			$this->validates_presence_of(array('name', 'my_int'));
		}
		
		public function associations() {
			$this->has_many('photos');
		}
		
	}

?>
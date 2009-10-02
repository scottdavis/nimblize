<?php

	class User extends NimbleRecord {
		
		public function validations() {
			$this->validates_presence_of(array('name', 'my_int'));
		}
		
		public function associations() {
			$this->has_many('photos');
		}
		
	}

?>
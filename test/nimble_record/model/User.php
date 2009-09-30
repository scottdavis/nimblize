<?php

	class User extends NimbleRecord {
		
		public function validations() {
			$this->validates_presence_of(array('name', 'my_int'));
		}
		
		public function associations() {
			$this->belongs_to('bob', 'joe');
			$this->has_and_belongs_to_many('poop');
			$this->has_many('photos');
		}
		
	}

?>
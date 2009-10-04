<?php

	class Photo extends NimbleRecord {
		public function validations() {
			$this->validates_presence_of(array('title', 'user_id'));
		}
		
		public function associations() {
			$this->belongs_to('user');
		}
	}

?>
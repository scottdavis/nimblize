<?php

	class Photo extends NimbleRecord {
		
		public static $white_list = array('title', 'user_id');
		
		public function validations() {
			$this->validates_presence_of(array('title', 'user_id'));
		}
		
		public function associations() {
			$this->belongs_to('user');
			$this->has_many('comments')->as('commentable');
		}
	}

?>
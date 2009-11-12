<?php

class Comment extends NimbleRecord {
	
	public function validations() {
		$this->validates_presence_of('comment');
	}
	
	public function associations() {
		$this->belongs_to_polymorphic('commentable');
	}
}

?>
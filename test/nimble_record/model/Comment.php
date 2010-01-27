<?php
/**
* @package FrameworkTest
*/
class Comment extends NimbleRecord {
	
	public function validations() {
		$this->validates_presence_of('comment');
	}
	
	public function associations() {
		$this->belongs_to('commentable')->polymorphic(true);
	}
}

?>
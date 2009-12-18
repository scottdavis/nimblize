<?php
 class Club extends NimbleRecord {
	
	
	public function validations() {
		$this->validates_presence_of('name');
	}
	
	public function associations() {
		$this->has_and_belongs_to_many('users');
	}
	
	
	}
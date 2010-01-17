#Create

	Model::create(<params>)

##Creating a record

	User::create(array('name' => 'bob', 'password' => 'foo'))
	
or, _ throws an exception if validations failed or saved failed

	User::_create(array('name' => 'bob', 'password' => 'foo'))


##Detecting if a record saved

	$user = User::create(array('name' => 'bob', 'password' => 'foo'));
	if($user->saved) {
		//do something
	}else{
		//handle errors
	}

or
	
	try {
		User::_create(array('name' => 'bob', 'password' => 'foo'));
		//do something
	}catch(NimbleRecordException $e) {
		//handel errors
	}
	
##Callbacks

1. before_create
2. after_create
3. before_save - runs after before_create
4. after_save - runs after after_create

Example:

	public function before_save() {
		if(!$this->new_record) {
		 	$old = User::_find($this->id);
		 	if($this->password == $old->password) {
		   	return;
		 	}
		}
		$this->salt = static::generate_salt();
		$this->password = static::hash_password($this->password, $this->salt);
	}

	public function after_create() {
		UserMailer::deliver_new_user($this->email, $this);
		$path = FileUtils::join(NIMBLE_ROOT, 'get', $this->username);
		FileUtils::mkdir_p($path);
		chmod($path, 0755);
	}

#Create
##Creating a record

	User::create(array('name' => 'bob', 'password' => 'passowrd'))
	
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

## _Create

Normaly a create doesn't throw an exception if it fails instead the $obj->saved flag is set to false
with _create it thows an exception
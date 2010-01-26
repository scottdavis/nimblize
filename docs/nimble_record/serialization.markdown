##Serialization

Formats

1. Json
2. Xml
	
###Options
	
	array('only' => NULL, 'except' => NULL, 'include' => NULL, 'lamda' => NULL, 'append' => NULL, 'methods' => NULL);
	
1. only - `array(phonenumber)` - would only return the phone number in the output
2. except - exclude certin columns `array('name', 'address')` - would return all columns except name and address
3. include - not implimented yet
4. lamda - closure that executes on every key value pair `function($key, $value) {$value = urlencode($value);return array($key, $value);}`
5. append - key, value pair or key, closure pair `array('foo' => function($obj) {return $obj->user->bar;})`
6. methods - calls a method on the database object requires key, value format ex. `array(name => 'get_user_name')`

###Usage

	$user = User::find('first');
	$user->to_xml();
	
or

	$user->to_json();
	
	
Options usage 

	$user->to_json(array('methods' => 'calculate_age'))
	
you can mix and match also 

	$user->to_json(array('append' => array('foo' => 'bar'), 'only' => array('name')))
	
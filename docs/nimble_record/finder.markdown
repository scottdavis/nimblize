#Finders

	Model::find('all', <options>)
	Model::find('first', <options>)
	Model::find_all(<options>)
	Model::find_by<column>()
	Model::find_by<column>_and_<column>...()
	
	
	
##Finder Options

	array('conditions' => <conditions>, 'limit' => <string>, 'order' => <string> 'joins' => <string>, 'group' => <string>)

###Conditions (WHERE)

`array('conditions' => <conditions>)`

`array('user_id' => 1)`
	
`"user_id = '1'"`
	
`NimbleRecord::sanitize(array("name = ?", 1))`

###Limit

`array('limit' => <string>)`

`"0,10"`

###Order

`array('order' => <string>)`

`"name DESC"`

###Group

`array('group' => <string>)`

`"user.id"`

###Joins

`array('joins' => <string>)`

`"INNER JOIN packages ON user.id=packages.user_id"`
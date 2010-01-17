#Finders

	Model::find('all', <options>)
	Model::find('first', <options>)
	Model::find_all(<options>)
	Model::find_by<column>()
	Model::find_by<column>_and_<column>...()
	
##Finder Options

	array('conditions' => <conditions>, 'limit' => <string>, 'order' => <string> 'joins' => <string>, 'group' => <string>)

###Conditions (WHERE)

`array('user_id' => 1)`
	
`"user_id = '1'"`
	
`NimbleRecord::sanitize(array("name = ?", 1))`

###Limit

`"0,10"`

###Order

`"name DESC"`

###Group

`"user.id"`

###Joins

`"INNER JOIN packages ON user.id=packages.user_id"`
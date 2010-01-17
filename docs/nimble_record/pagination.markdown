#Pagination

	Model::paginate(<options>)

##Options

Options are the same as finders except that 'per_page' and 'page' must me set

##Examples

	User::paginate(array('per_page' => 5, 'page' => 1, 'conditions' => array('zipcode' => 12345)))
	
	
	
##Helpers

	$users = User::paginate(array('per_page' => 5, 'page' => 1, 'conditions' => array('zipcode' => 12345)))
	
In the view	

	paginate($collection, $options)

###Options

	$pagination_options = array(
	      'class'          => 'pagination',
	      'previous_label' => '&laquo; Previous',
	      'next_label'     => 'Next &raquo;',
	      'param_name'     => 'page'
	    );

###Usage	

	<?php echo paginate($users) ?>
	
Output example 3 is the active page

	<ul>
		<li><a href='foo?page=1'>1</a></li>
		<li><a href='foo?page=2'>2</a></li>
		<li>3</li>
	<ul>
#Controllers

Controller live in app/controllers

All controllers extend `\ApplicationController`

##Before and After Filters
Before Filters run before the controller method is called
After Filters run after the controller method is called
Example filter syntax:
Global filter

	public function before_filter() {
		$this->user = User::find($_GET['user_id]);
	}
	
Example scoped before filter to method foo 
*Note: this will only execute if you are calling the foo method from your routes*

	public function before_filter_for_foo() {
		$this->user = User::find($_GET['user_id]);
	}

Example scoped before filter to all but foo

	public function before_filter_except_foo() {
		$this->user = User::find($_GET['user_id]);
	}

Example after filter
	
	public function after_filter() {
		//do something
	}
	
*Note: After filters follow the same syntax as before filters*

##Rendering and Echoing

###Rendering
If you do not define a render in a controller method it will try to find a template that matches the controller method to render 
so it is not needed to render if you are doing simple CRUD

	$this->render(<template path from app/view/>)
	
Example if i want to render a template in app/view/user/show.php

	$this->render('user/show.php');
	
	
###Echoing

If you wish to echo raw data out of a controller and do not need to render a template
then the `$this->has_renderd` needs to be set to true within your controller method

###Layout

Layout wrap the view functions allowing for global wrapper templates by default one is created in `app/view/layout/application.php`

if you wish to render no layout simply set the `$this->layout` to false

if you wish to render a different template then application.php use the 

`$this->set_layout_template(<path>)` function
	
##Redirecting and Headers

##Redirects

to redirect from within a controller you may simply do `$this->redirect(<url>)`. Combining the redirect function with the url helpers makes a powerful partnership.
	
Example:

	$this->redirect_to(u('category_path', 1));

##Headers
*NOTE:NEVER SET HEADERS WITH THE `header()` php function unless you know what you are doing*

`$this->header(<header info>, <status code>)`
status code defaults to 200
	
Example:

	$this->header('Content-type: application/json', 200);


	
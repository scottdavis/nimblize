#Getting started

install the framework

	pear install jetviper21.pearfarm.org/nimblize

create your application skeleton

	nimblize <your app name>


##Database settings and enviroments

Database settings are stored as json files in

config/<enviroment>/database.json
	
*Note: the only adapter that is testing and working at this time is mysql*

##Command line tools

1. script/db
2. script/generate
3. script/update
4. script/niceroutes


**Note: in order to use these commands with other environments such as test you need to prefix the command with `NIMBLE_ENV=<enviroment>`**

###script/db

####Usage

Drop, Create, Reset

`script/db drop` drops the databse based on your enviroment settings

`script/db create` creates the database based on your environment settings

`script/db reset` drop and recreates and migrates the database based on your enviroment settings

Migrate

`script/db migrate` migrates the database to the max version
	
`script/db migrate <version number>` - migrates to a version
	
`script/db migrate up` migrates up to the max version
	
Stories (test data laoder)

`script/db stories load` runs the up method in the storyhelper class in the lib folder

`script/db stories clear` runs the down method on the storyhelper class in the lib folder

`script/db stories reload` drops the database and reloads stories

###script/generate

####Usage

1. model
2. controller
3. migration
4. mailer
5. help - displays help


Controller `script/generate controller name`
Example:
	`script/generate controller my` 
Creates:

      -> app/controller/MyController.php
			-> app/view/my/index.php
			-> app/view/my/edit.php
			-> app/view/my/add.php
			-> app/view/my/show.php

Model `script/generate model name`
Generate a Model that extends NimbleRecord
`script/generate model Task`

	-> app/model/task.php
	-> db/migrations/<timestamp>_create-task.php
	-> test/unit/TaskTest.php

Mailer `script/generate mailer bar <methods> ...`
Generate a Model that extends NimbleMailer
`script/generate mailer foo`

	-> app/model/bar.php
	-> app/view/foo/foo.php
	-> app/view/foo/foo.txt

Test `script/generate test (functional | unit) name`
Generates a Test that extends nimbles PHPUnit functional test case
	`script/generate test functional my`
	
	 -> app/test/functional/MyControllerTest.php 

Generates a Tests that extends nimbles PHPUnit unit test case
`script/generate test unit my`

	-> app/test/unit/MyUnitTest.php

###script/update
Updates the current config and framework files to the current version of nimblize (use with caution)

###script/niceroutes
Displays a nice print out of the routes defined in config/routes.php
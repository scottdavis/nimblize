#Nimblize

Nimblize is a php framework much like ruby on rails that aims to be RESTful and make rapid development in php a reality

##Getting started

install the framework

	pear install jetviper21.pearfarm.org/nimblize

create your application skeleton

	nimblize <your app name>

Also see [Getting started](http://github.com/jetviper21/nimblize/blob/master/docs/getting_started.markdown) and [PHP Docs](http://docs.nimblize.com)


###Database settings and enviroments

Database settings are stored as json files in

config/<enviroment>/database.json
	
*Note: the only adapter that is testing and working at this time is mysql*
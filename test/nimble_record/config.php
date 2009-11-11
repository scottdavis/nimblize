<?php
	require_once('PHPUnit/Framework.php');
	require_once(dirname(__FILE__) . '/../../nimblize.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/migration.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/lib/migration_runner.php');
	require_once(dirname(__FILE__) . '/model/User.php');
	require_once(dirname(__FILE__) . '/model/Photo.php');

	if(file_exists(dirname(__FILE__) . '/database.json')) {
		/**
		* Example database.json contents
		* {"host":"localhost","database":"nimble_record_test","username":"root","password":"","adapter":"mysql"}
		*/
		$json = file_get_contents(dirname(__FILE__) . '/database.json');
		$settings = json_decode($json, true);
	}else{
		$settings = array('host' 			=> 'localhost',
											'database' 	=> 'nimble_record_test',
											'username'	=> 'root',
											'password'	=> '',
											'adapter'		=> 'mysql'
									 		);
	}
	NimbleRecord::establish_connection($settings);
	define('MYSQL_DATABASE', $settings['database']);

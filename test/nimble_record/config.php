<?php
	define('MYSQL_DATABASE', 'nimble_record_test');
	require_once('PHPUnit/Framework.php');
	require_once(dirname(__FILE__) . '/../../nimblize.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/migration.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/lib/migration_runner.php');
	require_once(dirname(__FILE__) . '/model/User.php');
	require_once(dirname(__FILE__) . '/model/Photo.php');
	
	
	if(file_exists(dirname(__FILE__) . '/databse.json')) {
		$json = file_get_contents(dirname(__FILE__) . '/databse.json');
		$settings = json_decode($json, true);
	}else{
		$settings = array('host' 			=> 'localhost',
											'database' 	=> MYSQL_DATABASE,
											'username'	=> 'root',
											'password'	=> '',
											'adapter'		=> 'mysql'
									 		);
	}					
	NimbleRecord::establish_connection($settings);
	
		
	
?>
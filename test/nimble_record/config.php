<?php
	define('MYSQL_DATABASE', 'nimble_record_test');
	require_once('PHPUnit/Framework.php');
	require_once(dirname(__FILE__) . '/../../nimble_support/base.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/base.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/migration.php');
	require_once(dirname(__FILE__) . '/../../nimble_record/migrations/lib/migration_runner.php');
	require_once(dirname(__FILE__) . '/model/user.php');
	require_once(dirname(__FILE__) . '/model/photo.php');
	$settings = array('host' 			=> 'localhost',
										'database' 	=> MYSQL_DATABASE,
										'username'	=> 'root',
										'password'	=> '',
										'adapter'		=> 'mysql'
									 );
									
	NimbleRecord::establish_connection($settings);
	
		
	
?>
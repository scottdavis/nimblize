<?php
	require_once('PHPUnit/Framework.php');
	require_once(__DIR__ . '/../../nimblize.php');
	require_once(__DIR__ . '/../../nimble_record/migrations/migration.php');
	require_once(__DIR__ . '/../../nimble_record/migrations/lib/migration_runner.php');
	foreach(array('User', 'Photo', 'Comment', 'Club', 'ClubUser') as $model) {
		require_once(__DIR__ . "/model/$model.php");
	}
	if(!defined('CONNECTED')) {
		if(file_exists(__DIR__ . '/database.json')) {
			/**
			* Example database.json contents
			* {"host":"localhost","database":"nimble_record_test","username":"root","password":"","adapter":"mysql"}
			*/
			$json = file_get_contents(__DIR__ . '/database.json');
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
		define('CONNECTED', true);
	}
	
	NimbleRecord::$debug = true;
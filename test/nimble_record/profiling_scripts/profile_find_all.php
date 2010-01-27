<?php

	/**
	* Run profile_create.php before running this
	* @package FrameworkTest
	*/

	function echo_memory_usage() { 
	   $mem_usage = memory_get_usage(true); 
	   if ($mem_usage < 1024) {
	   	var_dump($mem_usage." bytes"); 
	   }elseif ($mem_usage < 1048576) {
	   	var_dump(round($mem_usage/1024,2)." kilobytes"); 
	   }else{ 
	   	var_dump(round($mem_usage/1048576,2)." megabytes"); 
		}
   }



	echo_memory_usage();
	define('MYSQL_DATABASE', 'nimble_record_test');
	require_once(dirname(__FILE__) . '/../../../nimble_support/base.php');
	require_once(dirname(__FILE__) . '/../../../nimble_record/base.php');
	require_once(dirname(__FILE__) . '/../../../nimble_record/migrations/migration.php');
	require_once(dirname(__FILE__) . '/../../../nimble_record/migrations/lib/migration_runner.php');
	require_once(dirname(__FILE__) . '/../model/user.php');
	require_once(dirname(__FILE__) . '/../model/photo.php');
	$settings = array('host' 			=> 'localhost',
										'database' 	=> MYSQL_DATABASE,
										'username'	=> 'root',
										'password'	=> '',
										'adapter'		=> 'mysql'
									 );
									
	NimbleRecord::establish_connection($settings);
	echo_memory_usage();
	$u = User::find_all(array('limit' => '0,500'));
	echo_memory_usage();
	$u->clear();
	echo_memory_usage();
	$u2 = User::find_all();
	echo_memory_usage();
	$u2->clear();
	echo_memory_usage();


?>
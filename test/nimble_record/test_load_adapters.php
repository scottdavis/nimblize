<?php
	/**
	* This test can only be ran by its self
	* 
  * @package FrameworkTest
  */



	require_once(dirname(__FILE__) . '/config.php');
	class TestAdapterLoading extends PHPUnit_Framework_TestCase {
		
		public function test_load_mysql() {
			$settings = array('host' 			=> 'localhost',
												'database' 	=> 'nimble_record_test',
												'username'	=> 'root',
												'password'	=> '',
												'adapter'		=> 'mysql'
											 );
			
			
			NimbleRecord::establish_connection($settings);
			
			$this->assertTrue(isset(NimbleRecord::$adapter));
			$this->assertTrue(is_a(NimbleRecord::$adapter, 'MysqlAdapter'));
			$this->assertTrue(class_exists('MysqlQueryResult'));
			
			$this->assertFalse(class_exists('Sqlite3Adapter'));
			$this->assertFalse(class_exists('Sqlite3QueryResult'));
			
		}
		
		
		public function test_load_sqlite3() {
			$settings = array('file'			=> dirname(__FILE__) . 'my.db',
												'adapter'		=> 'sqlite3',
												'database' => 'test'
											 );
			
			
			NimbleRecord::establish_connection($settings);
			
			$this->assertTrue(isset(NimbleRecord::$adapter));
			$this->assertTrue(is_a(NimbleRecord::$adapter, 'Sqlite3Adapter'));
			$this->assertTrue(class_exists('Sqlite3QueryResult'));
			
		}
		
	}
?>
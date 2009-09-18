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
	
		class TestMigration extends Migration {
			public $tables = array("users", "photos");

			public function up() {
					$table = $this->create_table('users');
						$table->string('name');
						$table->integer('my_int');
						$table->timestamps();
					$table->go();

					$table2 = $this->create_table('photos');
						$table2->belongs_to('user');
						$table2->string('title');
					$table2->go();
				}

				public function down() {
					foreach($this->tables as $t) {
						$this->drop_table($t);
					}
				}



		}
	
	//load the test database
	if(!defined('DATABASE_CREATED')) {
		$m = new Migration();
		$m->drop_database(MYSQL_DATABASE);
		$m->create_database(MYSQL_DATABASE);
		define('DATABASE_CREATED', true);
	}
	
	//load the table if it hasn't been loaded
	
	if(!defined('TABLE_CREATED')) {
		$g = new TestMigration();
		$g->down();
		$g->up();
		define('TABLE_CREATED', true);
	}
	
?>
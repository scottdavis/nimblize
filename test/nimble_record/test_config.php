<?php
	if(!defined('NIMBLE_IS_TESTING')) {
		define("NIMBLE_IS_TESTING", true);
	}
	require_once(dirname(__FILE__) . '/config.php');

	class TestMigration extends Migration {
		public $tables = array("users", "photos", "comments");

		public function up() {
			$table = $this->create_table('users');
				$table->string('name', array('null' => false));
				$table->string('last_name');
				$table->string('address');
				$table->integer('my_int');
				$table->timestamps();
			$table->go();

			$table2 = $this->create_table('photos');
				$table2->belongs_to('user');
				$table2->string('title');
				$table2->string('description');
			$table2->go();
			
			$table3 = $this->create_table('comments');
				$table3->polymorphic('commentable');
				$table3->string('comment');
			$table3->go();
		}

		public function down() {
			foreach(array_reverse($this->tables) as $t) {
				$this->drop_table($t);
			}
		}
	}

	/**
	 * TODO remove the dependency on a constant, if possible
	 */
	function reload_database_tables() {
		Migration::drop_database(MYSQL_DATABASE);
		Migration::create_database(MYSQL_DATABASE);
	}

	//load the test database
	if(!defined('DATABASE_CREATED')) {
		reload_database_tables();
		define('DATABASE_CREATED', true);
	}

	function run_migration() {
		$g = new TestMigration();
		$g->down();
		$g->up();
	}

	//load the table if it hasn't been loaded

	if(!defined('TABLE_CREATED')) {
		run_migration();
		user_data();
		define('TABLE_CREATED', true);
	}


	function create_users() {
		foreach(range(1,10) as $i) {
			User::_create(array('name' => 'names' . $i, 'my_int' => $i));
		}
	}

	function fill_user_photos() {
		$users = User::find_all();
		foreach($users as $user) {
			create_user_photos($user->id);
		}
	}

	function create_user_photos($user_id) {
		foreach(range(0, 10) as $i) {
			$p = Photo::_create(array('user_id' => $user_id, 'title' => 'title' . $i));
			create_comment($p->id);
		}
	}
	
	
	function create_comment($photo_id) {
		Comment::_create(array('comment' => 'this is my comment', 'commentable_type' => 'photo', 'commentable_id' => $photo_id));
	}

	function user_data() {
		create_users();
		fill_user_photos();
	}
<?php
	require_once(dirname(__FILE__) . '/config.php');

	class QueryTest extends PHPUnit_Framework_TestCase {

		public function testSelectQueryBuild() {
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$this->assertEquals('SELECT * FROM `users`', $query->build());
		}
		
		public function testSelectQueryConditions() {
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$query->where = '`users`.id = 5';
			$this->assertEquals('SELECT * FROM `users` WHERE `users`.id = 5', $query->build());
		}
		
		public function testSelectQueryLimit() {
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$query->limit = '0,5';
			$this->assertEquals('SELECT * FROM `users` LIMIT 0,5', $query->build());
		}
		
		public function testSelectQueryConditionsAndLimit() {
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$query->where = '`users`.id = 5';
			$query->limit = '0,5';
			$this->assertEquals('SELECT * FROM `users` WHERE `users`.id = 5 LIMIT 0,5', $query->build());
		}
		
		public function testSelectJoins() {
			$join = NimbleAssociation::process_join(new User, 'photos');
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$query->join = $join;
			$this->assertEquals('SELECT * FROM `users` ' . $join, $query->build());
		}
		
		public function testSelectJoinsComditionsAndLimit() {
			$join = NimbleAssociation::process_join(new User, 'photos');
			$query = new NimbleQuery();
			$query->from = NimbleRecord::table_name('User');
			$query->where = '`users`.id = 5';
			$query->limit = '0,5';
			$query->join = $join;
			$this->assertEquals('SELECT * FROM `users` ' . $join . ' WHERE `users`.id = 5 LIMIT 0,5', $query->build());
		}
		
		public function testBuildInsert() {
			$query = new NimbleQuery(NimbleQuery::INSERT);
			$query->insert_into = NimbleRecord::table_name('User');
			$query->columns = array('name', 'my_int');
			$query->values = array('bob', 5);
			$match = "INSERT INTO `users` (`name`, `my_int`) VALUES ('bob', '5')";
			$this->assertEquals($match, $query->build());
		}
		/**
		* @expectedException NimbleRecordException
		*/
		public function testBuildInsertFails() {
			$query = new NimbleQuery(NimbleQuery::INSERT);
			$query->insert_into = NimbleRecord::table_name('User');
			$query->columns = array('name', 'my_int');
			$query->build();
		}
		
		
		public function testBuildUpdate() {
			$query = new NimbleQuery(NimbleQuery::UPDATE);
			$query->update = NimbleRecord::table_name('User');
			$query->columns = array('name', 'my_int');
			$query->values = array('bob', 5);
			$match = "UPDATE `users` SET `name` = 'bob', `my_int` = '5'";
			$this->assertEquals($match, $query->build());
		}
		
		public function testBuildUpdateWhere() {
			$query = new NimbleQuery(NimbleQuery::UPDATE);
			$query->update = NimbleRecord::table_name('User');
			$query->where = '`id` = 5';
			$query->columns = array('name', 'my_int');
			$query->values = array('bob', 5);
			$match = "UPDATE `users` SET `name` = 'bob', `my_int` = '5' WHERE `id` = 5";
			$this->assertEquals($match, $query->build());
		}
		
		
		public function testBuildDelete() {
			$query = new NimbleQuery(NimbleQuery::DELETE);
			$query->from = NimbleRecord::table_name('User');
			$match = "DELETE FROM `users`";
			$this->assertEquals($match, $query->build());
		}
		
		public function testBuildDeleteConditions() {
			$query = new NimbleQuery(NimbleQuery::DELETE);
			$query->from = NimbleRecord::table_name('User');
			$query->where = "`id` = 5";
			$match = "DELETE FROM `users` WHERE `id` = 5";
			$this->assertEquals($match, $query->build());
		}
		
		public function testIn() {
			$in = NimbleQuery::in('`id`', range(1,10));
			$this->assertEquals('`id` IN(1,2,3,4,5,6,7,8,9,10)', $in);
		}
		
	}
?>
<?php

	require_once(dirname(__FILE__) . '/config.php');
  /**
  * @package FrameworkTest
  */	
	class PaginationTest extends PHPUnit_Framework_TestCase {
	
	
		public function PaginationData() {
			$data = array(
							array(2, 4, 4, range(5,8)),
							array(1, 4, 4, range(1,4)),
				);
				return $data;
		}
	
		/**
		* @dataProvider PaginationData
		*/
		public function testPaginationReturns($page, $per_page, $total, $ids) {
			$users = User::paginate(array('per_page' => $per_page, 'page' => $page));
			$this->assertEquals($total, count($users));
			$this->assertEquals(collect(function($u){return $u->id;}, $users), $ids);
			$this->assertEquals(User::count(), $users->total_count);
			$this->assertEquals($per_page, $users->per_page);
			$this->assertEquals($page, $users->page);
		}
		
		public function testPaginationWithConditions() {
			$users = User::paginate(array('per_page' => 3, 'page' => 1, 'conditions' => array('name' => 'names1')));
			$this->assertEquals(1, count($users));
			$this->assertEquals(1, $users->total_count);
			$this->assertEquals(3, $users->per_page);
			$this->assertEquals(1, $users->page);	  
		}
		
		
		
	
		
	}

?>
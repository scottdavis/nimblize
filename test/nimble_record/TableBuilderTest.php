<?php
	require_once(dirname(__FILE__) . '/config.php');
	
	class TableBuilderTest extends PHPUnit_Framework_TestCase {
		public function testBuildTable() {
			$users = User::find('all');
			$table = new SmartTable($users, $cols=4);
			$table->td = TagHelper::content_tag('td', "name: {name} --- <br/>");
			$b = $table->build();
			$e = !empty($b);
			$this->assertTrue($e);
		}
		
		public function testBuildTable2() {
			$users = User::find('all');
			$table = new SmartTable($users, $cols=4);
			$table->callback = function($object) {
				return TagHelper::content_tag('td', "name: {$object->name} --- <br/>");
			};
			$b = $table->build();
			$e = !empty($b);
			$this->assertTrue($e);
		}
		
	}
?>
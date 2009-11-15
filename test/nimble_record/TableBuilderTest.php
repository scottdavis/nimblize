<?php
	require_once(dirname(__FILE__) . '/config.php');
	
	class TableBuilderTest extends PHPUnit_Framework_TestCase {
		public function testBuildTable() {
			$users = User::find('all');
			$table = new SmartTable($users, $cols=4);
			$table->td = TagHelper::content_tag('td', "name: {name} --- <br/>");
			$this->assertEquals($table->build(), '<table><tbody><tr class=""><td>name: names1 --- <br/></td><td>name: names2 --- <br/></td><td>name: names3 --- <br/></td><td>name: names4 --- <br/></td></tr><tr class=""><td>name: names5 --- <br/></td><td>name: names6 --- <br/></td><td>name: names7 --- <br/></td><td>name: names8 --- <br/></td></tr><tr class=""><td>name: names9 --- <br/></td><td>name: names10 --- <br/></td><td>&nbsp;</td></tr></tbody></table>');
		}
		
		public function testBuildTable2() {
			$users = User::find('all');
			$table = new SmartTable($users, $cols=4);
			$table->callback = function($object) {
				return TagHelper::content_tag('td', "name: {$object->name} --- <br/>");
			};
			$this->assertEquals($table->build(), '<table><tbody><tr class=""><td>name: names1 --- <br/></td><td>name: names2 --- <br/></td><td>name: names3 --- <br/></td><td>name: names4 --- <br/></td></tr><tr class=""><td>name: names5 --- <br/></td><td>name: names6 --- <br/></td><td>name: names7 --- <br/></td><td>name: names8 --- <br/></td></tr><tr class=""><td>name: names9 --- <br/></td><td>name: names10 --- <br/></td><td>&nbsp;</td></tr></tbody></table>');
		}
		
	}
?>
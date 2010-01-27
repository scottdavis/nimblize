<?php


	require_once('PHPUnit/Framework.php');
	require_once(dirname(__FILE__) . '/../../nimble_support/lib/file_utils.php');
	require_once(dirname(__FILE__) . '/config.php');
	/**
  * @package FrameworkTest
  */
	class CyclerTest extends PHPUnit_Framework_TestCase {
		
		public function testCyclerCycles() {
			
			foreach(array('brown', 'green', 'red', 'green') as $color) {
				$cycle = cycle('brown', 'green', 'red', 'green');
				$this->assertEquals($color, $cycle);
			}
		}
		
			public function testCyclerResets() {

				foreach(array('brown', 'brown', 'brown', 'brown') as $color) {
					$cycle = cycle('brown', 'green', 'red', 'green');
					$this->assertEquals($color, $cycle);
					cycler::reset_cycler();
				}
			}
		
		
	}

?>
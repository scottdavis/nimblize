<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../../nimble_support/lib/file_utils.php');
require_once(dirname(__FILE__) . '/config.php');

class FileUtilsTest extends PHPUnit_Framework_TestCase {
  function testJoin() {
  	$args = array('test', 'test2', 'test3');
  	foreach (array(false, true) as $pass_as_array) {  		
  		if ($pass_as_array) {
  			$result = call_user_func('FileUtils::join', $args);
  		} else {
  			$result = call_user_func_array('FileUtils::join', $args);
  		}
  		$this->assertEquals(implode(DIRECTORY_SEPARATOR, $args), $result);
  	}
  }	
}

?>
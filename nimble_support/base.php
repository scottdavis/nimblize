<?php
	require_once(dirname(__FILE__) . '/lib/inflector.php');
	require_once(dirname(__FILE__) . '/lib/date_helper.php');
	
	
	function array_include($value, $array) {
		$array = array_flip($array);
		return isset($array[$value]);
	}
	
	
?>
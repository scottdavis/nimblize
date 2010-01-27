<?php
/**
* @package FrameworkTest
*/
	require_once(dirname(__FILE__) . '/../config.php');
	
	for($i=0;$i<10;$i++) {
		$u = User::create(array('name' => 'bob' . $i, 'my_int' => "$i"));
	}
	
?>
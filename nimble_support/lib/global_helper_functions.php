<?php
	/**
	* echos out text using htmlspecialchars to help avoid xss attacks
	* @param string $text
	*/
	function h($text) {
		echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}
	
	/**
	* Faster version of in_array only works on single level arrays
	* @param string $value
	* @param array $array
	*/
	function array_include($value, array $array) {
		$array = array_flip($array);
		return isset($array[$value]);
	}
	

?>
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
	
	/**
	* Similar to rubys collect method
	* @param function $func
	* @param array|interator $array
	* @uses collect(function($value){return $value+1}, range(1,5));
	*/
	function collect($func, $array) {
		$out = array();
		foreach($array as $value) {
			array_push($out, $func($value));
		}
		return $out;
	}
	/**
	*
	* @param string $file
	* @param string $alt
	* @param array $options
	*/
	function image_tag($file, $alt = '', $options = array()) {
		echo AssetTag::image($file, $alt, $options);
	}

?>
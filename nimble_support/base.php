<?php
	require_once(dirname(__FILE__) . '/lib/file_utils.php');
	require_once(dirname(__FILE__) . '/lib/inflector.php');
	require_once(dirname(__FILE__) . '/lib/date_helper.php');
	require_once(dirname(__FILE__) . '/lib/asset_tag.php');
	require_once(dirname(__FILE__) . '/lib/cycler.php');
	require_once(dirname(__FILE__) . '/lib/mime.php');
	require_once(dirname(__FILE__) . '/lib/tag_helper.php');
	require_once(dirname(__FILE__) . '/lib/string_cacher.php');
	
	
	function array_include($value, $array) {
		$array = array_flip($array);
		return isset($array[$value]);
	}
	
	
?>
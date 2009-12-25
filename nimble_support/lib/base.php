<?php
	/**
	* @package Support
	* Loads in all support classes
	*/
	$dir = __DIR__;
	require_once($dir . '/file_utils.php');
	foreach(array('tag_helper', 'mime', 'string_cacher', 
				  'asset_tag', 'cycler', 'form_helper') as $file) {
		require_once(FileUtils::join($dir, $file . '.php'));
	}


?>
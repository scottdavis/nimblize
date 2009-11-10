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
		unset($func);
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

	/**
		* Function to create a link
		* @param string $name Link Name
		* @param string $url Url for the link
	**/
  function link_to($name, $url) {
    return TagHelper::content_tag('a', $name, array('href' => $url));
  }

	/**
	 * Function to create a link that is actualy javascript! for triggering the delete method on controllers
	 * @param string $name Link name
	 * @param string $url Url for link
	 * @param boolean $confirm Adds a javascript confirm box to the link
	**/
	function delete_link($name, $url, $confirm = true) {
		if($confirm) {
			$confirmtxt = 'confirm(\'Are you sure?\')';
		}else{
			$confirmtxt = 'true';
		}
		$content = "if ($confirmtxt) { var f = document.createElement('form');
		       f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href;
		       var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method');
		       m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); };return false;";
		return TagHelper::content_tag('a', $name, array('href' => $url, 'onclick' => $content));
	}
	
	/**
	 * Function to shorten a string and add an ellipsis 
	 * @param string $string Origonal string
	 * @param integer $max Maximum length
	 * @param string $rep Replace with... (Default = '' - No elipsis -)
	 * @return string
	 **/
	function truncate($string, $max = 25, $rep = '&hellip;') {
	    $leave = $max - strlen ($rep);
	    return substr_replace($string, $rep, $leave);
	}

?>
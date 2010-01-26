<?php

	/**
	* @package support
	* This File contains conveniat wrappers for helper functions
	* For more information on a function see its alias class method
	*/

	/**
	* echos out text using htmlspecialchars to help avoid xss attacks
	* @param string $text
	*/
	function h($text) {
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
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
	* Iterates and calls the closure on each index
	* @param function $func
	* @param array|iterator $array
	* @uses collect(function($value){return $value+1}, range(1,5));
	*/
	function collect($func, $array) {
		$out = array();
		foreach($array as $value) {
			$options = array($value);
			$out[] = $func($value);
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
	function image_tag() {
		$args = func_get_args();
		return call_user_func_array(array('AssetTag', 'image'), $args);
	}

	/**
		* Function to create a link
		* @param string $name Link Name
		* @param string $url Url for the link
	  **/
  function link_to($name, $url, $options = array()) {
    $options = array_merge($options, array('href' => $url, 'title' => $name));
    return TagHelper::content_tag('a', $name, $options);
  }
  
	/**
		* Function to create link to the previous page
		* @param string $text Link Name
	  **/  
  function link_to_back($text = 'Back') {
    $href = 'javascript:history.go(-1)';
    if(isset($_SERVER['HTTP_REFERER'])) {
      $href = $_SERVER['HTTP_REFERER'];
    }
    return TagHelper::content_tag('a', $text, array('href' => $href, 'title' => 'back'));
  }

	/**
	 * Function to create a link that is actualy javascript! for triggering the delete method on controllers
	 * @param string $name Link name
	 * @param string $url Url for link
	 * @param boolean $confirm Adds a javascript confirm box to the link
	**/
	function delete_link($name, $url, $confirm = true, $confirm_text = 'Are you sure?') {
		if($confirm) {
			$confirmtxt = "confirm('$confirm_text')";
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
	
	/**
	 * Alias for the TagHelper::pagination method for building pagination views
	 * @see TagHelper::pagination
	 * @return string
	 **/
	function paginate() {
		$args = func_get_args();
		return call_user_func_array(array('TagHelper', 'pagination'), $args);
	}
	
	/**
	* @see AssetTag::javascript_include_tag()
	* @return string
	*/
	function javascript_include_tag() {
		$args = func_get_args();
		return call_user_func_array(array('AssetTag', 'javascript_include_tag'), $args);
	}
	
	/**
	* @see AssetTag::stylesheet_link_tag()
	* @return string
	*/
	function stylesheet_link_tag() {
		$args = func_get_args();
		return call_user_func_array(array('AssetTag', 'stylesheet_link_tag'), $args);
	}
	/**
	* same as calling $_REQUEST['foo'] just a nice helper
	*/
	function params($key) {
		if(isset($_REQUEST[$key])) {
			return $_REQUEST[$key];
		}
	}
	
	function is_assoc($array) {
	    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}
	
	function escape_javascript($javascript) {
  	$escape = array("\r\n"  => '\n',
          					"\r"    => '\n',
          					"\n"    => '\n',
          					'"'     => '\"',
          					"'"     => "\\'"
      							);
      return str_replace(array_keys($escape), array_values($escape), $javascript);
  }
	
	function javascript_tag($js) {
		return TagHelper::content_tag('script', $js, array('type' => 'text/javascript'));
	}
	
	
?>
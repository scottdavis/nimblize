<?php
	
	require_once(dirname(__FILE__) . '/form_helper/form.php');
	require_once(dirname(__FILE__) . '/form_helper/remote.php');

	function error_messages_for($class) {
		if(empty($class->errors)) {
			return;
		}
		$errors = $class->errors;
		$out = array(TagHelper::tag('ul', array('class' => 'errors')));
		foreach($errors as $col => $error) {
			array_push($out, TagHelper::content_tag('li', TagHelper::content_tag('p', $error)));
		}
		array_push($out, TagHelper::close_tag('ul'));

		$message = TagHelper::content_tag('h2' , "Errors occured saving this record", array('class' => 'error_message'));
		return TagHelper::content_tag('div',  $message . join("\n", $out), array('class' => 'error_container'));
	}

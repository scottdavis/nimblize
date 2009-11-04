<?php
define('CLI_RUNNER', true);


require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/simple_html_dom.php');
// because we can't guarantee where we'll be in the filesystem, find the
// nearest config/boot.php file from the current working directory.

//set the enviroment to test
$_SERVER['WEB_ENVIRONMENT'] = 'test';
$_SERVER['REQUEST_METHOD'] = '';
$path_parts = explode(DIRECTORY_SEPARATOR, getcwd());
while (!empty($path_parts)) {
  $path = implode(DIRECTORY_SEPARATOR, array_merge($path_parts, array("config", "boot.php")));
  if (file_exists($path)) {
    define("NIMBLE_IS_TESTING", true);
    define("NIMBLE_TEST", true);
    require_once($path); break;    
  } else {
    array_pop($path_parts);
  } 
}

if (!defined("NIMBLE_IS_TESTING")) {
  throw new Exception("Could not find Nimble config/boot.php from " . getcwd() . "!");
  exit(1); 
}
/** mock session as an array **/
	$_SESSION = $_POST = $_GET = array();

	require_once(dirname(__FILE__) . '/functional_testcase.php');
	require_once(dirname(__FILE__) . '/unit_testcase.php');

?>

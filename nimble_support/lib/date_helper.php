<?php
/**
 * @package NimbleSupport
 */
class DateHelper {
	
	public static $formats = array(
			'db' => 'Y-m-d H:i:s',
			'standard' => 'm/d/y',
			'full' => 'F j, Y, g:i a',
			'rfc822' => 'd M Y'
		);
	
	
	public static function to_string($format, $timestamp) 
	{
		
		 $formats = static::$formats;
			if(!empty($format[$formats]) && !isset($formats[$format])){
				throw new \Exception('Invaild Date output format');
			}
			if (!empty($timestamp) && !isset($timestamp)){
				throw new \Exception('Invalid Date');
			}
			
			return date($formats[$format], $timestamp);
	}
	
	
	public static function from_db($db_time) {
		$d = date_parse_from_format(static::$formats['db'], $db_time);
		return mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']);
	}
	
}
?>
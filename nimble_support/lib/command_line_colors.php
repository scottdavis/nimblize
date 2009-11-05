<?php
	
class CommandLineColor {
		
			private static $colors = array(
		    "black" => 0,
		    "red" => 1,
		    "green" => 2,
		    "yellow" => 3,
		    "blue" => 4,
		    "magenta" => 5,
		    "cyan" => 6,
		    "white" => 7
				);
 
			private static $formats = array(
    		"bold" => 1,
    		"underline" => 4
  			);
 
			private static $seperator = "\033";
 

			// self::blue_on_white
			public static function __callStatic($method, $args) {
				if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {return $args[0];}
				if(empty($args[0])) {return "";}
				$string = $args[0];
				$has_on = (strpos('_on_', $method) !== false) ? true : false; 
				$split = explode("_", $method);
				$formats = array();
				$forground_color = 7;
				$background_color = 0;	
				if($has_on) {
					$s = strstr($method, 'on');
					$s = explode('_', $s);
					$color = $s[1];
					if(isset(static::$colors[$color])) {
						$background_color = static::$colors[$color];
					}
				}
				$method = strtolower($method);
				if(strpos('on', $method) !== false) {
					
				}
				foreach($split as $var) {
					if(isset(static::$formats[$var])) {
						$formats[] = static::$formats[$var];
					}
					if(isset(static::$colors[$var]) && $background_color != static::$colors[$var]) {
						$forground_color = static::$colors[$var];
					}
				}
 
				return static::prepare_string($string, $forground_color, $background_color, $formats);
 
			}
			
 
			private static function prepare_string($string, $forground_color, $background_color, $formats) {
				$out = array(
											static::prepare_forground_color($forground_color),
											static::prepare_background_color($background_color),
											static::prepare_formatting($formats),
											$string, static::$seperator . "[0m"
										);
				return implode("", $out);
			}
 
			private static function prepare_forground_color($color) {
				return static::handle_color(3, $color);
			}
 
			private static function prepare_background_color($color) {
				return static::handle_color(4, $color);
			}
 
			private static function handle_color($lead, $color) {
				if(empty($color)) {return "";}
				return static::$seperator . "[{$lead}{$color}m";
			}
 
			private static function prepare_formatting($formats) {
				$out = array();
				foreach($formats as $format) {
					$out[] = static::$seperator . "[{$format}m";
				}
				return implode("", $out);
			}
 
	}


?>
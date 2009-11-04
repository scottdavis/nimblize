<?php
	
	class CommandlineColors {
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

			// self::blue_on_white
			public static function __callStatic($method, $args) {
				$method = strtolower($method);
				if(strpos('on', $method) !== false) {
					
				}
			}
	}

		

?>
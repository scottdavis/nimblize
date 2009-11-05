<?php
	/**
	* @package Support
	*/
	class FileUtils {
		
		/**
		 * Creates paths to a file that is OS generic.
		 * @uses FileUtils::join('root', 'sub', 'nimble.txt')
		 * @param string,...|array $args The path to create. If the first parameter is an array, use that array's elements to create the path.
		 * @return string The joined directory string.
		 */
		public static function join() {
			$args = func_get_args();
			if (is_array($args[0])) { $args = $args[0]; }
			return implode(DIRECTORY_SEPARATOR, $args);
		}
	  
		/**
		* Recursively creates directories
		* @uses FileUtils::mydir_p('myapp', 'controller') 
		* @param string $path - Path to create directory
		* @param integer $mode - Mode in which to create directories
		*/
		public static function mkdir_p($path, $mode=0775) {
			if(!is_dir($path)) {
				mkdir($path, $mode, true);
			}
		}
	
	}

?>
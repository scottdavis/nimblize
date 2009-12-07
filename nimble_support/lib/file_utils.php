<?php
	/**
	* @package Support
	*/
	class FileUtils {
		
		
		
		public static function __callStatic($method, $arguments) {
	    $internal_method = "_${method}";
	    if (method_exists("FileUtils", $internal_method)) {
	      $last_argument = end($arguments);
	      $use_cache = ($last_argument !== false);
	      $cache_key = false;

	      if ($use_cache) {
	        $cache = Cache::get_cache();
					$args = array();
					//this is for the stupid file objects that can't be serialized seriously php needs to add _serialize to it
					foreach($arguments as $arg) {
						$args[] = (string) $arg;
					}
	        $cache_key = 'flieutils-' . $method . '-' . md5(serialize($args));
	        if ($cache->exists($cache_key)) {
	          return $cache->get($cache_key);
	        }
	      }

	      $result = call_user_func_array(array("FileUtils", $internal_method), $arguments);
	      if ($use_cache) {
	        $cache->set($cache_key, $result);        
	      }

	      return $result;
	    }
	  }	
		
		/**
		 * Creates paths to a file that is OS generic.
		 * @uses FileUtils::join('root', 'sub', 'nimble.txt')
		 * @param string,...|array $args The path to create. If the first parameter is an array, use that array's elements to create the path.
		 * @return string The joined directory string.
		 */
		public static function _join() {
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
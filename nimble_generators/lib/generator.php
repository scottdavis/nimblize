<?php
	require_once(dirname(__FILE__) . '/../../nimble_support/lib/file_utils.php');
	require_once(dirname(__FILE__) . '/../../nimble_support/lib/inflector.php');

	/**
	 * Class for generating the components of a Nimble skeleton.
	 * @package Generators
	 */
	class Generator {
		public static $template_path, $script_path, $nimble_root;
		
		/**
		 * Handle generating templates magically.
		 */
		public static function __callStatic($method, $arguments) {
			if (strpos($method, 'generate_') === 0) {
			  $template_path = FileUtils::join(static::$template_path, str_replace('generate_', '', $method) . '.tmpl');
			  if (file_exists($template_path)) {			  	
			  	if (isset($arguments[0])) {
  					copy($template_path, $arguments[0]);
			  	}
			  }
			}	
		}

		/**
		 * Creates the database config files for a specified environment.
		 * @param string $path The path where the config file should be generated.
		 * @param string $env The name of the environment to generate.
		 */
		public static function generate_database_config($path, $env) {
			$string = str_replace('[env]', $env, file_get_contents(FileUtils::join(static::$template_path, 'database.json')));
			static::write_file($path, $string);
		}
	
		/**
		 * Copy global user scripts into the target script directory.
		 * @param string $path The target path for scripts. Will not be generated if it doesn't exist.
		 */
		public static function generate_scripts($path) {
			if (!is_dir($path)) { throw new Exception("Target directory does not exist: ${path}"); }
			
			$ignore = array('nimblize');
			if ($dir = opendir(static::$script_path)){
				while (($file = readdir($dir)) !== false) {
					if (in_array($file, $ignore)) { continue; }
					$source_file = FileUtils::join(static::$script_path, $file);
					if (is_file($source_file)) {
						$target_file = FileUtils::join($path, $file);
						copy($source_file, $target_file);
						@chmod($target_file, 0775);
					}
				}
				closedir($dir);
			} else {
			  throw new Exception("Source directory cannot be read: " . static::$script_path);	
			}
		}

		/**
		 * Generate a test case.
		 * @param string $type The type of test to generate.
		 * @param string $name The class name for the generated test.
		 */
		public static function generate_test($type, $name) {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(static::$nimble_root, 'test', $type);
			
			if (is_dir($path)) {
  			$test_path = 'nimblize/nimble_test/lib/phpunit_testcase.php';
				if (($test_case_code = file_get_contents(FileUtils::join(static::$template_path, "${type}_test.tmpl"))) !== false) {
					$test_case_code = str_replace(
						array('{class_name}', '{test_path}'),
						array($class_name, $test_path),
						$test_case_code
					);
				} else {
					throw new Exception("Test source file for ${type} not found!");	
				}
				
				FileUtils::mkdir_p($path);
				$file_path = FileUtils::join($path, $class_name . 'Test.php');
				static::write_file($file_path, $test_case_code);				
			} else {
				throw new Exception("Test directory for ${type} not found!");	
			}
		}

		/**
			* Creates a model class with option of a parent to extend
			* @param string $name the name of the class
			*/
		public static function generate_model($name) {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(static::$nimble_root, 'app', 'model', Inflector::underscore($class_name) . '.php');
			$string = file_get_contents(FileUtils::join(static::$template_path, 'model.tmpl'));	
			$string = str_replace('{class_name}', $class_name, $string);
			static::write_file($path, $string);
			static::generate_test('unit', $name);
		}

		/**
			* Creates a controller and its associated views ex. add, create, update, show, index, delete
			* @todo need to add name space support
			* @param string $name - suffix you want the name the controller
			*/
		public static function generate_controller($name, $views = true) {
			$class_name = Inflector::classify($name);
			$path_name = FileUtils::join(static::$nimble_root, 'app', 'controller', Inflector::underscore($class_name) . '_controller.php');
			$view_path = FileUtils::join(static::$nimble_root, 'app', 'view', strtolower(Inflector::underscore($class_name)));
			if ($views) {
				FileUtils::mkdir_p($view_path);
				$methods = static::create_view_functions($view_path);
				$type = "ApplicationController";
			} else {
				$methods = '';
				$type = "Controller";
			}
			$string = file_get_contents(FileUtils::join(static::$template_path, 'controller.tmpl'));
			$string = str_replace(array('{class_name}', '{template_path}', '{methods}', '{type}'), array($class_name, $view_path, $methods, $type), $string);
			static::write_file($path_name, $string);
			static::generate_test('functional', $name);
		}

		/**
		 * @access private
 		 * @return string The methods for the view.
		 * @see function controller
		 * @param string $view_path - folder in which to create files
		 */
		private static function create_view_functions($view_path) {
			$out = '';
			foreach(array('index', 'add') as $view) {
				self::view(FileUtils::join($view_path, $view . '.php'));
				$out .= self::view_function($view);
			}
	
			foreach(array('create') as $view){
				$out .= self::view_function($view);
			}
	
	
			foreach(array('update', 'delete') as $view) {
				$out .= self::view_function($view, true);
			}
	
	
			foreach(array('show', 'edit') as $view) {
				self::view(FileUtils::join($view_path, $view . '.php'));
				$out .= self::view_function($view, true);
			}
			return $out;
		}
	
		/**
		 * @param string $action - name of the action
		 * @param boolean $id - wither created function takes an id or not
		 * @see function create_view_functions
		 * @return string 
		 */
		public static function view_function($action, $id = false) {
      $out = array();

      $out[] = '  /**';
      $out[] = "   * ${action}";
      if ($id) {
        $out[] = "   * @param \$id string The unique identifier for this object.";
      }
      $out[] = "   */";
      if ($id) {
        $out[] = "  public function {$action}(\$id) {";
      } else {
        $out[] = "  public function {$action}() {";
      }
      $out[] = '';
      $out[] = '}';
      $out[] = '';

      return implode("\n", $out);
    }

		/**
		 * Creates a view template file
		 * @see function create_view_functions
		 * @param string $path The path to touch the file.
		 */
		private static function view($path) {
			@touch($path);
		}
		
		public static function update($dir) {
			//update scripts
			self::generate_scripts(FileUtils::join($dir));
			//update boot.php
			self::generate_boot(FileUtils::join($dir, '..', 'config', 'boot.php'));
		}
		
		/**
		 * Create templates for mailers.
		 * @param string $name The name of the mailer.
		 * @param array $methods The methods to generate.
		 */
		public static function generate_mailer($name, $methods = array()) {
			$class_name = Inflector::classify($name);
			$out = file_get_contents(FileUtils::join(static::$template_path, 'mailer.tmpl'));
			$template_path = FileUtils::join(static::$nimble_root, 'app', 'view', strtolower(Inflector::underscore($class_name)));
			
			$method_output = '';
			foreach ($methods as $method) {
				$method_output .= self::mailer_method($method);
				self::mailer_template($template_path, $method);
			}
			
			$out = str_replace(array('{class_name}', '{template_path}', '{methods}'), array($class_name, $template_path, $method_output), $out);
			$path_name = FileUtils::join(static::$nimble_root, 'app', 'model', $class_name . '.php');
			static::write_file($path_name, $out);
		}
		
		private static function mailer_method($name) {
			$out = "\n";
			$out .= "   public function " . $name . '($to)' . " {\n";
			$out .= '	  	$this->recipiants = $to;' . "\n";
			$out .= '	  	$this->from = \'\';' . "\n";
			$out .= '	  	$this->subject = \'\';' . "\n";
			$out .= "	  }\n";
			return $out;
		}

    /**
     * Generate a set of mailer templates, one PHP file and one text file.
     * @access private
     */
		private static function mailer_template($path, $method) {
      FileUtils::mkdir_p($path);
			foreach (array('php', 'txt') as $type) {
  			@touch(FileUtils::join($path, strtolower($method) . '.' . $type));
			}
		}
		
		/**
		 * Generate a migration.
		 * @param string $name The name of the migration.
		 * @param string $table The table to create a migration for.
		 */
		public static function generate_migration($name, $table = '') {
			$path = FileUtils::join(static::$nimble_root, 'db', 'migrations');
			FileUtils::mkdir_p($path);
			
			$file_name = time() . '_' . $name . '_migration.php';
			$class_name = Inflector::classify($name . 'Migration');
									
			$out = file_get_contents(FileUtils::join(static::$template_path, 'migration.tmpl'));
			$up = '';
			$down = '';
			if(!empty($table)) {
				$up .= '$t = $this->create_table("' . $table . '");';
				$up .= "\n" . '				//enter column definitions here';
				$up .= "\n" . '			$t->go();';
				$down .= '	$this->drop_table("' . $table . '");';				
			}
			$out = str_replace(
			  array('{class_name}', '{up_code}', '{down_code}'), 
			  array($class_name, $up, $down), 
			  $out
			);
			
			static::write_file(FileUtils::join($path, $file_name), $out);
		}
		
		/**
		 * Write a file to the filesystem.
		 */
		private static function write_file($path, $string) {
		  file_put_contents($path, $string);
		}

    /**
     * Retrieve the help documentation.
     */
		public static function help() {
			return file_get_contents(FileUtils::join(static::$template_path, 'help.tmpl'));
		}
	}

  foreach (array(
  	'template_path' => array(dirname(__FILE__), '..', 'templates'),
  	'script_path' => array(dirname(__FILE__), '..', '..', 'nimble_scripts'),
  	'nimble_root' => NIMBLE_ROOT
  ) as $name => $path) {
  	Generator::${$name} = FileUtils::join($path);
  }
  
?>
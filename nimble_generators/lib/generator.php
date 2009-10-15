<?php

	/**
	* This File contains all the logic for creating the nimble MVC skeleton
	*/


	require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
	require_once(dirname(__FILE__) . '/../../lib/support/file_utils.php');
	$folder = dirname(__FILE__);

	define('TEMPLATE_PATH', FileUtils::join($folder, '..', 'templates'));
	define('SCRIPT_PATH', FileUtils::join($folder, '..', '..', 'bin'));

	/**
	* @package Generators
	*/
	class Generator {

		/**
		* Creates the database config files for a specified enviromant
		* @param string $path - Path to creat file
		* @param string $env - Enviroment name
		*/
		public static function database_config($path, $env) {
			$db = fopen($path, "w");
			fwrite($db, preg_replace('/\[env\]/', $env, file_get_contents(TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'database.json')));
			fclose($db);
		}
	
		/**
		* Creates a boot.php file
		* @param string $path - path you want to place the boot file
		*/
		public static function boot($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'boot.php.tmpl'), $path);
		}
	
		/**
		* Creates a .htaccess file
		* @param string $path - path you want the htaccess stored
		*/
		public static function htaccess($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'htaccess.tmpl'), $path);
		}

		/**
		* Creates the script directory and copies everything from bin except nimblize
		* @param string $path - path you want the script folder located
		*/
		public static function scripts($path) {
			$ignore = array('nimblize', '.', '..');
			if($dir = opendir(SCRIPT_PATH)){
				while (($file = readdir($dir)) !== false) {
					if(array_include($file, $ignore)) {
						continue;
					}
					copy(FileUtils::join(SCRIPT_PATH, $file), FileUtils::join($path, $file));
				}
			}
		}

		/**
		* Creates an empty route file
		* @param string $path
		*/
		public static function route($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'route.tmpl'), $path);
		}
	
		/**
		* Create a r404 class file 
		* @param string $path - path you want file created
		*/
		public static function r404($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'r404.tmpl'), $path);
		}
		/**
			* Creates a Nimble Unit Test Case
			* @param string $name name of test
			*/
				
		public static function unit_test($name) {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(NIMBLE_ROOT, 'test', 'unit');
			$test_path = 'nimblize/nimble_test/lib/unit_test.php';
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'unit_test.tmpl'));
			
			FileUtils::mkdir_p($path);
			$file = fopen(FileUtils::join($path, $class_name . 'Test.php'), "w");
			fwrite($file, $string);
			fclose($path);
		}
		
		/**
			* Creates a Nimble Unit Test Case
			* @param string $name name of test
			*/
				
		public static function functional_test($name) {
			$class_name = Inflector::classify($name);
			$test_path = 'nimblize/nimble_test/lib/functional_test.php';
			$path = FileUtils::join(NIMBLE_ROOT, 'test', 'functional');
			$file_path = FileUtils::join($path, $class_name . 'ControllerTest.php');
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'functional_test.tmpl'));
			if(!is_dir($path)) {
				FileUtils::mkdir_p($path);
			}
			$string = str_replace(array('{class_name}', ' {test_path}'), array($class_name, $test_path));
			static::write_file($file_path, $string);
		}
		
		/**
			* Creates a model class with option of a parent to extend
			* @param string $name the name of the class
			*
			*/
		public static function model($name) {
			$class_name = Inflector::classify($name);
			$path = FileUtils::join(NIMBLE_ROOT, 'app', 'model', $class_name . '.php');
			$string = file_get_contents(FileUtils::join(TEMPLATE_ROOT, 'model.tmpl'));	
			$string = str_replace('{class_name}', $class_name, $string);
			static::write_file($path, $string);
		}

		/**
			* Creates a controller and its associated views ex. add, create, update, show, index, delete
			* @todo need to add name space support
			* @param string $name - suffix you want the name the controller
			*/
		public static function controller($name) {
			$class_name = Inflector::classify($name);
			$path_name = FileUtils::join(NIMBLE_ROOT, 'app', 'controller', $class_name . 'Controller.php');
			$view_path = FileUtils::join(NIMBLE_ROOT, 'app', 'view', strtolower(Inflector::underscore($class_name)));
			FileUtils::mkdir_p($view_path);
			$methods = static::create_view_functions($view_path);
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'controller.tmpl'));
			$string = str_replace(array('{class_name}', '{template_path}', '{methods}'), array($class_name, $view_path, $methods));
			static::write_file($path, $string);
		}

		/**
		* @access private
		* @return string
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
		* @access private
		* @param string $action - name of the action
		* @param boolean $id - wither created function takes an id or not
		* @see function create_view_functions
		* @return string 
		*/
		private static function view_function($action, $id=false) {
			$out = "	/**\n";
			$out .= "	* " . $action . "\n";
			if($id) {
				$out .= "	* @param " . '$id' . " string\n";
				$out .= "	*/\n";
				$out .= "    public function " . $action . '($id)' . " {\n";
			}else{
				$out .= "	*/\n";
				$out .= "    public function " . $action . "() {\n";
			}
			$out .= "    }\n";
			$out .= "\n";
			return $out;
		}
	
		/**
		* Creates a view template file
		* @see function create_view_functions
		* @param string $path - path to touch the file
		*/
		private static function view($path) {
			touch($path);
		}
		
		
		public static function help() {
			return file_get_contents(FileUtils::join(TEMPLATE_PATH, 'help.tmpl'));
		}
		
		
		public static function update($dir) {
			//update scripts
			self::scripts(FileUtils::join($dir));
			//update boot.php
			self::boot(FileUtils::join($dir, '..', 'config', 'boot.php'));
		}
		
		public static function mailer($name, $methods) {
			$class_name = Inflector::classify($name);
			$out = file_get_contents(FileUtils::join(TEMPLATE_APTH, 'mailer.tmpl'));
			$methods = '';
			$template_path = FileUtils::join(NIMBLE_ROOT, 'app', 'view', strtolower(Inflector::underscore($class)));
			foreach($methods as $method) {
				$methods .= self::mailer_method($method);
				self::mailer_template($path, $method);
			}
			$out = str_replace(array('name', 'template_path', 'methods'), array($class_name, $template_path, $methods), $out);
			$path_name = FileUtils::join(NIMBLE_ROOT, 'app', 'model', $class_name . '.php');
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
		
		private static function mailer_template($path, $method) {
			FileUtils::mkdir_p($path);
			touch(FileUtils::join($path, strtolower($method) . '.php'));
			touch(FileUtils::join($path, strtolower($method) . '.txt'));
		}
		
		
		
		public static function migration($name, $path, $table='') {
			$class_name = INflector::classify($name);
			$out = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'migration.tmpl'));
			$up = '';
			$down = '';
			if(!empty($table)) {
				$up .= '			$this->create_table("' . $table . '")';
				$up .= '				//enter column definitions here';
				$up .= '			$this->go()';
				$down .= '			$this->drop_table("' . $table . '")';				
			}
			$out = str_replace(array('{name}', '{up}', '{down}'), array($class_name, $up, $down))
			static::write_file($path, $out);
			
			
		}


		private static function write_file($path, $string) {
			file_put_contents($path, $string);
		}

	}

	?>
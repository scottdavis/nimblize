<?php

	/**
	* This File contains all the logic for creating the nimble MVC skeleton
	*/


	require_once(dirname(__FILE__) . '/../../nimble_support/lib/file_utils.php');
	$folder = dirname(__FILE__);

	define('TEMPLATE_PATH', FileUtils::join($folder, '..', 'templates'));
	define('SCRIPT_PATH', FileUtils::join($folder, '..', '..', 'nimble_scripts'));

	/**
	* @package Generators
	*/
	class Generator {
		
		
		public static function generate_blank_php_file($path) {
			copy(FileUtils::join(TEMPLATE_PATH, 'empty_php.tmpl'), $path);
		}
		
		/**
		* Creates the database config files for a specified enviromant
		* @param string $path - Path to creat file
		* @param string $env - Enviroment name
		*/
		public static function database_config($path, $env) {
			$string = str_replace('[env]', $env, file_get_contents(FileUtils::join(TEMPLATE_PATH, 'database.json')));
			static::write_file($path, $string);
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
					chmod(FileUtils::join($path, $file), 0775);
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
			$test_path = 'nimblize/nimble_test/lib/phpunit_testcase.php';
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'unit_test.tmpl'));
			$string = str_replace(array('{class_name}', '{test_path}'), array($class_name, $test_path), $string);
			FileUtils::mkdir_p($path);
			$file_path = FileUtils::join($path, $class_name . 'Test.php');
			static::write_file($file_path, $string);
		}
		
		/**
			* Creates a Nimble Unit Test Case
			* @param string $name name of test
			*/
				
		public static function functional_test($name) {
			$class_name = Inflector::classify($name);
			$test_path = 'nimblize/nimble_test/lib/phpunit_testcase.php';
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
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'model.tmpl'));	
			$string = str_replace('{class_name}', $class_name, $string);
			static::write_file($path, $string);
		}

		/**
			* Creates a controller and its associated views ex. add, create, update, show, index, delete
			* @todo need to add name space support
			* @param string $name - suffix you want the name the controller
			*/
		public static function controller($name, $views=true) {
			$class_name = Inflector::classify($name);
			$path_name = FileUtils::join(NIMBLE_ROOT, 'app', 'controller', Inflector::underscore($class_name) . '_controller.php');
			$view_path = FileUtils::join(NIMBLE_ROOT, 'app', 'view', strtolower(Inflector::underscore($class_name)));
			if($views) {
				FileUtils::mkdir_p($view_path);
				$methods = static::create_view_functions($view_path);
				$type = "ApplicationController";
			}else{
				$methods = '';
				$type = "Controller";
			}
			$string = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'controller.tmpl'));
			$string = str_replace(array('{class_name}', '{template_path}', '{methods}', '{type}'), array($class_name, $view_path, $methods, $type), $string);
			static::write_file($path_name, $string);
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
		
		
		
		public static function migration($name, $table='') {
			$path = FileUtils::join(NIMBLE_ROOT, 'db', 'migrations');
			FileUtils::mkdir_p($path);
			$file_name = time() . '_' . $name . '.php';
			$class_name = Inflector::classify($name);
			$out = file_get_contents(FileUtils::join(TEMPLATE_PATH, 'migration.tmpl'));
			$up = '';
			$down = '';
			if(!empty($table)) {
				$up .= '$t = $this->create_table("' . $table . '");';
				$up .= "\n" . '				//enter column definitions here';
				$up .= "\n" . '			$t->go();';
				$down .= '	$this->drop_table("' . $table . '");';				
			}
			$out = str_replace(array('{name}', '{up_code}', '{down_code}'), array($class_name, $up, $down), $out);
			static::write_file(FileUtils::join($path, $file_name), $out);
			
			
		}


		private static function write_file($path, $string) {
			file_put_contents($path, $string);
		}

	}

	?>
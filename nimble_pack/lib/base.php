<?php
require_once(dirname(__FILE__) . '/exception.php');
require_once(dirname(__FILE__) . '/controller.php');
require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/route.php');
require_once(dirname(__FILE__) . '/route/url_builder.php');



/**
 * Nimble is the base class in the application.
 * This class provides methods to add & call routes, parse URLs, and load plugins.
 * @package NimblePack
 */
class Nimble
{
	var $routes = array();
	var $routes_by_short_name = array();
	var $config = array();
	var $plugins = array();
	var $test_mode = false;
	static private $instance = NULL;

	function __construct()
	{
		if(defined("NIMBLE_IS_TESTING") && NIMBLE_IS_TESTING) {
			$this->test_mode = true;
		}
		$this->url = (!empty($_GET['__url'])) ? '/' . trim($_GET['__url'], '/') : '';
		/** set default configs */
		$this->config['title_seperator'] = ':';
		$this->config['default_layout'] = '';
		$this->config['uri'] = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
		$this->page_title = '';
		if(!$this->test_mode) {
			if(isset($_SESSION) && !isset($_SESSION['flashes'])) {
				$_SESSION['flashes'] = array();
			}
		}
	}

	/**
	 * Returns the uri defined by nimble config 'uri'
	 */
	public static function uri() {
		return self::getInstance()->config['uri'];
	}

	/**
	 * Get the global Nimble object instance.
	 * @return Nimble The global Nimble reference.
	 */
	public static function getInstance()
	{
		if(self::$instance == NULL) {
			self::$instance = new Nimble();
		}
		return self::$instance;
	}

	/**
	 * Add a URL and associated controller class, method, and HTTP method to the routing table.
	 * @param string $url The URL pattern for this rule.
	 * @param string $this->klass The name of the controller class to instantiate when this rule matches.
	 * @param string $this->klass_method The name of the method on the instantiated controller class.
	 * @param string $http_method The HTTP method this request responds to.
	 */
	public function add_url($rule, $klass, $klass_method, $http_method = 'GET')
	{
		$args = func_get_args();
		$rule = $klass = $klass_method = $short_url = null;

		if (count($args) >= 3) {
			foreach (array('rule', 'klass', 'klass_method', 'http_method', 'short_url') as $field) {
				if (count($args) > 0) {	${$field} = array_shift($args); }
			}
		}

		if (!empty($short_url)) {
			if (isset($this->routes_by_short_name[$short_url])) {
				$original_route = $this->routes[$this->routes_by_short_name[$short_url]];
				throw new NimbleException(sprintf("Short URL %s already defined by route %s::%s", $short_url, $original_route->controller, $original_route->method));
			}
		}

		// parse format
		$has_format = false;
		if (preg_match('/\.[a-z]+$/', $this->url)) {
			$rule .= '\.(?P<format>[a-zA-Z0-9]+)';
			$has_format = true;
		}
		$rule = preg_replace('/:([a-zA-Z0-9_]+)(?!:)/', '(?P<\1>[\sa-zA-Z0-9_-]+)', $rule);

		$route_info = array(
			'rule' => '/^' . str_replace('/','\/',$rule) . '$/',
			'controller' => $klass,
		  'method' => $klass_method,
			'http_method' => $http_method,
			'has_format' => $has_format,
			'short_url' => $short_url
		);

		$id = count($this->routes);

		$this->routes[$id] = (object)$route_info;

		if (!empty($short_url)) {	$this->routes_by_short_name[$short_url] = $id;	}

		return $id;
	}

	/**
	 * Remove the specified route by ID.
	 * @param int $id The route to remove.
	 * @return boolean True if removed, false if not.
	 */
	public function remove_url_by_id($id) {
		if (isset($this->routes[$id])) {
			unset($this->routes[$id]);
			if (($index = array_search($id, $this->routes_by_short_name)) !== false) {
				unset($this->routes_by_short_name[$index]);
			}
			return true;
		}
		return false;
	}

	public function get_route_info_by_short_name($short_name) {
		if (!isset($this->routes_by_short_name[$short_name])) {
			throw new NimbleException(sprintf("Short name %s not defined", $short_name));
		}
		return $this->routes[$this->routes_by_short_name[$short_name]];
	}

	/**
	 * Match the HTTP request's URL and HTTP method against the stored routes and, if a match is found, call the appropriate controller's method.
	 * If the client you're using doesn't support sending HTTP requests with methods
	 * other than GET or POST, set $_POST['_method'] to the actual HTTP method you wish to use.
	 */
	public function dispatch()
	{
		if(isset($_SERVER['QUERY_STRING'])) {
			$string = explode('&', $_SERVER['QUERY_STRING']);
			unset($string[0]);
			$_SERVER['QUERY_STRING'] = implode('&', $string);
		}
		if($this->test_mode && !isset($_POST['_method'])) {
			$_POST['_method'] = 'get';
		}
		$this->load_plugins();
		foreach($this->routes as $conf) {
			// if a vaild _method is passed in a post set it to the REQUEST_METHOD so that we can route for DELETE and PUT methods
			if(isset($_POST['_method']) && !empty($_POST['_method']) && in_array(strtoupper($_POST['_method']), Route::$allowed_methods)){
				$_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
			}
			/** test to see if its a valid route */
			if (preg_match($conf->rule, $this->url, $matches) && $_SERVER['REQUEST_METHOD'] == $conf->http_method){
				/** Only declared variables in URL regex */
				$matches = $this->parse_urls_args($matches);
				$_GET = array_merge($_GET, $matches);
				$this->klass = new $conf->controller();
				/** set the layout tempalte to the default */
				$this->klass->set_layout_template();
				$this->klass->format = ($conf->has_format) ? array_pop($matches) : 'html';
				ob_start();

				// call before filters
				call_user_func(array($this->klass, "run_before_filters"), $conf->method);

				// call methods on controller class
				call_user_func_array(array($this->klass , $conf->method), $matches);

				if(!$this->klass->has_rendered && isset($this->config['view_path'])) {
					/**
					 * Add inflector for this type of code from now on
					 */
					$dir = str_replace('Controller', '', $conf->controller);
					$dir = str_replace('\\', '/', $dir);
					$dir = strtolower(Inflector::underscore($dir));
					$__format = '.php';
					if($this->klass->format != 'html') {
					  $__format = '.' . $this->klass->format;
					}
					$view = FileUtils::join($dir, $conf->method . $__format);
					$this->klass->render($view);
				}

				// call after filters
				call_user_func(array($this->klass, "run_after_filters"), $conf->method);

				$out = ob_get_clean();
				if (count($this->klass->headers)>0){
					foreach($this->klass->headers as $header){
						if(!$this->test_mode) {
							header($header[0], true, empty($header[1]) ? null : $header[1]);
						}
					}
				}
				echo trim($out);
				if(!$this->test_mode){
					exit();
				}
			}
		}
		if(empty($_SERVER['REQUEST_METHOD']) && !$this->test_mode){
			throw new NimbleException('No Request Method');
		}
		if(!$this->test_mode){
			if(NIMBLE_ENV == 'development') {
				var_dump($this->routes);
				throw new NimbleException('Path not found: ' . $_GET['__url']);
			}
			call_user_func(array('r404' , $_SERVER['REQUEST_METHOD']));
		}
	}

	/**
	 * Parse an array of URL parts from preg_match for allowed matches.
	 * Nimble uses named subpatterns to identify parts of the URL to pull.
	 * This function filters out any non-named subpatterns (indexed by a number)
	 * and returns only matches that are indexed by a string.
	 * @param array $matches The list of URL matches from preg_match.
	 * @return array The list of valid named subpattern matches.
	 */
	private function parse_urls_args($matches)
	{
		$first = array_shift($matches);
		$new_matches = array();
		foreach($matches as $k=>$match){
			if (is_string($k)){
				$new_matches[$k]=$match;
			}
		}
		return $new_matches;
	}

	/**
	 * Add a list of plugins to be loaded when the Nimble object is instantiated.
	 * This method can be called either globally or at the controller level.
	 * If called at the controller level, any views the controller calls also
	 * have access to the plugin's code.
	 *
	 * Each plugin is stored in a separate directory within the directory specified
	 * by $config['plugins_path']. An init.php file in the plugin's directory
	 * loads the rest of the plugin's code at runtime.
	 * @param string,... The list of plugins to be loaded.
	 */
	public static function plugins()
	{
		$args = func_get_args();
		if(count($args) == 0) {return false;}
		$klass = self::getInstance();
		$klass->plugins = $args;
		//$klass->load_plugins();
	}

	/**
	 * Load the requested plugins before the rest of the code executes.
	 */
	public function load_plugins()
	{
		if(count($this->plugins) == 0) {return false;}
		self::require_plugins($this->plugins);
	}

	/**
	 * Require the requested plugins' init.php files into the program.
	 * @param string $array The list of plugin directories.
	 */
	public static function require_plugins($array)
	{
		$klass = self::getInstance();
		foreach($array as $plugin) {
			if(array_key_exists('plugins_path', $klass->config)) {
		  $file = FileUtils::join($klass->config['plugins_path'], $plugin, 'init.php');
		  if(file_exists($file)) {
		  	require_once($file);
		  	continue;
		  }
			}
			$file = FileUtils::join(dirname(__FILE__), '..', 'plugins', $plugin, 'init.php');
			require_once($file);
		}
	}

	/**
	 * Set a configuration option.
	 * @param string $config The name of the configuration key to set.
	 * @param string $value The value for the configuration key.
	 */
	public static function set_config($config, $value)
	{
		$klass = self::getInstance();
		$klass->config[$config] = $value;
	}

	/**
	 * Set a page title
	 * @see function Nimble::get_title()
	 * @uses Nimble::set_title('My page title');
	 */

	public static function set_title($title) {
		$klass = self::getInstance();
		$klass->page_title = $title;
	}
	/**
	 * Get the page title
	 * @see function Nimble::set_title()
	 * @uses Nimble::get_title();
	 * @returns the page title set by set_title
	 */
	public static function get_title() {
		$klass = self::getInstance();
		$title = $klass->page_title;

		if(isset($klass->config['site_title'])) {
			if(empty($title)) {
				$title = $klass->config['site_title'];
			}else{
				$title = $klass->config['site_title'] .  $klass->config['title_seperator'] . $title;
			}
		}
		return $title;
	}

	/**
	 * Displays a flash message for the current key
	 * @param string $key
	 * @uses <?php echo Nimble::display_flash('notice') ?>
	 */
	public static function display_flash($key) {
		if(isset($_SESSION['flashes'][$key])) {
			$value = $_SESSION['flashes'][$key];
			unset($_SESSION['flashes'][$key]);
			return $value;
		}
	}

	/**
	 * Sets a flash message for the current key
	 * @param string $key
	 * @param string $value
	 */
	public static function flash($key, $value) {
		$_SESSION['flashes'][$key] = $value;
	}



}
<?php
	$dir = dirname(__FILE__);
	require_once($dir . '/../exception.php');
  /**
  * UrlBuilder constructs a usable URL from a Route pattern.
	* @package Route
  */
  class UrlBuilder {

      public static function root_path() {
        return Nimble::uri();
      }
  
      /**
       * Get the current UrlBuilder object.
       * @return UrlBuilder The current UrlBuilder.
       */
      public static function getInstance()
      {
          if(self::$instance == NULL) {
              self::$instance = new self();
          }
          return self::$instance;
      }

      /**
       * Cleans up the route pattern.
       * @param string $route The route pattern.
       * @return string The cleaned up route pattern. 
       */
      public static function clean_route($route)
      {
          return str_replace('$/', '', str_replace('/^', '', str_replace('\/','/', $route)));
      }

      /**
       * Get the current request's URI.
       * @return string The current request's URI.
       */
      public static function uri() {
          return preg_replace('/\/$/', '', Nimble::uri());
      }

      /**
       * Build a URL based on a route and a list of parameters that match the route's pattern's named parameters.
       * @param Route $route The route.to match.
       * @param array $params The array of params in the order they occur in the url.
       * @return string The constructed URL, or the original pattern if no match.
       * @throws NimbleException if the wrong number of parameters for the included pattern are provided.
       */
      public static function build_url($route, $params=array()) {
          $route_regex = '/\(\?P<[\w]+>[^\)]+\)/'; // matches (?P<foo>[a-zA-Z0-9]+)
          $pattern = self::clean_route($route->rule);
          if(!empty($params) && preg_match_all($route_regex, $pattern, $matches)){
              // test if we have the right number of params
              if (count($matches[0]) < count($params)) {
                  throw new NimbleException('Invalid Number of Params expected: ' . count($matches[0]) . ' Given: ' . count($params));
              }

              // replace the regular expression syntax with the params
              return str_replace('//', '/', self::uri() . preg_replace(array_fill(0, count($params), $route_regex), $params, $pattern, 1));
          }else{
              return self::uri() . $pattern;
          }
      }

      /**
       * Build a URL that points at the provided controller and action, with the provided params to match.
       * @param string $controller, $action, $params ...
	   * usage: url_for('controller', 'action')
       * @return string The constructed URL.
       * @throws NimbleException if neither the controller nor the action match.
       */
      public static function url_for()
      {	
			$args = func_get_args();
			$controller = array_shift($args);
			$action = array_shift($args);
			$params = $args;
	    $cache = Cache::get_cache();
      $cache_key = 'url_for-' . md5(serialize($args));
      if ($cache->exists($cache_key)) {
        return $cache->get($cache_key);
      }
          $klass = Nimble::getInstance();
          foreach($klass->routes as $route) {
              if(strtolower($route->controller) == strtolower($controller) && strtolower($route->method) == strtolower($action)) {
                  $result = self::build_url($route, $params);
                  $cache->set($cache_key, $result); 
                  return $result;
              }
          }
          throw new NimbleException('Invalid Controller / Method Pair');
      }

      /**
       * Dump out application routes to a human readable format.
       * @param boolean $cli True if being called from the command line.
       * @return string The application's routes in a human readable format.
       */
      public static function dumpRoutes($cli=false)
      {
          $klass = Nimble::getInstance();
          $out = array();
          foreach($klass->routes as $route) {
              $pattern = self::clean_route($route->rule);
              $pattern = empty($pattern) ? 'root path' : $pattern;
							$string = '';
							if(!empty($route->short_url)) {
								$string .= ' ' . CommandLineColor::underline('Short Url:') . ' ' . CommandLineColor::yellow($route->short_url) . ' ';
							}
							$string .= CommandLineColor::underline("Controller:") . ' ' . CommandLineColor::yellow($route->controller) . ' ' . 			
							CommandLineColor::underline('Action:') . ' ' . CommandLineColor::magenta($route->method) . ' ' . 
							CommandLineColor::underline('Method:') . ' ' . CommandLineColor::green($route->http_method) . ' ' . 
							CommandLineColor::underline('Pattern:') . ' ' . CommandLineColor::bold_red($pattern);
							array_push($out, $string);
          }  
     			$return = "\n";
          $return .= join("\n", $out);
          $return .= "\n";
          return $cli ? $return : htmlspecialchars($return);
      }
  }
  // Global functions

  /**
   * @global
   * Build a URL that points at the provided controller and action, with the provided params to match.
   * @uses url_for('controller', 'action', 'param1', 'params2', 'etc')
   * @return string The constructed URL.
   * @throws NimbleException if neither the controller nor the action match.
   */
  function url_for() {
      $args = func_get_args(); 
      return call_user_func_array(array('UrlBuilder','url_for'), $args);
  }
  
  function root_path() {
    return UrlBuilder::root_path();
  }
  
?>
<?php

	/**
 	* Rotes control how HTTP requests are handled by the application.
	* @package NimblePack
 	*/
class Route
{
    static $allowed_methods = array("GET", "POST", "PUT", "DELETE");
    var $pattern;
    var $controller;
    var $action;
    var $http_method = 'GET';
    var $http_format = '';
    private $bind_id;

    /**
     * Create a new Route and define the URI pattern match.
     * @param string $pattern The pattern to match.
     * @return Route The new Route object.
     */
    function __construct($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Add a controller to the route.
     * @param string $controller The controller to instantiate if the pattern matches.
     * @return Route The Route object.
     */
    function controller($controller)
    {
				if(preg_match('/(::|\x5c)/', $controller)) {				
	        $this->controller = static::parse_namespace($controller);
				}else{
					$this->controller = $controller;
				}
        return $this;
    }

		/**
		* Parses the namespace out of either admin/foo or Admin::Foo format and makes it callable
		* @param string $string
		* @access private
		*/
		private static function parse_namespace($string) {
			$a = preg_split('/(::|\x5c)/', $string);				
			$name = array_pop($a);	
			$ao = array();
			foreach($a as $_a) {$ao[] = strtolower($_a);}
			$string = implode('\\', $ao);
			return $string .'\\' . $name;
		}
		
    /**
     * Add a controller action to this route.
     * @param string $action The method on the controller to call if the pattern matches.
     * @return Route The Route object.
     */
    function action($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Add an HTTP method to this route, finishing the route.
     * @param string $http+_method The HTTP method to match against the request.
     * @return Route the Route object.
     */
    function on($http_method)
    {
        $this->http_method = strtoupper($http_method);
        $this->bind();
        return $this;
    }

    /**
     * Add a short URL to search for.
     */
    function short_url($short) {
    	$this->short_url = $short;
      $this->rebind();
    	return $this;
    }

    /**
     * Bind the Route object to Nimble's router.
     * @throws NimbleException if the requested HTTP method is invalid.
     */
    function bind()
    {
        if (array_include($this->http_method, self::$allowed_methods)){
        		$parameters = array();
        		foreach (array('pattern', 'controller', 'action', 'http_method', 'short_url') as $field) {
        			if (isset($this->{$field})) {
        				$parameters[] = $this->{$field};
        			} else {
        				break;
        			}
        		}
        		$this->bind_id = call_user_func_array(array(Nimble::getInstance(), 'add_url'), $parameters);
        } else {
            throw new NimbleException('Invalid Request');
        }
    }

    function rebind() {
    	if (isset($this->bind_id)) {
    		Nimble::getInstance()->remove_url_by_id($this->bind_id);
    		$this->bind();
    	}
    }

    /* build the default routes for a controller pass it the prefix ex. Form for FormController */
    /**
     * Build the common set of routes for the typical CRUD controller.
     * For example, if the $controller_prefix is set to "person", the
     * following Routes are constructed:
     *
     * persons/ calls PersonController::index if HTTP GET
     * persons/ calls PersonController:create if HTTP POST
		 * person/:id/edit PersonController::edit if HTTP GET
     * person/:id calls PersonController::update if HTTP PUT
     * person/:id calls PersonController::delete if HTTP DELETE
     * person/:id calls PersonController::show if HTTP GET
     * person/add calls PersonController::add if HTTP GET
     *
     * @param string $controller_prefix The controller class name's prefix.
     */
    public static function resources($controller_prefix)
    {

				// admin\Main
	
	
				$controller_word = 'controller';
				//does it already contain controller
				if(substr(strtolower($controller_prefix), -1 * strlen($controller_word)) != $controller_word) {
					$controller_prefix .=  ucwords($controller_word);
				}
				
				//  \MainController
				//	admin\MainController
				//  admin\foo\MainController
				$controller = Inflector::classify($controller_prefix);
				$url_prefix = '/' . strtolower(str_replace(ucwords($controller_word), '', str_replace('\\', '/', static::parse_namespace($controller_prefix))));
				$method = str_replace('/', '_', trim($url_prefix, '/'));
				$a = explode('/', $url_prefix);
				$end = array_pop($a);
				$url_prefix = implode('/', $a);
				$singular = implode('/', array($url_prefix, Inflector::singularize($end)));
				$plural = implode('/', array($url_prefix, Inflector::pluralize($end))); 				
        R($singular . '/add')->controller($controller)->action('add')->on('GET')->short_url($method . '_add');
        R($singular . '/:id/edit')->controller($controller)->action('edit')->on('GET')->short_url($method . '_edit');
				
        $actions = array('index' => 'GET', 'create' => 'POST');
        foreach($actions as $action => $_method) {
					$_action = empty($method) ? '' : $method . '_' . $action;
        	R($plural)->controller($controller)->action($action)->on($_method)->short_url($_action);
        }
        $actionss = array('update' => 'PUT', 'delete' => 'DELETE', 'show' => 'GET');
        foreach($actionss as $action => $_method) {
					$_action = empty($method) ? '' : $method . '_' . $action;
        	R($singular . '/:id')->controller($controller)->action($action)->on($_method)->short_url($_action);
        }

    }
}

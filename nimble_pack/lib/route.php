<?php

	/**
 	* Rotes control how HTTP requests are handled by the application.
	* @package Nimble
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
        $this->controller = $controller;
        return $this;
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
        if (in_array($this->http_method, self::$allowed_methods)){
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
     * person/:id calls PersonController::update if HTTP PUT
     * person/:id calls PersonController::delete if HTTP DELETE
     * person/:id calls PersonController::show if HTTP GET
     * person/add calls PersonController::add if HTTP GET
     *
     * @param string $controller_prefix The controller class name's prefix.
     */
    public static function resources($controller_prefix)
    {
        $controller = ucwords($controller_prefix) . 'Controller';
        $controller_prefix = strtolower($controller_prefix);
        $r = new Route($controller_prefix . '/add');
        $r->controller($controller)->action('add')->on('GET');
        $r = new Route($controller_prefix . '/:id/edit');
        $r->controller($controller)->action('edit')->on('GET');
        $actions = array('index' => 'GET', 'create' => 'POST');
        foreach($actions as $action=>$method) {
            $r = new Route(Inflector::pluralize($controller_prefix));
            $r->controller($controller)->action($action)->on($method);
        }
        $actionss = array('update' => 'PUT', 'delete' => 'DELETE', 'show' => 'GET');
        foreach($actionss as $action=>$method) {
            $r = new Route($controller_prefix . '/:id');
            $r->controller($controller)->action($action)->on($method);
        }

    }
}

<?php
	require_once(dirname(__FILE__) . '/exception.php');

	/**
 	* Controller handles user interaction with the site.
	* @package nimble_pack
	*/
class Controller {
		var $nimble;
    var $format;
    var $layout = true;
    var $layout_template;
    var $headers = array(array('Content-Type: text/html', 200));
		var $has_rendered = false;
		var $template = '';
		var $rendered_partials = array();
    /**
     * The expected output format for this controller.
     * @var string
     */
    var $http_format = 'html';
	/**
	*
	* @return instance of the Nimble class
	*/
	public function nimble() {
		return Nimble::getInstance();
	}
	/**
	* @param string path to template you want to use as layout
	* @return string path to the application.php template
	*/
	public function set_layout_template($template ='') {
		if(!empty($template) && file_exists($template)) {
			$this->layout_template = $template;
		}else{
			$this->layout_template = $this->nimble()->config['default_layout'];
		}
		return $this->layout_template;
	}
	
	
    /**
     * Load a plugin for this controller and its rendered view.
     * @param string,... $plugins The plugins to load.
     */
    public function load_plugins() {
        $args = func_get_args();
        if(count($args) == 0) { return false; }
        Nimble::require_plugins($args);
    }

    /**
     * Run filters before the controller's action is invoked.
     * @param string $method The controller action that is being invoked.
     */
    public function run_before_filters($method) {
			$this->run_filters('before', $method);
    }

    /**
     * Run filters after the controller's action is invoked.
     * @param string $method The controller action that is being invoked.
     */
    public function run_after_filters($method) {
       $this->run_filters('after', $method);
    }

		/**
			* Runs filters given the type and method to match
			* @param string $type (before | after)
			* @param string $method The method the controller if trying to invoke
			*/
 		private function run_filters($type, $method) {
			$_methods = get_class_methods($this);
			foreach($_methods as $_method) {
				$matches = array();
				$regex = "/^{$type}_filter($|_(for|except)_([0-9a-z_]+)$)/";
				if(preg_match($regex, $_method, $matches)) {
					if(isset($matches[2])) {
						$hash = array_flip(explode('_and_', $matches[3]));
						switch($matches[2]) {
							case 'for':
								if(isset($hash[$method])) {
									call_user_func(array($this, $_method));
								}
							break;
							case 'except':
								if(!isset($hash[$method])) {
									call_user_func(array($this, $_method));
								}
							break;
							default:
								return;
							break;
						}
					}else{
						call_user_func(array($this, $_method));
					}
				}
			}
		}

    /**
     * Return the current format.
     * @return string The current format.
     */
    public function format() { return $this->format; }

    /**
     * Include a PHP file, inject the controller's properties into that file, and echo the output.
     * If $this->layout == false, will act the same as Controller::render_partial.
     * @param string $file The view file to render, relative to the base of the application.
     */
    public function render($file) {
			if($this->has_rendered){
				throw new NimbleException('Double Render Error: Your may only render once per action');
			}
	
			$this->has_rendered = true;
			$this->template = FileUtils::join(Nimble::getInstance()->config['view_path'], $file);
      if ($this->layout==false){
      	echo trim($this->open_template($this->template)); 
      } else {
      	$this->content = trim($this->open_template($this->template)); 
  			echo trim($this->open_template($this->layout_template)); 
      }
   }

    /**
     * Include a PHP file, inject the controller's properties into that file, and return the output.
     * @param string $file The view file to render, relative to the base of the application.
     * @return string The rendered view file.
     */
    public function render_partial($file)
    {
				$this->rendered_partials[] = $file;
        return $this->open_template(FileUtils::join(Nimble::getInstance()->config['view_path'], $file));
    }

    /**
     * Open a view template file, inject the controller's properties into that file, and execute the file, capturing and returning the output.
     * @param string $name The view file to render, relative to the base of the application.
     * @return string The rendered view file.
     */
    private function open_template($name)
    {
        ob_start();
        if(file_exists($name)){
        	foreach(get_object_vars($this) as $key => $value) {
          	$$key = $value;
          }
					require($name);
				}else if(empty($name)){
					return;
        } else {
            throw new NimbleException('View ['.$name.'] Not Found');
        }
        return trim(ob_get_clean());
    }

    /**
     * Add an HTTP header to be included in the output.
     * @param string $text The header to add.
     */
    public function header($text, $code=''){ 
			$this->headers[] = array($text, $code); 
		}

    /**
     * Redirect to another URL.
     * @param string $url The URL to redirect to.
     * @param boolean $now If true
     */
    public function redirect($url, $now=false)
    {
			if($now && self::nimble()->test_mode == false){
				header(static::redirect_header($url), true, 302);
				exit();
			}else{
      	$this->header(static::redirect_header($url), 302);
				$this->has_rendered = true;
			}
    }
		
		public static function redirect_header($location) {
			return "Location: {$location}";
		}
		
    /**
     * Redirect to another URL immediately.
     * @param string $url The URL to redirect to.
     */
    public function redirect_to($url)
    {
       $this->redirect($url, true);
    }
}
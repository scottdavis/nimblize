<?php
	
	/**
	*  @package Support
	*  A Tag helping class
	*/
	class TagHelper {
		private static $BOOLEAN_ATTRS = array('disabled', 'readonly', 'multiple', 'checked'); 
		/**
		* Creates an element
		* @param string $name Tag name
		* @param array $options key => value pairs for tag attributes
		* @param boolean $open Leave the tag open ex. <test> || <test/>
		* @param boolean $escape escape attribtes like links etc.. 
		* note: if you need mixed escapeing manualy escape the attribute value with htmlspecialchars()
		* @return string
		*/
		public static function tag($name, $options=array(), $open=false, $escape=false) {
			$out = "<" . $name;
			 if(!empty($options)){
			 	$out .= ' ' . join(' ', self::tag_options($options, $escape));
			 }
			 $out .= $open ? ">" : " />";
			 return $out;
		}
		/**
		* Creates a content element ex. <div>foo</div>
		* @param string $name Tag name
		* @param string $content What goes inside the tag
		* @param array $options key => value pair tag attributes @see TagHelper::tag_options()
		* @return string
		*/
		public static function content_tag($name, $content, $options=array()) {
			return self::tag($name, $options, true) . $content . self::close_tag($name);
		}
		/**
		* Creates a cosing tag ex. </tag>
		* @param string $name tag name
		* @return string
		*/
		public static function close_tag($name) {
			return '</' . $name . '>';
		}
		/**
		* Create tag attributes
		* @param array $options key => value pairs for tag attributes
		* @param boolean $escape escape attribtes like links etc.. 
		* note: if you need mixed escapeing manualy escape the attribute value with htmlspecialchars()
		* @return string
		*/
		public static function tag_options($options, $escape=true) {
			if(isset($options) && !empty($options)){
				$attrs = array();
				if($escape) {
					foreach($options as $key => $value){
						if(in_array(self::$BOOLEAN_ATTRS)) {
							array_push($attrs, trim($key) . '="' . $value .'"');
						}else{
							array_push($attrs, trim($key) . '="' . htmlspecialchars($value) .'"');	
						}
					}
				}else{
					foreach($options as $key => $value){
						array_push($attrs, trim($key) . '="' . $value .'"');
					}
				}
				return $attrs;
			}
		}
		
		public static function pagination($collection, $options = array()) {
			$pagination_options = array(
			      'class'          => 'pagination',
			      'previous_label' => '&laquo; Previous',
			      'next_label'     => 'Next &raquo;',
			      'param_name'     => 'page'
			    );
			$pagination_options = array_merge($pagination_options, $options);
			
			if(!isset($collection->total_count) && isset($collection->page) && isset($collection->per_page)) {
				throw new NimbleException('You must pass a paginated result set to the pagination helper');
			}
			$url = $_SERVER['REQUEST_URI'];

			$total_buttons = ceil($collection->total_count / $collection->per_page) + 1;
			if($total_buttons == 1) {return '';}
			if(isset($_GET[$pagination_options['param_name']]) && !empty($_GET[$pagination_options['param_name']])) {
				$url = str_replace($pagination_options['param_name'] . '=' . (string) $_GET[$pagination_options['param_name']],
				 										$pagination_options['param_name'] . '={page}', 
														$url);		
			}else{
				if(strpos('?', $url) === false) {
					$url = $url . '?page={page}';
				}else{
					$url = $url . '&page={page}';
				}
			}
			//Build List
			$out = array();
			
			if((int) $collection->page != 1) {
				$prev_page = ($collection->page <= 1) ? 1 : ($collection->page - 1);
				$link = TagHelper::content_tag('a', 
																			 $pagination_options['previous_label'], 
																			 array('href' => str_replace('{page}', $prev_page, $url)));
																			
				$out[] = TagHelper::content_tag('li', $link);
			}
			for($i=1; $i < $total_buttons; $i++) {		
				$myurl = str_replace('{page}', $i, $url);
				$link = ((int) $collection->page == $i) ? $i : TagHelper::content_tag('a', $i, array('href' => $myurl));
				$out[] = TagHelper::content_tag('li', $link);
			}
			if((int) $collection->page != ($total_buttons -1)) {
				$next_page = ((int) $collection->page >= (int) $total_buttons) ? $total_buttons : ((int) $collection->page + 1);
				$link = TagHelper::content_tag('a', $pagination_options['next_label'], array('href' => str_replace('{page}', $next_page, $url)));
				$out[] = TagHelper::content_tag('li', $link);
			}
			return TagHelper::content_tag('ul', implode('', $out), array('class' => $pagination_options['class']));
			
		}
	
	}
	/**
	*  @package Support
	*  A form tag helping class
	*/
	
	class FormTagHelper extends TagHelper {
		
		/**
		*  Creates and HTML label tag
		*  @param string $contents What goes between the label tag
		*  @param string $id The help id of the element that this label is attached
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function label($contents, $id, $options=array()) {
			$options = array_merge($options, array('for' => $id));
			return self::content_tag('label', $contents, $options);
		}
		
		/**
		*  Creates and HTML text_field input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function text_field($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'text'));
			return self::tag('input', $options);
		}
    
    /**
		*  Creates and HTML file_field input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function file_field($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'file'));
			return self::tag('input', $options);
		}
    
		/**
		*  Creates and HTML pasword input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function password($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'password'));
			return self::tag('input', $options);
		}		
		/**
		*  Creates and HTML checkbox input tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function checkbox($id, $name, $options=array()){
			$options = array_merge($options, array('id' => $id, 'name' => $name, 'type' => 'checkbox', 'value' => '1'));
			return self::hidden_field($id='', $name, 0) . self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML submit button
		*  @param string $id The help id of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function submit($name, $options=array()) {
			$options = array_merge($options, array('type' => 'submit', 'value' => $name));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML image submit tag
		*  @param string $image url of the image. Best if used with an image helper
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function image_submit($image, $options=array()) {
			$options = array_merge($options, array('src' => $image, 'type' => 'image'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML hidden input tag
		*  @param string $id The help id of the element
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function hidden_field($id, $name, $value, $options=array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id, 'value' => $value, 'type' => 'hidden'));
			return self::tag('input', $options);
		}
		
		/**
		*  Creates and HTML textarea tag
		*  @param string $id The help id of the element
		*  @param string $name of the element
		*  @param string $value What goes in the textarea
		*  @param array $options key => value pairs for tag attributes
		*/
		public static function textarea($id, $name, $value, $options=array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id));
			return self::content_tag('textarea', $value, $options);
		}
		
		
		public static function select($id, $name, $content, $options = array()) {
			$options = array_merge($options, array('name' => $name, 'id' => $id));
			return self::content_tag('select', $content, $options);
		}
		
		public static function option($name, $value, $options = array()) {
			$options = array_merge($options, array('value' => $value));
			return self::content_tag('option', $name, $options);
		}
		
	}
	
	

	
	
	
	


?>
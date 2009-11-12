<?php

	/**
	* This class handles all the validations for models
	* each validation is called with a magic method
	*/




	class NimbleValidation {
		
		
		public static function _methods() {
			$a = get_class_methods();
			
			
		}
		
	
		/** tested */
		public static function acceptance_of($args = array('column_name', 'value')) {
			$defaults = array('message'=> 'must be accepted');
			$args = array_merge($defaults, $args);
			 if(!$args['value']) {
				return self::false_result($args['column_name'], $args['message']);	
            }else{
				return array(true);
			}
		}
		
		/** tested */
		public static function confirmation_of($args = array('column_name', 'value1', 'value2')) {	
      $defaults = array('message' => "doesn't match confirmation");
			$args = array_merge($defaults, $args);
      if($args['value1'] != $args['value2']) {
				return self::false_result($args['column_name'], $args['message']);
    	}else{
				return array(true);
			}
    }
		
		/**
		 * tested
		 * Validates that attributes are NOT in an array.
		 * eg. $this->validates_exclusion_of($callback, array(13, 19));
		 * eg. $this->validates_exclusion_of($callback, range(13, 19));
		 * @see range()
		 */
		public static function exclusion_of($args = array('column_name', 'value', 'in' => array())) {				
			$defaults = array('message' => 'is reserved');
			$args = array_merge($defaults, $args);
			$test_array = array_flip($args['in']);
			if(isset($test_array[$args['value']])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
        }
			
		/**
		 * tested
		 * Validates that attributes are in an array.
		 * eg. $this->validates_inclusion_of($callback, array(13, 19));
		 * eg. $this->validates_inclusion_of($callback, range(13, 19));
		 * @see range()
		 */
		public static function inclusion_of($args = array('column_name', 'value', 'in' => array())) {	
			$defaults = array('message' => 'is not included in the list');
			$args = array_merge($defaults, $args);
			$test_array = array_flip($args['in']);
			if(!isset($test_array[$args['value']])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
        }
		
		
		/* tested */
		public static function format_of($args = array('column_name', 'value', 'with')) {						
			$defaults = array('message' => 'is invalid');
			$args = array_merge($defaults, $args);
			if(!preg_match($args['with'], (string) $args['value'])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
        }
		/** tested */
		public static function length_of($args = array('column_name', 'value', 'length', 'in')) {
			$defaults = array('message' => 'is the wrong length');
			$args = array_merge($defaults, $args);	
			if(isset($args['length']) && !empty($args['length']) && strlen($args['value']) === (int) $args['length']) {
				return array(true);
			}elseif(isset($args['in']) && !empty($args['in'])){
				if(is_string($args['in']) && strpos($args['in'], '..') !== false) {
					$range = explode('..', $args['in']);
					$first = (int) $range[0];
					$last = (int) $range[1];
				}elseif(is_array($args['in'])){
					$first = $args['in'][0];
					$last = array_pop($args['in']);
				}else{
					throw new NimbleRecordException("input needs to either be a string 1..4 or an array(1,4)");
				}
				$l = strlen($args['value']);
				if($first <= $l && $last >= $l) {
					return array(true);
				}else{
					return self::false_result($args['column_name'], $args['message']);
				}	
			}else{
				return self::false_result($args['column_name'], $args['message']);
			}	
		}
		/** tested */
		public static function numercality_of($args = array('column_name', 'value')) {
			$defaults = array('message' => 'must be an integer');
			$args = array_merge($defaults, $args);
			if(is_numeric($args['value']) || empty($args['value'])) {
				return array(true);
			}else{
				return self::false_result($args['column_name'], $args['message']);
			}
		}
		
		/** tested */
		public static function presence_of($args = array('column_name', 'value')) {
			$defaults = array('message' => "can not be blank");
			$args = array_merge($defaults, $args);
			if(!isset($args['value'])) {
				return self::false_result($args['column_name'], $args['message']);
			}
			if(is_numeric($args['value'])) {
				if($args['value'] === 0 || $args['value'] === '0') {
					return array(true);
				}
			}
			if(empty($args['value'])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
		}
		
    
    public static function uniqueness_of($args = array('column_name', 'value', 'class', 'instance')) {
	    $defaults = array('message' => ": {$args['value']} already exists try something else");
			$args = array_merge($defaults, $args);
			$value = $args['value'];
			$class = $args['class'];
			$column = $args['column_name'];
			$fail = call_user_func_array(array($class, 'exists'), array($column, $value));
      if($fail) {
				$instance = $args['instance'];
				if(!$instance->new_record) {
					$result = call_user_func_array(array($class, '_find'), 
																				 array('first', array('conditions' => array($column => $value))));
					if($result->id === $instance->id) {
						return array(true);
					}
				}
        return self::false_result($column, $args['message']);
      }else{
        return array(true);
      }
    }
		
		
		
		
		private static function false_result($column_name, $message) {
			return array(false, $column_name, Inflector::humanize($column_name) . ' ' . $message);
		}
		
		
    }


?>
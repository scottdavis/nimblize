<?php

class NimblePagination implements NimbleRecordCommandInterface {
	public static $methods = 	array('paginate');
	
	public static function methods() {
		return static::$methods;
	}
	
	public static function do_method($method, $class, $table, $options = array()) {
	  $options['class'] = $class;
    return static::paginate($options);
	}
	
	private static function paginate($input) {
	  $class = $input['class'];
	  $defaults = array('per_page' => 25, 'page' => 1);
		$input = array_merge($defaults, $input);
		$input['page'] = is_null($input['page']) ? 1 : $input['page'];
		$input['conditions'] = isset($input['conditions']) ? $input['conditions'] : array();
		$total_count = call_user_func(array($class,'count'), $input);
		$per_page = $input['per_page'];
		$page = $input['page'];
		unset($input['page'], $input['per_page']);
		$limit = (int) $per_page * ((int) $page - 1);
		$limit = ($limit > 0) ? $limit : 0;
		$limit = implode(',', array($limit, (int) $per_page));
		$input['limit'] = $limit;
		unset($limit);
		$return = call_user_func(array($class, 'build_find_sql'), array('all', $input));
		$return = call_user_func_array(array($class, 'execute_query'), $return);
		$return->total_count = $total_count;
		$return->per_page = $per_page;
		$return->page = $page;
		return $return;
	}
	
	
}
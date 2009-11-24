<?php
/**
* @package NimbleRecord
*/
/**
* MATH METHODS
*
* @package NimbleRecord
* Math methods are completely magic
* Sum, Max, Min 
* -- Staticly called --
* ex User::(sum|max|min)('price', array('conditions' => array('id' => 5));
* Instance called follows has_many associations 
* ex $user->sum('cars', 'price');
* -- Count --
* NOTE: count is special
* Staticly called
* ex User::count()
* ex User::count(array('conditions' => array('price' => '5.00')));
* Instance called follows has_hany association
* $user->count('photos');
* $user->count('photos', array('conditions' => array('price' => '10.00')));
*/
class NimbleRecord {
	/** public vars */
	public static $query_log = array();
	public static $class;
	public static $debug = false;
	public static $test_mode = false;
	public static $adapter = NULL;
	public static $max_rows_for_cache = 500;
	public static $associations = array();
	public static $foreign_key_suffix = 'id';
	public static $primary_key_field = 'id';
	public static $table_name_prefix = '';
	public static $database;
	public static $table;
	public static $protected = array();
	public static $read_only = array();
	public static $white_list = array();
	public static $query_cache = array();
	/** protected vars */

	protected static $connection;
	protected static $columns = array();
	protected static $validations = array();
	protected static $my_class;
	protected static $table_names = array();
	protected static $temp = array();
	/** Method Maps */
	protected static $magic_method_map = array('delete' => '_delete');
	protected static $math_method_map = array('max' => '_max', 'sum' => '_sum', 'min' => '_min');
	
	var $update_mode = false;
	var $saved;
	var $errors;
	var $preloaded_associations = array();
	var $new_record = true;
	var $row = array();
	/**
	* Required
	* connects to the database and stores the connection
	* @param array $db_settings
	*/
	public static function establish_connection(array $db_settings) {
		static::$database = $db_settings['database'];
		$file = strtolower($db_settings['adapter']) . '_adapter';
		$filename = $file . '.php';
		/**
		* lazy loading of the adapter
		*/
		$_adapters = self::get_available_adapters();
		if(isset($_adapters[$filename])) {
			require_once(__DIR__ . '/../adapters/' . $filename);
		}
		$class = Inflector::classify($file);
		$klass = new $class($db_settings);
		static::$adapter = $klass;
	}
	
	private static function get_available_adapters() {
		$_adapters = array();
		if ($dh = opendir(__DIR__ . '/../adapters')) {
			while (($file = readdir($dh)) !== false) {
		    	if(strpos($file, '_adapter.php') !== false) {
						$_adapters[$file] = true;
					}
		  }
		 	closedir($dh);
		}
		return $_adapters;
	}
	
  /**
	* @return string the quoted table name
	* @param string $class
	*/
	public static function table_name($klass= '') {
		$klass = empty($klass) ? static::class_name() : $klass;
		if(isset(static::$table_names[$klass]) && !empty(static::$table_names[$klass])) { 
			$name = static::$table_name_prefix . static::$table_names[$klass];
		}else{
			static::$table_names[$klass] = strtolower(Inflector::pluralize($klass));
			$name = static::$table_name_prefix . static::$table_names[$klass];
		}
		return static::$adapter->quote_table_name($name);
	}
	/**
	* @return AbstractAdapter
	*/
	public static function adapter() {
		return static::$adapter;
	}
	/**
	* Is the class running in test mode?
	* @return boolean
	*/
	public static function test_mode() {
		return static::$test_mode;
	}
	/**
	* returns name of the primary key field
	*/
	protected static function primary_key_field() {
		return static::$primary_key_field;
	}
	/**
	* @return resource Raw database connection
	*/
	protected static function connection() {
		return static::$connection;
	}
	/**
	* Sets the connection
	* @param resource $conn
	*/
	public static function set_connection($conn) {
		static::$connection = $conn;
	}
	/**
	* @return string Current Databse
	*/
	public static function database() {
		return static::$database;
	}
	/**
	* @return string Current Class Name
	*/
	public static function class_name() {
		return get_called_class();
	}
	/**
	* @return string Class Name
	*/
	public static function get_class() {
		if(!isset(static::$my_class) && empty(static::$my_class)) {
			$class =  static::class_name();
			static::$my_class = new $class;
		}
		return static::$my_class;
	}
	
	/**
	* @return mixed Returns cleaned values from adapaters excape method
	* @param mixed $input
	*/
	private static function sanatize_input_array($input) {
		if(is_array($input)) {
			$clean_values = array();
			foreach($input as $value) {
				$clean_values[]= static::$adapter->escape($value);
			}
			return $clean_values;
		}else{
			return static::$adapter->escape((string) $input);
		}
	}
		
	/**
	* @see self::check_args_for_math_functions($options)
	* @param $options array('column' => 'name', 'conditions' => array('id' => 1))  
	*/
	protected static function check_args_for_math_functions(array $options){
		//verify options contains a column value
		if(!is_array($options) || !isset($options['column'])){
			throw new NimbleRecordException('InvalidArguments - please include a column ex. array(\'column\' => \'id\')');
		}
	}
	/**
	* Method count
	* use Class::count(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function _count(array $options = array()) {
		$defaults = array('column' => '*', 'conditions' => NULL, 'cache' => true);
		$options = array_merge($defaults, $options);
		static::check_args_for_math_functions($options);
		$query = new NimbleQuery();
		$query->select = 'count(' . $options['column'] . ') AS count_all';
		$query->from = self::table_name();
		if(isset($options['conditions'])) {
			$query->where = self::build_conditions($options['conditions']);
		}
		$sql = $query->build();
		return self::execute_query($sql, false, $options['cache'])->count_all;
	}
	/**
	* Method sum
	* use Class::sum(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function _sum(array $options = array('column' => NULL, 'conditions' => NULL)) {
		static::check_args_for_math_functions($options);
		$query = new NimbleQuery();
		$query->from = self::table_name();
		$query->select = 'sum(' . self::table_name() . '.' . $options['column'] . ') as sum_all'; 
		if(isset($options['conditions'])) {
			$query->where = self::build_conditions($options['conditions']);
		}
		$sql = $query->build();
		return self::execute_query($sql, false)->sum_all;
	}
	/**
	* Method max
	* @uses Class::max(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function _max(array $options = array('column' => NULL, 'conditions' => NULL)) {
		static::check_args_for_math_functions($options);
		$query = new NimbleQuery();
		$query->from = self::table_name();
		$query->select = 'max(' . self::table_name() . '.' . $options['column'] . ') as max_all'; 
		if(isset($options['conditions'])) {
			$query->where = self::build_conditions($options['conditions']);
		}
		$sql = $query->build();
		return self::execute_query($sql, false)->max_all;
	}
	/**
	* Method min
	* @uses Class::min(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function _min(array $options = array('column' => NULL, 'conditions' => NULL)) {
		static::check_args_for_math_functions($options);
		$query = new NimbleQuery();
		$query->from = self::table_name();
		$query->select = 'min(' . self::table_name() . '.' . $options['column'] . ') as min_all'; 
		if(isset($options['conditions'])) {
			$query->where = self::build_conditions($options['conditions']);
		}
		$sql = $query->build();
		return self::execute_query($sql, false)->min_all;
	}
	/**
	* Method build_conditions
	* use self::build_conditions(array('name' => 'bob')) or self::build_conditions('id = 3')
	* @param $conditions Array || String
	*/
	private static function build_conditions($conditions) {
			$sql = '';
			if(is_array($conditions)){
				$sql .= ' ' . self::build_where_from_array($conditions);
			}else if(is_string($conditions)){
				$sql .= ' ' . $conditions;
			}
			return $sql;
	}
	
	/**
	* Paginated Finder
	* @uses User::paginate(array('conditions' => 'foo = bar', 'page' => $_GET['page], 'per_page' => 25))
	*/
	
	public static function paginate(array $input) {
		$defaults = array('per_page' => 25, 'page' => 1);
		$input = array_merge($defaults, $input);
		$input['page'] = is_null($input['page']) ? 1 : $input['page'];
		$input['conditions'] = isset($input['conditions']) ? $input['conditions'] : array();
		$count_conditions = array();
		if(isset($input['conditions']) && !empty($input['conditions'])) {
			$count_conditions = array('conditions' => $input['conditions']);
		}
		$total_count = self::count($count_conditions);
		unset($count_conditions);
		$per_page = $input['per_page'];
		$page = $input['page'];
		unset($input['per_page']);
		unset($input['page']);
		$limit = (int) $per_page * ((int) $page - 1);
		$limit = ($limit > 0) ? $limit : 0;
		$limit = implode(',', array($limit, (int) $per_page));
		$args[0] = 'all';
		$args[1] = $input;
		$args[1]['limit'] = $limit;
		unset($limit);
		unset($input);
		$return = static::build_find_sql($args);
		$return = self::execute_query($return[0], $return[1]);
		$return->total_count = $total_count;
		$return->per_page = $per_page;
		$return->page = $page;
		return $return;
	}
	
	/**
	* Method find
	* @uses self::find(1,2,3,4,5) or self::find(3) or self::find('3')
	* @param string|integer|array $args
	*/
	public static function find() {
		$args = func_get_args();
		$return = self::build_find_sql($args);
		return self::execute_query($return[0], $return[1]);
	}
		
	
	public static function _find() {
		$args = func_get_args();
		$return = self::build_find_sql($args);
		return self::execute_query($return[0], $return[1], false);
	}
  
  public static function _delete() {
		$query = new NimbleQuery(NimbleQuery::DELETE);
		$query->from = self::table_name();
		$args = func_get_args();
		switch(count($args)) {
			case 1:
				$clean = self::sanatize_input_array($args[0]);
		    if(is_array($args[0])) {
		      $query->where = NimbleQuery::in(static::$primary_key_field, $clean);
		    }else{
		      $query->where = NimbleQuery::condition(static::$primary_key_field, $clean);
		    }
			break;
			default:
				$clean = self::sanatize_input_array($args);
				$query->where = NimbleQuery::in(static::$primary_key_field, $clean);
			break;
		}
    $sql = $query->build();
    return self::execute($sql);
  }
	
	private static function build_conditions_from_array($array) {
		$out = array();
		foreach($array as $key => $value) {
			$out[] = NimbleQuery::condition($key, $value);
		}
		return $out;
	}
	
	
	private static function build_find_sql($input) {
		$query = new NimbleQuery();
		$query->from = self::table_name();
		$temp = array_pop($input);
		(is_array($temp)) ? $options = $temp : $input[] = $temp;
		$all = false;
		$conditions = array();
		$input = static::sanatize_input_array($input);
		$num_args = count($input);
		if($num_args > 1) {
			$all = true;
			$conditions[] = NimbleQuery::in(static::$primary_key_field, $input);
		}elseif($num_args == 1){
			if(!array_include((string) $temp, array('first', 'all')) && is_numeric($temp)){
				$conditions[] = NimbleQuery::condition(static::$primary_key_field, $temp);
			}
			switch($input[0]) {
				case 'first':
					$query->limit = '0,1';
				break;
				case 'all':
					$all = true;
				break;
			}	
		}
		if(isset($options)) {
			if(isset($options['conditions'])) {
				if(is_array($options['conditions'])) {
					$conditions = array_merge($conditions, static::build_conditions_from_array($options['conditions']));
				}else{
					$conditions[] = $options['conditions'];
				}
			}
			if(isset($options['limit'])) {
				$query->limit = $options['limit'];
			}
			if(isset($options['order'])) {
				$query->order_by = $options['order'];
			}
		}
		if(!empty($conditions)) {
			$query->where = implode(' AND ', $conditions);
		}
		$sql = $query->build();
		unset($query);
		return array($sql, $all);
	}
	
  /**
	* Method find_all
	* use self::find_all(array('condtions' => 'name = bob')) or self::find_all()
	* @param options Array
	*/
	public static function find_all(array $options = array()) {
		return self::find('all', $options);
	}
	
	public function destroy() {
		call_user_func(array($this, 'before_destroy'));
    self::delete($this->id);
		$this->row = array();
		call_user_func(array($this, 'after_destroy'));
  }
	
	public static function delete_all() {
		$query = new NimbleQuery(NimbleQuery::DELETE);
		$query->from = self::table_name();
		$sql = $query->build();
		unset($query);
		return self::execute($sql);
	}
	
	public static function truncate() {
		$query = new NimbleQuery(NimbleQuery::TRUNCATE);
		$query->truncate = self::table_name();
		$sql = $query->build();
		return self::execute($sql);
	}
	
	public static function run_validations($klass) {	
		call_user_func_array(array($klass, 'validations'), array());
	}
	
	public static function getErrors($klass) {
		call_user_func_array(array($klass, 'before_validation'), array());
		/** Run default mysql validations based on column types */
		self::column_validatons($klass);
		/** run customn user made validations */
		self::run_validations($klass);
		call_user_func_array(array($klass, 'after_validation'), array());
	}
	
	
	public function is_valid() {
		static::getErrors($this);
		return count($this->errors) == 0 ? true : false; 
	}
	
	/**
  * Checks weither a record exists or not
  * @param string $col Column name you wish to check
  * @param string $value Value you wish to check aginst
  * @todo add multi conditional support
  */
  public static function exists($col, $value) {
		$query = new NimbleQuery();
		$query->select = '1';
		$query->from = static::table_name();
		$query->where = '(`' . static::sanatize_input_array($col) . '`= ' . "'" . static::sanatize_input_array($value) . "')";
		$query->limit = '0,1';
		$sql = $query->build();
		unset($query);
    $result = static::execute($sql);
    $return = $result->fetch_assoc();
    if(isset($return['1'])) {
      return true;
     }else{
      return false;
     }
  }
	
	
	
	/**
	* START CREATE METHODS
	*/

	public static function create(array $attributes = array()) {
		$c = static::class_name();
		$klass = new $c;
		$klass->row = array_merge($klass->row, $attributes);
		static::getErrors($klass);
		if(count($klass->errors) == 0) {
    	call_user_func_array(array($klass, 'before_create'), array());
			call_user_func_array(array($klass, 'before_save'), array());
		}
		/** Update timestamps if the columns exsist */
		$columns = static::columns();
		$columns = array_flip($columns);
		if(isset($columns['created_at']) && isset($columns['updated_at'])) {
			$klass->row = self::update_timestamps(array('created_at', 'updated_at'), $klass->row);
		}
		unset($columns);
		$query = new NimbleQuery(NimbleQuery::INSERT);
		$query->insert_into = self::table_name() ;
		unset($klass->row['id']);
		$values = static::prepair_nulls(array_values($klass->row));
		$query->columns = static::sanatize_input_array(array_keys($klass->row));
		$query->values = static::sanatize_input_array($values);
		$sql = $query->build();
		unset($query);
		if(count($klass->errors) == 0 && self::execute_insert_query($sql)) {
			array_push(static::$query_log, "CREATE: $sql");
			$klass->row['id'] = static::insert_id();
			$klass->saved = true;
			call_user_func_array(array($klass, 'after_create'), array());
			call_user_func_array(array($klass, 'after_save'), array());
			return $klass;
		}else{	
			$klass->saved = false;
			return $klass;
		}
		
	}
	
	public static function _create(array $attributes = array()) {
		$create = self::create($attributes);
		if(count($create->errors)) {
			throw new NimbleRecordException(join("\n", $create->errors));
		}
		if(!$create->saved){
			throw new NimbleRecordException('Failed to create record');
		}
		return $create;
	}
	
	
	private static function insert_id() {
		return static::$adapter->insert_id();
	}
	
	
	private static function update_timestamps(array $timestamp_cols, array $attributes) {
		$columns = self::columns();
		$time = DateHelper::to_string('db', time());
		foreach($timestamp_cols as $ts) {
			if(in_array($ts, $columns)) {
				$attributes = array_merge($attributes, array($ts => $time));
			}
		}
		return $attributes;
	}
	//@codeCoverageIgnoreStart
	public function before_update() {}
	public function after_update() {}
	public function before_create() {}
	public function after_create()  {}
	public function before_save()		{}
	public function after_save()		{}
	public function before_validation() {}
	public function after_validation() {}
	public function before_destroy() {}
	public function before_delete()	{}
	public function after_destroy()	{}
	public function after_delete() {}
	//@codeCoverageIgnoreEnd
	
	public static function update($id, $attributes = array()) {
		$klass = self::_find($id);
		$old_row = $klass->row;
		$klass->update_mode = true;
    $klass->row = array_merge($klass->row, $attributes);
    static::getErrors($klass);
		if(count($klass->errors) == 0) {
			call_user_func_array(array($klass, 'before_update'), array());
			call_user_func_array(array($klass, 'before_save'), array());
		}
		$updates = array();
		/** Update timestamp if the column exsists */
		$columns = static::columns();
		if(array_include('updated_at', $columns)) {
			$klass->row = self::update_timestamps(array('updated_at'), $klass->row);
		}
		unset($columns);
		// Build query
		$query = new NimbleQuery(NimbleQuery::UPDATE);
		$query->update = self::table_name();
		$query->columns = self::sanatize_input_array(array_keys($klass->row));
		$query->values = self::sanatize_input_array(array_values($klass->row));
		$query->where = "`id` = " . self::sanatize_input_array($id);
		$sql = $query->build();
		unset($query);
		if(count($klass->errors) == 0 && self::execute_insert_query($sql)) {
			array_push(self::$query_log, "UPDATE: $sql");
      $klass->id = $id;
			$klass->saved = true;
			$klass->update_mode = false;
			$klass->new_record = false;
			call_user_func_array(array($klass, 'after_update'), array());
			call_user_func_array(array($klass, 'after_save'), array());
			return $klass;
		}else{
			$klass->saved = false;
			$klass->update_mode = false;
			return $klass;
		}
	
	}
	
	public static function _update($id_or_array, $attributes = array()) {
		$update = self::update($id_or_array, $attributes);
		if(!$update->saved) {
			throw new NimbleRecordException('Failed to update record');
		}
		return $update;
	}
		
		
	protected static function prepair_nulls($array) {
		if(is_array($array)) {
			foreach(array_keys($array) as $key) {
				if(is_null($array[$key]) || $array[$key] == null) {
					$array[$key] = 'NULL';
				}
			}
		}else{
			if(is_null($array) || $array == null) {
				$array = 'NULL';
			}else{
				$v = $array;
				$array = "'{$v}'";
			}
		}
		return $array;
	}	
		
	
	/** 
	* Checks if the current query is cached
	* @return boolean
	*/
	
	private static function is_query_cached($sql) {
		return isset(static::$query_cache[static::generate_hash_key($sql)]) && !empty(static::$query_cache[static::generate_hash_key($sql)]);
	}
	
	/**
	* Fetches a query result from the cache
	*/
	
	private static function fetch_query_data_from_cache($sql) {
		if(static::$debug) {
			array_push(static::$query_log, "CACHED: $sql");
		}
		return static::$query_cache[static::generate_hash_key($sql)];
	}
	
	/**
	* sets a value in the cache
	*/
	
	public static function cache($key, $value) {
		return self::$query_cache[static::generate_hash_key($key)] = $value;
	}
	
	/**
	* Resets query cache
	*/
	
	public static function reset_cache() {
		static::$query_cache = array();
		return true;
	}
	
	
	public static function generate_hash_key($string) {
		return hash('md5', $string);
	}
	
	public static function remove_from_cache($key) {
		unset(static::$query_cache[$key]);
		return true;
	}
	
	/**
	* Method load_columns
	* Fetches all the columns from this table for validations and packs them into a cached array
	*/
	private static function load_columns() {
		$sql = static::$adapter->load_column_sql(static::table_name());
		if (static::is_query_cached($sql)) {
			return static::fetch_query_data_from_cache($sql);
		}else{
			$result = static::execute($sql);
			$output = array();
			while($row = $result->fetch_assoc()) {
				array_push($output, $row);
			}
			static::cache($sql, $output);
			return $output;
		}
	}
	/**
	* Method column array
	* Builds a 1 demisional array of column names 
	*/
	private static function column_array() {
		$columns = self::load_columns();
		$output = array();
		foreach($columns as $column) {
			$output[] = $column['Field'];
		}
		return $output;
	}
	
	/**
	* Method columns
	* Sets the static variable self::$columns with the results from self::column_array()
	* @param $override Boolean - will reload the columns
	*/
	public static function columns($override = false) {
		$class = self::class_name();
		if($override || empty(self::$columns[$class])) {
			self::$columns[$class] = self::column_array();
		}
		return self::$columns[$class];
	}
	
	
	/**
	* Method column_validations
	* Builds and array of required columns based on if the column is allowed to be null or not
	*/
	private static function column_validatons($klass) {
		$columns = self::load_columns();
		foreach($columns as $column) {
			if (strtolower($column['Null']) == 'no' && strtolower($column['Field'] != self::$primary_key_field) && !preg_match('/_id$/', strtolower($column['Field']))){
					if(is_null($klass->row[strtolower($column['Field'])]) || empty($klass->row[strtolower($column['Field'])])) {
						$col = ucwords($column['Field']);
						array_push($klass->errors, array(strtolower($column['Field']) => "{$col} can not be blank"));
					}
			}
		}
	}


  /**
	* Method execute_query
	* use self::execute_query('SELECT * FROM `foo` WHERE `foo`.id = 1', false)
	* @param sql String
	* @param all Boolean - true = multiresults | false = single result
	*/
	public static function execute_query($sql, $all = true, $cache = true){
		//fetch query cache if it exsists
		if ($cache && static::is_query_cached($sql)) {
			array_push(static::$query_log, "CACHED: $sql");
			return static::fetch_query_data_from_cache($sql);
		}else{
			//execute query and set cache pointer
			array_push(static::$query_log, $sql);
			$result = static::execute($sql);
			
			$key = '';
			if($cache) {
				$key = static::generate_hash_key($sql);
			}
			
			$return =  $all ? static::to_objects($result, $key) : static::to_object(static::class_name(), $result->fetch_assoc());
			if($cache && $result->num_rows() <= static::$max_rows_for_cache) {
				static::cache($sql, $return);
			}
			if($result) {
				$result->free();
			}
			return $return;
		}
	}
	
	
	public static function start_transaction() {
		static::execute('BEGIN;');
	}
	
	public static function rollback_transaction() {
		static::execute('ROLLBACK;');
	}
	
	public static function commit_transaction() {
		static::execute('COMMIT;');
	}
	
	public static function execute_insert_query($sql) {
		$return = static::execute($sql);
		return $return;
	}
	
	
	
	public static function disable_referential_integrity($reset=false) {
		if(!$reset) {
			$old = static::select_one("SELECT @@FOREIGN_KEY_CHECKS");
			static::$temp['old_fk'] = reset($old);
			static::execute("SET FOREIGN_KEY_CHECKS = 0");
		}else{
			static::execute("SET FOREIGN_KEY_CHECKS = " . static::$temp['old_fk']);
			unset(static::$temp['old_fk']);
		}
		
	}
	/**
	* executes a single query returns the result object;
	* @param $sql 
	*/
	public static function execute($sql, $reset=false) {
		if($reset) {
			static::$adapter->reset();
		}
		if(static::test_mode()){
			echo $sql . "\n\n";
		}
		if(static::$debug) {
			array_push(self::$query_log, $sql);
		}
		return static::$adapter->query($sql);
	}
	
	/**
	* executes a scaler returning a single row
	* @param $sql 
	*/
	public static function select_one($sql) {
		$result = self::execute($sql);
		$r = $result->fetch_assoc();
		$result->free();
		return $r;
	}
	
	
	/**
	* Method to_objects
	* use self::to_objects($result)
	* @param array $result_set_array
	*/
	private static function to_objects($result_set_array, $key='') {
		$class = static::class_name();
		$object_list = array();
		while($result_set = $result_set_array->fetch_assoc()) {
		  array_push($object_list, self::to_object($class, $result_set));
		}
		return new NimbleResult($object_list, array('key' => $key));
	}
  	/**
	* Method to_object
	* use self::to_object($result_set)
	* @param array $result_set 
	*/
  private static function to_object($class, $result_set) {
		if (empty($result_set)){
			throw new NimbleRecordNotFound();
		}
	$object = new $class;
	$object->row = $result_set;
	$object->new_record = false;
	unset($result_set);
	unset($class);
	return $object;
  }
	/**
	* Method build_where_from_array
	*	use self::build_where_from_array(array())
	* @param $conditions Array - array('id' => 3, 'name' => 'bob')
	*/
	private static function build_where_from_array($conditions){
		$sql = '';
		foreach($conditions as $condition => $value){
			$sql .= $condition. " = '" . static::$adapter->escape($value) ."'";
		}
		return $sql;
	}
  
  /* Instance Methods */

  public function __construct($args = array()) {
		$this->row = array();
	  $this->errors = array();
		static::process_associations($this);
		$all_columns = static::columns();
		
		//set all columns to NULL
		foreach($all_columns as $col) {
			$this->set_var($col, NULL, false);
		}
		//if no args process return
		if(empty($args)) {return;}
		
		//if white listing is turned on only allow mass assignment on vars that are in the white list
		if(!empty(static::$white_list)) {
			$all_columns = array_intersect($all_columns, static::$white_list);
		}
		if(count($bad_params = array_diff(array_keys($args), $all_columns)) > 0) {
			throw new NimbleRecordException(implode(',', $bad_params) . ': are a protected attribute(s) and can not be mass assigned');
		}
		//if mass assigning set vars
		foreach($all_columns as $var) {
			if(isset($args[$var])) {
				//process black list
				if(array_include($var, static::$protected)) {
					throw new NimbleRecordException($var . ': is a protected attribute and can not be mass assigned');
				}
	  		$this->set_var($var, $args[$var]);
			}
		}
  }


  /**
  * Method __toString
  * returns NULL or record id
  */
  public function __toString(){
    if (isset($this->row['id'])){
      return (string) $this->row['id'];
    }else{
      return 'NULL';
    }
  }

	public function save() {    
		/**
		* CREATE CODE
		*/
    if($this->new_record) {
			$class = self::create($this->row);
      if($class->saved) {
				$this->row = $class->row;
				return true;
			}else{
        $this->errors = $class->errors;
				return false;
      }
    }else{
			/**
			* UPDATE CODE
			*/
      if(!isset($this->row[self::primary_key_field()])) {
        throw new NimbleRecordException("Primary key not set for row, cannot save.");
      }
      $f = self::primary_key_field();
      $primary_key_value = $this->row[$f];
      unset($this->row[$f]);
			$class = self::update($primary_key_value, $this->row);
      if($class->saved) {
        $this->row = $class->row;
				return true;
      }else{
        $this->errors = $class->errors;
				return false;
      }
    }
  }

  //@codeCoverageIgnoreStart 
	public function validations() {}
	//@codeCoverageIgnoreEnd
	
	public function process_error_return($return) {
		if(!$return[0]) {
			$this->errors[$return[1]] = $return[2];
		}
	}
	
	private static function process_associations($class) {
		call_user_func_array(array($class, 'associations'), array());
	}

	public function __get($var) {
		if(isset($this->row[$var])) {
			return stripslashes($this->row[$var]);
		}
		if(array_include($var, static::columns()) && is_null($this->row[$var])) {
				return NULL;
		}
		$type = NimbleAssociation::find_type($this, $var);
		if($type !== false) {
			return call_user_func_array(array('NimbleAssociation', 'find_' . $type), array($this, $var));
		}
		throw new NimbleRecordException("Property not found in record.");
	}
	
	
	public function __set($var, $value) {
		$this->set_var($var, $value);
	}
	
	private function set_var($var, $value, $read_only_check = true) {
		if(array_include($var, static::columns())){
			if($read_only_check && array_include($var, static::$read_only)) {
				throw new NimbleRecordException($var . ': is a read only attribute');
			}
			$this->row[$var] = $value;
		}else{
			throw new NimbleRecordException("Can not set property: $var it does not exsit.");
		}
	}
	
	
	private function set_assoc($method, $args) {
		$class_name = Inflector::classify(get_class($this));
		if(!isset(NimbleAssociation::$associations[$class_name])) {
			NimbleAssociation::$associations[$class_name] = array();
		}
		if(!isset(NimbleAssociation::$associations[$class_name][$method])) {
			NimbleAssociation::$associations[$class_name][$method] = array();
		}
		switch(count($args)) {
			case 1:
			$var = reset($args);
			$class = new NimbleAssociationBuilder($class_name, $method, $var);
			NimbleAssociation::$associations[$class_name][$method][$var] = $class;
			return $class; 
			break;
			default:
				foreach($args as $var) {
					$class = new NimbleAssociationBuilder($class_name, $method, $var);
					NimbleAssociation::$associations[$class_name][$method][$var] = $class;
					return NULL;
				}
			break;
		}
	}
	
	
	private static function process_magic_condition_merge($c1, $c2) {
		$c1 = self::build_conditions($c1, false);
		$c2 = self::build_conditions($c2, false);
		$out = array($c1, $c2);
		return implode(' AND', $out);
	}
	
	public function __call($method, $args)  {
		
		if(array_include($method, NimbleAssociation::$types)) {
			return $this->set_assoc($method, $args);
		}
		
		/** 
		* count magic 
		* Its special
		*/
		if(strtolower($method) == 'count') {
			$klass = get_called_class();
			if(count($args) == 0) {
				return call_user_func_array(array($klass, '_count'), array());
			}else{
				if(!NimbleAssociation::exists(self::class_name(), 'has_many', $args[0])) {
					throw new NimbleRecordException('Association does not exsist');
				}
				$class = Inflector::classify($args[0]);
				$key = NimbleAssociation::foreign_key(self::class_name());
				$conditions = array('conditions' => array($key => $this->id));
				if(isset($args[1])) {
					if(isset($args[1]['conditions'])) {
						$conditions['conditions'] = static::process_magic_condition_merge($conditions['conditions'], $args[1]['conditions']) ;
						unset($args[1]['conditions']);
					}
					$conditions = array_merge($conditions, $args[1]);
				}
				return call_user_func_array(array($class, '_count'), array($conditions));
			}
		}
		/**
		* See static::$math_method_map for included methods
		*/
		if(isset(static::$math_method_map[$method])) {
			$klass = get_called_class();
			if(empty($args) || count($args) < 2) {
				throw new NimbleRecordException('You need to pass an association name and column');
			}
			if(!NimbleAssociation::exists(self::class_name(), 'has_many', $args[0])) {
				throw new nimbleRecordException('Association does not exist');
			}
			$class = Inflector::classify($args[0]);
			$key = NimbleAssociation::foreign_key(self::class_name());
			$conditions = array('conditions' => array($key => $this->id), 'column' => $args[1]);
			if(isset($args[2])) {
				if(isset($args[2]['conditions'])) {
					$conditions['conditions'] = static::process_magic_condition_merge($conditions['conditions'], $args[2]['conditions']);
					unset($args[2]['conditions']);
				}
				$conditions = array_merge($conditions, $args[2]);
			}
			return call_user_func_array(array($class, static::$math_method_map[$method]), array($conditions));
		}
		
		/**
		* See static::$magic_method_map for included methods
		*/
		if(isset(static::$magic_method_map[$method])) {
			$klass = get_called_class();
			if(empty($args)) {
				$args = array($this->id);
			}
			call_user_func_array(array($klass, static::$magic_method_map[$method]), $args);
		}
		
		if(array_include($method, static::columns())) {
			$this->row[$method] = $args;
		}
		if(strpos($method, 'uniqueness_of') !==false) {
			$args[1]['class'] = get_class($this);
			$args[1]['instance'] = $this;
		}
		if(preg_match('/^validates_([0-9a-z_]+)$/', $method, $matches)) {
			$klass_method = $matches[1];
			if(method_exists('NimbleValidation', $klass_method)) {
				if(!is_array($args[0]) && is_string($args[0])) {
					$args[0] = array($args[0]);
				}
				foreach($args[0] as $column) {
           if(!isset($this->row[$column])) {
             $value = '';
           }else{
             $value = $this->row[$column];
           }
					$argss = array('column_name' => $column, 'value' => $value);
					if(isset($args[1]) && !empty($args[1])) {
						$argss = array_merge($argss, $args[1]);
					}
					$return = call_user_func_array(array('NimbleValidation', $klass_method), array($argss));
					$this->process_error_return($return);
				}
			}
		}	
	}
	
	public static function __callStatic($method, $args) {
		$matches = array();
		$klass = get_called_class();
		if(strtolower($method) == 'count') {
			return call_user_func_array(array($klass, '_count'), $args);
		}
		if(isset(static::$magic_method_map[$method])) {
			return call_user_func_array(array($klass, static::$magic_method_map[$method]), $args);
		}
		if(isset(static::$math_method_map[$method])) {
			return call_user_func_array(array($klass, static::$math_method_map[$method]), $args);
		}
		if(preg_match('/^find_by_([a-z0-9_]+)$/', $method, $matches)) {
			$where = static::build_where_for_magic_find($matches, $args);
			return call_user_func_array(array($klass, 'find'), array('first', array('conditions' => $where)));
		}
		if(preg_match('/^find_all_by_([a-z0-9_]+)$/', $method, $matches)) {
			$where = static::build_where_for_magic_find($matches, $args);
			return call_user_func_array(array($klass, 'find'), array('all', array('conditions' => $where)));
		}
	}


	private static function build_where_for_magic_find($matches, $args) {
		$method_called = array_shift($matches);
		$i = 0;
		$where = array();
		$cols = isset($matches[0]) ? explode('_and_', $matches[0]) : $matches;
		
		foreach($cols as $column) {
			if(array_include($column, static::columns())) {
				$col = self::sanatize_input_array($column);
				$val = self::sanatize_input_array($args[$i]);
				$where[$col] = $val;
				$i++;
			}
			return $where;
		}
	}

	
	public function __isset($name) {
		return isset($this->row) && isset($this->row[$name]);
	}
	

	//@codeCoverageIgnoreStart 
	public function associations() {}
	//@codeCoverageIgnoreEnd

	public function to_xml($include_head = true, $options = array()) {
		$xw = new xmlWriter();
		$xw->openMemory();

		if ($include_head){
			$xw->startDocument('1.0','UTF-8');
			$xw->startElement(strtolower(static::class_name()));
		}
		foreach($this->row as $key => $value) {
		$xw->writeElement($key, $value);
		}	
		if ($include_head){
			$xw->endElement(); 
		}
		return $xw->outputMemory(true);
	}
	
	public function to_json($options = array()) {
		return json_encode($this->row);
	}
	
	
}

?>
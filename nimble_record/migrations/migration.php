<?php
	require_once(dirname(__FILE__) . '/lib/create_table.php');
	require_once(dirname(__FILE__) . '/lib/alter_table.php');
	require_once(dirname(__FILE__) . '/../base.php');

	class Migration {
	
		static $show_sql = true;
		/**
		* Mysql types
		*/
		public static $NATIVE_DATABASE_TYPES = array(
        	'primary_key' => array('name' => NULL, 'sql' => "int(11) DEFAULT NULL auto_increment PRIMARY KEY"),
        	'string'      => array('name' => "varchar", 'limit' => 255),
        	'text'        => array('name' => "text"),
        	'integer'     => array('name' => "int", 'limit' => 11),
        	'float'       => array('name' => "float"),
        	'decimal'     => array('name' => "decimal"),
       	 	'datetime'    => array('name' => "datetime"),
        	'timestamp'   => array('name' => "datetime"),
        	'time'        => array('name' => "time"),
        	'date'        => array('name' => "date"),
        	'binary'      => array('name' => "blob"),
        	'boolean'     => array('name' => "tinyint", 'limit' => 1 )
			);
		/**
		* Creates a database
		* @param string $database_name
		* @param string $charset - default utf8
		*/
		public static function create_database($database_name, $charset = 'utf8') {
			return static::execute("CREATE DATABASE `" . $database_name . "` DEFAULT CHARACTER SET `" . $charset . "`");
		}
		
		/**
		* Drops a database
		* @param string $database_name
		*/
		public static function drop_database($database_name) {
			return static::execute("DROP DATABASE IF EXISTS `" . $database_name . "`");
		}
		/**
		* Creates a create_table object for creating a table
		* @param string $table_name
		* @param array $options
		*/
		public function create_table($table_name, $options = array()) {
			return new CreateTable($table_name, $options, $this);
		}
		/**
		* Creates a alter_table object for altering a table
		* @param string $table_name
		* @param array $options
		*/
		public function alter_table($table_name, $options = array()) {
			return new AlterTable($table_name, $options, $this);
		}
		/**
			* Creates a join table for has_and_belongs_to_many relationships
			* @param string $model_one
			* @param string $model_two
			*/
		public function create_join_table($model_one, $model_two) {
			$table_name = NimbleAssociation::generate_join_table_name(array($model_one, $model_two));
			$table = $this->create_table($table_name, array('id' => false));
				$table->references($model_one);
				$table->references($model_two);
			$table->go();
		}
		/**
		* Drops a table from the database
		* @param string $table_name
		*/
		public function drop_table($table_name) {
			return static::execute('DROP TABLE IF EXISTS ' . self::quote_table_name($table_name));
		}
		
		/**
		* Runs the current migration object
		* @param boolean $down - default false
		*/
		public function run($down = false) {
			if($down) {
				$this->down();
			}else{
				$this->up();
			}
		}
		
		/**
		* Wrapper method to execute sql through the databse adapter
		* @param string $sql
		*/
		public static function execute($sql) {
			if(static::$show_sql) {
				echo $sql . "\n\n";
			}
			$query = NimbleRecord::execute($sql, true);
			return $query;
		}
		/**
		* @see AbstractAdapter->type_to_sql
		*/
		public static function type_to_sql() {
			$args = func_get_args();
			return call_user_func_array(array(NimbleRecord::$adapter, 'type_to_sql'), $args);
		}
		/**
		* Adds column options to sql 
		* @param string $sql - sql string to addend options
		* @param array $options
		* @param boolean $alter - is this an alter table call
		*/
		public static function add_column_options($sql, $options = array(), $alter = false) {
			$sql .= isset($options['default']) ? ' DEFAULT ' . $options['default'] : '';
			$sql .= (isset($options['null']) && !$options['null']) ? ' NOT NULL' : '';
			$sql .= ($alter && isset($options['null']) && $options['null']) ? ' NULL' : '';
			return $sql;
		}
		/**
		* @see AbstractAdapter->quote_column_name
		*/
		public static function quote_column_name($name) {
    	return NimbleRecord::$adapter->quote_column_name($name);
    }
 		/**
		* @see AbstractAdapter->quote_table_name
		*/
 		public static function quote_table_name($name) {
    	return NimbleRecord::$adapter->quote_table_name($name);    	
		}
		/**
		* @see NimbleRecord::columns
		*/
		public static function columns($table) {
			return NimbleRecord::load_columns($table);
		}
		/**
		* Pulls the current data for a column
		*/
		public static function columns_data($table, $column) {
			$columns = static::columns($table);
			foreach($columns as $_column) {
				if(strtolower($_column['Field']) == strtolower($column)) {
					return $_column;
				}
			}
			throw new Exception("No Column Found");
		}
		
		public function up() {}
		public function down() {}
		
		
	}

?>
<?php
	class CreateTable {
	
	
		/**
		* You should never need to call this directly instead use Migration->create_table('foo');
		* @param string $table_name
		* @param array $options
		* @param Migration $migration
		*/
		public function __construct($table_name, $options, $migration) {
			$this->passed_options = $options;
			$this->migration = $migration;
			$this->table_name = $table_name;
			$this->quoted_table_name = Migration::quote_table_name($table_name);
			$this->sql = 'CREATE TABLE ' . $this->quoted_table_name;
			$this->columns = array();
			$this->options = "ENGINE=InnoDB DEFAULT CHARSET=utf8";
			$this->other = array();
			
			if(isset($this->passed_options['id']) && !$this->passed_options['id']) {
				//do nothing
			}else{
				array_push($this->columns, "id int(11) DEFAULT NULL auto_increment PRIMARY KEY");
			}
		}
		
		/**
		* Adds a column to the table object
		* @param string $column_name
		* @param string $type
		* @param array $options
		*/
		public function add_column($column_name, $type, $options = array()) {
			$defaults = array('limit' => NULL, 'precision' => NULL, 'scale' => NULL);
			$options = array_merge($defaults, $options);
			$add_column_sql = Migration::quote_column_name($column_name) . ' ' . Migration::type_to_sql($type, $options['limit'], $options['precision'], $options['scale']);
			$add_column_sql = Migration::add_column_options($add_column_sql, $options);
			array_push($this->columns, $add_column_sql);
		}
		
		/**
		* Adds an index on the column name passed in
		* @param string $column_name
		* @param array $options
		*/
		public function add_index($column_name, $options = array()) {
			$columns = (is_array($column_name)) ? $column_name : array($column_name);
			$index_name = self::index_name($this->table_name, $columns);
			$quoted_columns = self::quote_column_names($columns);
			$index_type = (isset($options['unique']) && $options['unique']) ? ' UNIQUE' : '';
			array_push($this->other, 'CREATE' . $index_type . ' INDEX ' . Migration::quote_column_name($index_name) . ' ON ' . $this->quoted_table_name . ' (' . join(',', $quoted_columns) .')');
		}
		/**
		* Adds a foreign key
		* @param string $column_name
		* @param string $ref_table
		* @param array $options
		*/
		public function add_foreign_key($column_name, $ref_table, $ref_column, $options = array('on_update' => 'CASCADE', 'on_delete' => 'CASCADE')) {
			$name = 'fk_' . $this->table_name . '_' . $column_name;
			$fk = 'CONSTRAINT ' . $name . ' FOREIGN KEY (' . Migration::quote_column_name($column_name) . ') REFERENCES ' . Migration::quote_table_name($ref_table) . '(' .  Migration::quote_column_name($ref_column) . ')';
			$fk .= ' ON UPDATE ' . $options['on_update'] . ' ON DELETE ' . $options['on_delete'];
			array_push($this->other, 'ALTER TABLE ' . $this->quoted_table_name . ' ADD ' . $fk);
		}
		
		/**
		* @see add_index
		*/
		public function index() {
			$args = func_get_args();
			call_user_func_array(array($this, 'add_index'), $args);
		}
		/**
		* Adds a string column to the table object
		* @param string $column_name
		* @param array $options
		*/
		public function string($column_name, $options = array()) {
			$this->add_column($column_name, 'string', $options);
		}
		/**
		* Adds an integer column to the table object
		* @param string $column_name
		* @param array $options
		*/
		public function integer($column_name, $options = array()) {
			$this->add_column($column_name, 'integer', $options);
		}
		/**
		* Adds a boolean object to the table object
		* @param string $column_name
		* @param array $options
		*/
		public function boolean($column_name, $options = array()) {
			$this->add_column($column_name, 'boolean', $options);
		}
		/**
		* Adds a text column to the table object
		* @param string $column_name
		* @param array $options
		*/
		public function text($column_name, $options = array()) {
			$this->add_column($column_name, 'text', $options);
		}
		/**
		* Adds a created_at and updated_at column to the table object
		* This column is then auto updated by the ORM when ever a create or update occures
		*/
		public function timestamps() {
			$this->add_column('created_at', 'datetime');
			$this->add_column('updated_at', 'datetime');
		}
		/**
		* @see datetime
		*/
		public function timestamp($column_name, $options = array()) {
			$this->add_column($column_name, 'datetime', $options);
		}
		/**
		* Add a datetime column to the table object
		* @param string $column_name
		* @param array $options
		*/
		public function datetime($column_name, $options = array()) {
			$this->add_column($column_name, 'datetime', $options);
		}
		/**
		* Sets up a foreign key relation shot to the specified reference table
		* @param string $ref_table
		* @param string $ref_column
		* @param array $options
		*/
		public function references($ref_table, $ref_column = 'id', $options = array('on_update' => 'CASCADE', 'on_delete' => 'CASCADE')) {
			$column_name = Inflector::singularize($ref_table) . '_' . Inflector::singularize($ref_column);
			$ref_table = Inflector::pluralize($ref_table);
			$this->add_column($column_name, 'integer', array('null' => false));
			$this->add_index($column_name);
			$this->add_foreign_key($column_name, $ref_table, $ref_column, $options);
		}
		/**
		* @see references
		*/
		public function belongs_to() {
			$args = func_get_args();
			call_user_func_array(array($this, 'references'), $args);
		}
		
		/**
		* Sets up a polymorphic column structure within the table object
		* @param string $column_prefix
		* Creates columns and indexs on:
		* <ul>
		*		<li>$column_prefix + _id</li>
		*		<li>$column_prefix + _type</li>
		* </ul>
		*/
		public function polymorphic($column_prefix) {
			$this->add_column($column_prefix . '_id', 'integer', array('null' => false));
			$this->add_column($column_prefix . '_type', 'string', array('null' => false));
			$this->add_index($column_prefix . '_id');
			$this->add_index($column_prefix . '_type');
		}
		/**
		* Runs the table migration converting the table object into runable sql
		*/
		public function go() {
			(string) $sql = $this->sql;
			$sql .= ' (' . join(", ", $this->columns) . ')';
			$sql .= ' ' . $this->options;
			$out = array(Migration::execute($sql));
			foreach($this->other as $other){
				array_push($out, Migration::execute($other));
			}
			return $out;
		}
		
		/**
		* Quotes each column name
		* @see Migration::quote_column_name
		*/
		public static function quote_column_names($columns) {
			(array) $out = array();
			foreach($columns as $column){
				array_push($out, Migration::quote_column_name($column));
			}
			return $out;
		}
		/**
		* Generates and index name
		* @param string $table_name
		* @param mixed $columns
		*/
		public static function index_name($table_name, $columns) {
			return "index_" . $table_name . "_on_" . join("_and_", $columns);
		}
		
	
	}

?>
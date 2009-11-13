<?php

	class NimbleAssociation {
		public static $types = array('has_many', 'has_many_polymorphic', 
																 'belongs_to', 'has_and_belongs_to_many', 
																 'belongs_to_polymorphic');	
		public static $associations = array();

		
		//self::find_has_many($class, $association);
		public static function __callStatic($method, $args) {
			$matches = array();
			$regex = '/^find_(' . implode('|', static::$types) . ')$/';
			if(preg_match($regex, $method, $matches)){
					return call_user_func_array(array('self', $matches[1] . '_find'), 
																			array($args[0], $args[1]));
																		
			}else{
				throw new NimbleRecordException('Association type does not exist');
			}
		}
		
		
		public static function exists($class, $key, $association_name) {
				if(!isset(static::$associations[$class])) {return false;}
				$associations = static::$associations[$class];
				if (isset($associations[$key])) {
					return array_include($association_name, $associations[$key]);
				}else{
					return false;
				}
			}
		
		public static function find_type($class, $association) {
			$class = is_string($class) ? $class : get_class($class);
			if(!isset(static::$associations[$class])) {return false;}
			foreach(static::$associations[$class] as $assoc => $assocs) {
				if(array_include($association, $assocs)) {
					return (string) $assoc;
				}
			}
			return false;
		}
		
		public static function foreign_key($class) {
			$class = is_string($class) ? $class : get_class($class);
			$model = Inflector::classify($class);
			return Inflector::foreignKey(Inflector::singularize($class), $model::$foreign_key_suffix);
		}

		public static function table_name($name) {
			$name = static::model($name);
			$table = call_user_func(array('NimbleRecord', 'table_name'), $name);
			var_dump($table);
			$table = strtolower($table);
			return $table;
		}

		public static function model($name) {
			return Inflector::classify($name);
		}

		protected static function has_many_find($class, $name) {
			$key = static::foreign_key(get_class($class));
			$id = $class->row[NimbleRecord::$primary_key_field];
			$conditions = array($key => $id);
			$model = static::model($name);
			$find_array = call_user_func("$model::find_all", 
			  array('conditions' => $conditions)
			);
			return $find_array;
		}


		protected static function has_many_polymorphic_find($class, $name) {
			$model = static::model($name);
			$singular = Inflector::singularize($name);
			$polymorphic_column_type = $singular . 'able_type';
			$polymorphic_column_id =  $singular . 'able_id';
			$class = strtolower($class);
			$conditions = $polymorphic_column_type . " = '$class' AND " . $polymorphic_column_id . " = '{static::id}'";
			return call_user_func("$model::find_all", array('conditions' => $conditions));
		}

		protected static function belongs_to_find($class, $name) {
			$primary_key_value = $class->row[$name . '_id'];
			$model = static::model($name);
			return call_user_func("$model::find", $primary_key_value);
		}
		
	}

?>
<?php

	class NimbleAssociation {
		public static $types = array('has_many', 'has_one', 
																 'belongs_to', 'has_and_belongs_to_many');	
		public static $associations = array();

		
		//self::find_has_many($class, $association);
		public static function __callStatic($method, $args) {
			$matches = array();
			$regex = '/^find_(' . implode('|', static::$types) . ')$/';
			if(preg_match($regex, $method, $matches)){
					return call_user_func_array(array('self', '_' . $matches[1]), 
																			array($args[0], $args[1]));
																		
			}else{
				throw new NimbleRecordException('Association type does not exist');
			}
		}
		
		
		private static function _has_many($class, $name) {
			$class_name = is_string($class) ? Inflector::Classify($class) : Inflector::Classify(get_class($class));
			$obj = NimbleAssociation::$associations[$class_name]['has_many'][$name];
			if(isset($obj->as) && !empty($obj->as)) {
				return static::has_many_polymorphic_find($class, $name);
			}
			if(isset($obj->through) && !empty($obj->through)) {
				return array();
			}
			return static::has_many_find($class, $name);
		}
		
		private static function _has_one() {
			
		}
		
		private static function _belongs_to($class, $name) {
			$class_name = is_string($class) ? Inflector::Classify($class) : Inflector::Classify(get_class($class));
			$obj = NimbleAssociation::$associations[$class_name]['belongs_to'][$name];
			if(isset($obj->polymorphic) && $obj->polymorphic === true) {
				return static::belongs_to_polymorphic_find($class, $name);
			}
			return static::belongs_to_find($class, $name);
		}
		
		private static function _has_and_belongs_to_many() {
			
		}
		
		public static function exists($class, $key, $association_name) {
				if(!isset(static::$associations[$class])) {return false;}
				$associations = static::$associations[$class];
				if (isset($associations[$key])) {
					return isset($associations[$key][$association_name]);
				}else{
					return false;
				}
			}
		
		public static function find_type($class, $association) {
			$class = is_string($class) ? $class : get_class($class);
			if(!isset(static::$associations[$class])) {return false;}
			foreach(static::$associations[$class] as $assoc => $assocs) {
				if(isset(static::$associations[$class][$assoc][$association])) {
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
			$id = $class->row[NimbleRecord::$primary_key_field];
			$model = static::model($name);
			$singular = Inflector::singularize($name);
			$polymorphic_column_type = $singular . 'able_type';
			$polymorphic_column_id =  $singular . 'able_id';
			$class = strtolower(get_class($class));
			$conditions = $polymorphic_column_type . " = '$class' AND " . $polymorphic_column_id . " = '" . $id . "'";
			return call_user_func("$model::find_all", array('conditions' => $conditions));
		}
		
		
		protected static function belongs_to_polymorphic_find($class, $name) {
			$singular = Inflector::singularize(get_class($class));
			$polymorphic_column_type = strtolower($singular) . 'able_type';
			$model = static::model($class->row[$polymorphic_column_type]);
			$polymorphic_column_id =  strtolower($singular) . 'able_id';
			$id = $class->row[$polymorphic_column_id];
			return call_user_func_array(array($model, 'find'), array($id));
		}

		protected static function belongs_to_find($class, $name) {
			$primary_key_value = $class->row[$name . '_id'];
			$model = static::model($name);
			return call_user_func("$model::find", $primary_key_value);
		}
		
	}
	
	
	class NimbleAssociationBuilder {
		
		static $options = 		array('has_many' => 								array('through', 'foreign_key', 'class_name', 
																																		'conditions', 'order', 'foreign_key', 'include', 'as'),
																
																'belongs_to' => 							array('class_name', 'conditions', 'foreign_key', 'include', 
																																		'polymorphic'),
																'has_and_belongs_to_many' => array('class_name', 'join_table', 'foreign_key',
																																		'association_foreign_key', 'conditions', 'order'),
																'has_one' =>								 array('class_name', 'conditions', 'order', 'foreign_key',
																 																		'include', 'as', 'through')
																);
		
														
		public function __construct($class, $type, $arg) {
			if(!array_include($type, NimbleAssociation::$types)) {
				throw new NimbleRecordException('Invalid Association Type: ' . $type);
			}
			$this->type = $type;
			$this->class = is_string($class) ? Inflector::classify($class) : Inflector::classify(get_class($class));
			$this->name = $arg;
			foreach(static::$options[$this->type] as $var) {
				$this->{$var} = NULL;
			}
			return $this;
		}
		
		public function __call($method, $args) {
			if(array_include($method, static::$options[$this->type])) {
				$this->{$method} = reset($args);
				return $this;
			}else{
				throw new NimbleRecordException('Property does not exsist on this association type only: ' 
																				. implode(', ', static::$options[$this->type]));
			}
		}
	}
	




?>
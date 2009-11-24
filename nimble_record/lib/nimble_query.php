<?php

	class NimbleQuery {
		const SELECT = 'SELECT';
		const INSERT = 'INSERT';
		const UPDATE = 'UPDATE';
		const DELETE = 'DELETE';
		
		var $select = '*';
		var $type = self::SELECT;
		var $out = array();
		static $map = array(self::SELECT => array('SELECT', 'FROM', 'JOIN', 'WHERE', 'GROUP BY', 'ORDER', 'LIMIT'), 
												self::INSERT => array('INSERT INTO', 'VALUES'), 
												self::UPDATE => array('UPDATE', 'SET', 'WHERE'), 
												self::DELETE => array('DELETE', 'FROM', 'WHERE')
											);
		
		static $required = array(self::SELECT => array('from'),
														 self::INSERT => array('insert_into', 'columns', 'values'),
														 self::UPDATE => array('update', 'columns', 'values'),
														 self::DELETE => array('from')
														);
		
		
		public function __construct($type = self::SELECT) {
			$this->type = $type;
			foreach(static::$map[self::SELECT] as $var) {
				$var = Inflector::underscore(strtolower($var));
				$this->{$var} = isset($this->{$var}) ? $this->{$var} : '';
			}
		}
		
		private function check_vars() {
			foreach(static::$required[$this->type] as $var) {
				if(!isset($this->{$var}) || empty($this->{$var})) {
					throw new NimbleRecordException('required var ' . $var . ' is not set');
				}
			}
		}
		
		
		private function build_select() {
			$this->check_vars();
			foreach(static::$map[self::SELECT] as $part) {
				$var = Inflector::underscore(strtolower($part));
				if(!empty($this->{$var})) {
					if($part === 'JOIN') {
						$this->out[] = $this->{$var};
					}else{
						$this->out[] = implode(" ", array($part, $this->{$var}));	
					}
				}
			}
		}
		
		private function build_insert() {
			$this->check_vars();
			foreach(static::$map[self::INSERT] as $part) {
				$var = Inflector::underscore(strtolower($part));
				if(!empty($this->{$var})) {
					if($part === 'VALUES') {
						$cols = '(' . implode(", ", $this->columns) . ')';
						$values = "('" . implode("', '", $this->{$var}) . "')";
						$this->out[] = implode(" ", array($cols, $part, $values));	
					}else{
						$this->out[] = implode(" ", array($part, $this->{$var}));	
					}
				}
			}
		}
		
		private function build_update() {
			$this->check_vars();
			foreach(static::$map[self::UPDATE] as $part) {
				$var = Inflector::underscore(strtolower($part));
				if($part === 'SET') {
						if($col_count = count($this->columns) != count($this->values)) {
							$this->values = array_pad($this->values, $col_count, 'NULL'); 
						}
						$i=0;
						$out = array();
						$this->out[] = $part;
						foreach($this->columns as $col) {
							$out[] = '`' . $col . "` = '" . $this->values[$i] . "'";  
							$i++;
						}
						$this->out[] = implode(", ", $out);
					}
					if(!empty($this->{$var})) {{
							$this->out[] = implode(" ", array($part, $this->{$var}));	
					}
				}
			}
		}
		
		private function build_delete() {
			$this->check_vars();
			$this->out[] = self::DELETE;
			foreach(static::$map[self::DELETE] as $part) {
				$var = Inflector::underscore(strtolower($part));
				if(!empty($this->{$var})) {
						$this->out[] = implode(" ", array($part, $this->{$var}));	
				}
			}
		}

		public function build() {
			$method = 'build_' . strtolower($this->type);
			if(method_exists($this, $method)) {
				call_user_func(array($this, $method));
				return implode(" ", $this->out);
			}else{
				throw new NimbleRecordException('Bad Query Type');
			}
		}
		
		
		
		public static function in($column, array $ids) {
			return $column . ' IN(' . implode(',', $ids) . ')';
		}
		
		
	}
	
	
	
	
	

?>
<?php

	class NimbleResult implements ArrayAccess, Countable, Iterator {
	 	private $array = array();

		public function __construct($array, $options = array())
    {
    	if(is_array($array)) {
      	$this->array = $array;
				$this->length = $this->count();
       }

			if(!empty($options)) {
				foreach($options as $key => $value) {
					$this->$key = $value;
				}
			}

    }
		/**
			* Checks to see if an object is in the array
			* helpful for testing
			* @param NimbleRecord $obj
			* @return boolean
			*/
		public function includes($obj) {
			return (array_search($obj, $this->array) !== false);
		}
		
		
		public function clear() {
			if(isset($this->key)) {
				NimbleRecord::remove_from_cache($this->key);
			}
			unset($this->array);
			unset($this->length);
			unset($this);
		}

		public function first() {
			return $this->array[0];
		}
		
		public function last() {
		  return $this->array[count($this->array) - 1];
		}

		public function to_xml($options = array()) {
			return NimbleSerializer::XML($this, $options);
		}
		
		public function to_json($options = array()) {
			return NimbleSerializer::JSON($this, $options);
		}
		
	
		public function __toString() {
			return '';
		}


		public function keys(){
			return array_keys($this->array);
		}
	
		public function columns(){
			if($this->length > 0) {
				return array_keys($this->first()->row);
			}
			return array();
		}

    public function rewind() {
        reset($this->array);
    }

    public function current() {
        $array = current($this->array);
        return $array;
    }

    public function key() {
        $array = key($this->array);
        return $array;
    }

    public function next() {
        $array = next($this->array);
        return $array;
    }

    public function valid() {
        $array = $this->current() !== false;
        return $array;
    }

		public function count() {
			return count($this->array);
		}
		
		public function length() {
			return $this->length;
		}
		
		public function offsetSet($offset, $value) {
        $this->array[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

		
	}

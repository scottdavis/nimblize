<?php

	class NimbleResult implements Countable, Iterator {
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

		public function to_xml($options = array()) {
			$klass = $this->array[0]->class_name();
			$plural = Inflector::pluralize($klass);
			$xw = new xmlWriter();
			$xw->openMemory();
			$xw->startDocument('1.0','UTF-8');
			$xw->startElement(strtolower($plural)); 
				foreach($this->array as $value) {
					$xw->startElement(strtolower($klass));
					$xw->writeRaw($value->to_xml(false));
					$xw->endElement();
				}
			$xw->endElement(); 
			$xw->endDtd();
			return $xw->outputMemory(true);
		}
		
		public function to_json($options = array()) {
			$out = array();
			
			foreach($this->array as $obj) {
				$out[] = $obj->row;
			}
			
			$json = json_encode($out);
			return $json;
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

		
	}

?>
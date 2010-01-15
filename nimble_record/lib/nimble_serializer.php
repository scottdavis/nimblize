<?php

class NimbleSerializer {
	const XML = 'XML';
	const JSON = 'JSON';
	var $options = array('only' => NULL, 'except' => NULL, 'include' => NULL, 'lamda' => NULL);
	var $collection = NULL;
	var $single = false;
	var $type = NULL;
	/**
		* This Class handles all record serializations
		* @param mixed $collecton - takes a NimbleResult or a NimbleRecord
		* @param string $type - XML | JSON
		* @param array $options 
		* Allowed Options
		*	<ol>
		*  <li>only</li>
		*  <li>except</li>
		*  <li>lamda - closure</li>
		* </ol>
		*/
	public function __construct($collection, $type = self::XML, $options = array()) {
		$this->options = array_merge($this->options, $options);
		$this->collection = $collection;
		$this->type = $type;
		if(is_a($collection, 'NimbleRecord')) {
			$this->single = true;
		}
	}
	
	/**
		* Serializes an NimbleResult Collection to XML
		* @return string XML
		*/
	public function build_collection_xml() {
		$x = new xmlWriter();
		$x->openMemory();
		$x->startDocument('1.0','UTF-8');
		if($this->single) {
			$x->startElement(strtolower(get_class($this->collection)));
			$x->writeRaw($this->build_record_xml($this->collection));
		}else{
			$x->startElement(Inflector::pluralize(strtolower(get_class($this->collection->first()))));
			foreach($this->collection as $obj) {
				$x->startElement(strtolower(get_class($this->collection->first())));
				$x->writeRaw($this->build_record_xml($obj));
				$x->endElement(); 
			}
		}
		$x->endElement(); 
		$xml = $x->outputMemory(true);
		unset($x);
		return $xml;
	}
	
	/**
		* Serializes an NimbleResult Collection to JSON
		* @return string JSON
		*/
	public function build_collection_json() {
		if($this->single) {
			$out = $this->build_record_json($this->collection);
		}else{
			$out = array();
			foreach($this->collection as $obj) {
				$out[] = $this->build_record_json($obj);
			}
		}
		return json_encode($out);
	}
	/**
		* Serializes an NimbleRecord to XML
		* @param NimbleRecord $obj
		* @return string XML
		*/
	public function build_record_xml($obj) {
		$xw = new xmlWriter();
		$xw->openMemory();
		$keys = $this->prepair_keys(array_keys($obj->row));
		foreach($keys as $key) {
			$value = $obj->row[$key];
			if(is_callable($this->options['lamda'])) {
				list($key, $value) = $this->options['lamda']($key, $value);
			}
			$xw->writeElement($key, $value);
		}		
		$xml = $xw->outputMemory(true);
		unset($xw);
		return $xml;
	}
	/**
		* Serializes an NimbleRecord to JSON
		* @param NimbleRecord $obj
		* @return string JSON
		*/
	public function build_record_json($obj) {
		$keys = $this->prepair_keys(array_keys($obj->row));
		$out = array();
		foreach($keys as $key) {
			$value = $obj->row[$key];
			if(is_callable($this->options['lamda'])) {
				list($key, $value) = $this->options['lamda']($key, $value);
			}
			$out[$key] = $value;
		}
		return $out;
	}
	/**
		* Handles the only and except options
		* @param array $keys - keys from a nimble record
		* @return array - keys to use
		*/
	public function prepair_keys(array $keys) {
		if(!is_null($this->options['except'])) {
			$keys = array_diff($keys, $this->options['except']);
		}
		if(!is_null($this->options['only'])) {
			$keys = $this->options['only'];
		}
		return $keys;
	}
	
	/**
		* Processes the serialization based on $this->type
		* @return string
		*/
	public function serialize() {
		switch($this->type) {
			case self::XML:
				return $this->build_collection_xml();
			break;
			case self::JSON:
				return $this->build_collection_json();
			break;
			default:
				return $this->build_collection_xml();
			break;
		}
	}
	
	
	/**
		* Enables serialization to act as a static method call for nice clear one liners
		* @return string
		*/
	public static function __callStatic($method, $args) {
		if(count($args) < 1) {
			throw new Exception("invaid arguments");
		}
		if(!isset($args[1]) || empty($args[1])) {
			$args[1] = array();
		}
		$class = new self($args[0], strtoupper($method), $args[1]);
		return $class->serialize();
	}
		
}



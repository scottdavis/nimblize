<?php

class SmartTable {
	public function __construct($collection, $cols=2, $tr_class_name='', $table_options = array()) {
		$this->collection = $collection;
		$this->cols = $cols;
		$this->tr_class_name = $tr_class_name;
		$this->td = '';
		$this->table_options = $table_options;
		$this->content = '';
		$this->callback = NULL;
	}
	
	
	private function process_td($obj) {
		preg_match_all('/{[a-z0-9_\(\)]+}/', $this->td, $matches);
		$called = array();
		if(isset($matches[0]) && empty($matches[0])) {return $this->td;}
		foreach($matches[0] as $match) {
			$var = str_replace(array('}', '{'), '', $match);
			if(strpos($match, '()') === false) {
				$called[$match] = $obj->{$var};
			}else{
				$var = str_replace(array('()'), '', $var);
				$called[$match] = call_user_func(array($obj, $var));
			}
		}
		return str_replace(array_keys($called), array_values($called), $this->td);
	}
	
	public function build() {
		if(empty($this->callback) && empty($this->td)) {
			throw new NimbleException('You must set the td or callback variable');
		}
		if(empty($this->collection) || count($this->collection) == 0) {
			return "";
		}
		$i = 0;
		foreach($this->collection as $obj) {
			if($i % $this->cols == 0) {
				if($i != 0) {
					$this->content .= '</tr>';
				}
				$this->content .= "<tr class=\"{$this->tr_class_name}\">";
			}
			if(!empty($this->callback)) {
				$var = $this->callback;
				$this->content .= $var($obj);
			}else{
				$this->content .= $this->process_td($obj);
			}
			$i++;
		}
		$remainder = count($this->collection) % $this->cols;
		$remainder = $this->cols - $remainder;
		for($i=0;$i<$remainder;$i++) {
			$this->content .= "<td class='empty'>&nbsp;</td>";
		}
		$this->content .= "</tr>";
		$this->content = TagHelper::content_tag('tbody', $this->content);
		$this->content = TagHelper::content_tag('table', $this->content, $this->table_options);
		unset($this->callback);
		unset($var);
		return $this->content;
	}
}
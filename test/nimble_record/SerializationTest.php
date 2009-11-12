<?php
require_once(dirname(__FILE__) . '/config.php');

	class SerializationTest extends PHPUnit_Framework_TestCase { 
		
		public function testToXml() {
			$user = User::find('first');
			$xml = $user->to_xml();
			
			$simple_xml = simplexml_load_string($xml);
			$xml_as_array = (array) $simple_xml;
			$keys = array_keys($xml_as_array);
			foreach($keys as $key) {
				if(is_a($xml_as_array[$key], 'SimpleXMLElement')) {
					$this->assertTrue(is_a($xml_as_array[$key], 'SimpleXMLElement'));
					continue;
				}
				$this->assertEquals($xml_as_array[$key], $user->{$key});
			}


			
		}
		
	}

?>
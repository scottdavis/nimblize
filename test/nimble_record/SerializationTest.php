<?php
require_once(dirname(__FILE__) . '/config.php');

	class SerializationTest extends PHPUnit_Framework_TestCase { 
		
		public function setUp() {
			NimbleRecord::start_transaction();
		}
		
		public function tearDown() {
			NimbleRecord::rollback_transaction();
		}
		
		public function testToXml() {
			$user = User::find(1);
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
		
		
		public function testMassToXml() {
			$users = User::find('all');
			$xml = $users->to_xml();
			$xml = simplexml_load_string($xml);
			$out = array();
			foreach($xml->user as $user) {
				$out[] = $user;
			}
			$keys = array_keys($users->first()->row);
			$i=0;
			foreach($users as $user) {
				$obj = $out[$i];
				foreach($keys as $key) {
					if(is_a($obj->{$key}, 'SimpleXMLElement')) {
						$this->assertTrue(is_a($obj->{$key}, 'SimpleXMLElement'));
						continue;
					}
					$this->assertEquals($user->{$key}, $obj->{$key});
				}
				$i++;
			}
			
		}
		
		
		public function testToJson() {
			$user = User::find(1);
			$json = $user->to_json();
			
			$decoded = json_decode($json);
			
			foreach($decoded as $key => $val) {
				$this->assertEquals($user->{$key}, $val);
			}
			
		}
		
		public function testMassToJson() {
			$users = User::find('all');
			$json = $users->to_json();
			$user = $users->first();
			$keys = array_keys($user->row);
			$json = json_decode($json);
			$this->assertEquals(count($json), $users->length);
			$i=0;
				foreach($users as $user) {
					$obj = $json[$i];
					foreach($keys as $key) {
						$this->assertEquals($user->{$key}, $obj->{$key});
					}
					$i++;
				}
		}
		
	}

?>
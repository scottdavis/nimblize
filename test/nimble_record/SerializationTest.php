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
		
		
		
		public function testCollectionToJSON() {
			$users = User::find_all();
			$out = NimbleSerializer::JSON($users, array('except' => array('updated_at', 'created_at')));
			$this->assertEquals($users->length, count(json_decode($out)));
			$user = $users->first();
			$json = json_decode($out);
			$keys = array_keys((array) $json[1]);
			$i=0;
				foreach($users as $user) {
					$obj = $json[$i];
					foreach($keys as $key) {
						$this->assertEquals($user->{$key}, $obj->{$key});
					}
					$i++;
				}
		}
		
		
		public function testSingleToJson() {
			$u = User::find('first');
			$out = NimbleSerializer::JSON($u, array('except' => array('updated_at', 'created_at')));
			$this->assertEquals('{"address":"","id":"1","last_name":"","my_int":"1","name":"names1"}', $out);
		}
		
		
		public function testOptionOnlyXml() {
			$class = new NimbleSerializer(array());
			$u = User::find('first');
			$class->options['only'] = array('name');
			$this->assertEquals('<name>names1</name>', $class->build_record_xml($u));
		}
		
		public function testOptionsExceptXml() {
			$u = User::find('first');
			$this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<user><address></address><id>1</id><last_name></last_name><my_int>1</my_int><name>names1</name></user>', NimbleSerializer::XML($u, array('except' => array('updated_at', 'created_at'))));
		}
		
		public function testCollectionToXML() {
			$users = User::find_all();
			$class = new NimbleSerializer($users, NimbleSerializer::XML, array('except' => array('updated_at', 'created_at')));
			$this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<users><user><address></address><id>1</id><last_name></last_name><my_int>1</my_int><name>names1</name></user><user><address></address><id>2</id><last_name></last_name><my_int>2</my_int><name>names2</name></user><user><address></address><id>3</id><last_name></last_name><my_int>3</my_int><name>names3</name></user><user><address></address><id>4</id><last_name></last_name><my_int>4</my_int><name>names4</name></user><user><address></address><id>5</id><last_name></last_name><my_int>5</my_int><name>names5</name></user><user><address></address><id>6</id><last_name></last_name><my_int>6</my_int><name>names6</name></user><user><address></address><id>7</id><last_name></last_name><my_int>7</my_int><name>names7</name></user><user><address></address><id>8</id><last_name></last_name><my_int>8</my_int><name>names8</name></user><user><address></address><id>9</id><last_name></last_name><my_int>9</my_int><name>names9</name></user><user><address></address><id>10</id><last_name></last_name><my_int>10</my_int><name>names10</name></user></users>', $class->serialize());
		}
		
		public function testCollectionWithCallStaticXML() {
			$users = User::find_all();
			$this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<users><user><address></address><id>1</id><last_name></last_name><my_int>1</my_int><name>names1</name></user><user><address></address><id>2</id><last_name></last_name><my_int>2</my_int><name>names2</name></user><user><address></address><id>3</id><last_name></last_name><my_int>3</my_int><name>names3</name></user><user><address></address><id>4</id><last_name></last_name><my_int>4</my_int><name>names4</name></user><user><address></address><id>5</id><last_name></last_name><my_int>5</my_int><name>names5</name></user><user><address></address><id>6</id><last_name></last_name><my_int>6</my_int><name>names6</name></user><user><address></address><id>7</id><last_name></last_name><my_int>7</my_int><name>names7</name></user><user><address></address><id>8</id><last_name></last_name><my_int>8</my_int><name>names8</name></user><user><address></address><id>9</id><last_name></last_name><my_int>9</my_int><name>names9</name></user><user><address></address><id>10</id><last_name></last_name><my_int>10</my_int><name>names10</name></user></users>', NimbleSerializer::XML($users, array('except' => array('updated_at', 'created_at'))));
		}
		
		
	}

?>
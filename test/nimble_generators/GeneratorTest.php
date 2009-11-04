<?php

require_once(dirname(__FILE__) . '/../../nimble_generators/lib/generator.php');

require_once('PHPUnit/Framework.php');
require_once('vfsStream/vfsStream.php');

class GeneratorTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		vfsStreamWrapper::register();
		$root = new vfsStreamDirectory('root');
		$root->addChild(new vfsStreamDirectory('template'));
		$root->addChild(new vfsStreamDirectory('target'));	
		vfsStreamWrapper::setRoot($root);	

		Generator::$template_path = vfsStream::url('root/template');
		Generator::$nimble_root = vfsStream::url('root');
	}
	
	function providerTestGenerateTemplate() {
		return array(
			array('old', false),
			array('new', true)
		);	
	}
	
	/**
	 * @covers Generator::__callStatic
	 * @dataProvider providerTestGenerateTemplate
	 */
	function testGenerateTemplate($name, $is_generated) {
		vfsStreamWrapper::getRoot()->getChild('template')->addChild(vfsStream::newFile('new.tmpl'));
		
		call_user_func("Generator::generate_${name}", vfsStream::url('root/target/made-it'));
		
		$this->assertEquals($is_generated, vfsStreamWrapper::getRoot()->hasChild('target/made-it'));
	}
	
	function testGenerateTest() {
		vfsStreamWrapper::getRoot()->addChild(new vfsStreamDirectory('test'));
		vfsStreamWrapper::getRoot()->getChild('test')->addChild(new vfsStreamDirectory('unit'));
		vfsStreamWrapper::getRoot()->getChild('template')->addChild(vfsStream::newFile('unit_test.tmpl')->withContent('{class_name}'));
		
		Generator::generate_test('unit', 'Unit');
		
		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test/unit/UnitTest.php'));
		$this->assertEquals("Unit", file_get_contents(vfsStream::url('test/unit/UnitTest.php')));
		
	}
}

?>
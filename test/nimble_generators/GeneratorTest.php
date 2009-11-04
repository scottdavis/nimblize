<?php

require_once(dirname(__FILE__) . '/../../nimble_generators/lib/generator.php');

require_once('PHPUnit/Framework.php');
require_once('vfsStream/vfsStream.php');

class GeneratorTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		vfsStreamWrapper::register();
		$root = new vfsStreamDirectory('root');
		$root->addChild(new vfsStreamDirectory('template'));
		$root->getChild('template')->addChild(vfsStream::newFile('test.tmpl'));
		$root->addChild(new vfsStreamDirectory('target'));	
		vfsStreamWrapper::setRoot($root);	

		Generator::$template_path = vfsStream::url('root/template');
	}
	
	function providerTestGenerateTemplate() {
		return array(
			array('old', false),
			array('test', true)
		);	
	}
	
	/**
	 * @covers Generator::__callStatic
	 * @dataProvider providerTestGenerateTemplate
	 */
	function testGenerateTemplate($name, $is_generated) {
		call_user_func("Generator::generate_${name}", vfsStream::url('root/target/made-it'));
		
		$this->assertEquals($is_generated, vfsStreamWrapper::getRoot()->hasChild('target/made-it'));
	}
}

?>
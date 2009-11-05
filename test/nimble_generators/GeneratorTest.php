<?php

require_once(dirname(__FILE__) . '/../../nimble_generators/lib/generator.php');

require_once('PHPUnit/Framework.php');
require_once('vfsStream/vfsStream.php');

class GeneratorTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		vfsStreamWrapper::register();
		$root = new vfsStreamDirectory('root');
		$root->addChild(new vfsStreamDirectory('template'));
		$root->addChild(new vfsStreamDirectory('scripts'));
		$root->addChild(new vfsStreamDirectory('target'));	
		vfsStreamWrapper::setRoot($root);	

		Generator::$template_path = vfsStream::url('root/template');
		Generator::$script_path = vfsStream::url('root/scripts');
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
		file_put_contents(vfsStream::url('root/template/new.tmpl'), "");
		
		call_user_func("Generator::generate_${name}", vfsStream::url('root/target/made-it'));
		
		$this->assertEquals($is_generated, file_exists(vfsStream::url('root/target/made-it')));
	}
	
	/**
	 * @covers Generator::generate_test
	 */
	function testGenerateTest() {
		mkdir(vfsStream::url('root/test/unit'), 0777, true);
		file_put_contents(vfsStream::url('root/template/unit_test.tmpl'), '{class_name}');
		
		Generator::generate_test('unit', 'Unit');
		
		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('test/unit/UnitTest.php'));
		$this->assertEquals("Unit", file_get_contents(vfsStream::url('test/unit/UnitTest.php')));
	}
	
	/**
	 * @covers Generator::generate_scripts
	 */
	function testGenerateScripts() {
	  file_put_contents(vfsStream::url('root/scripts/nimblize'), 'no');	
	  file_put_contents(vfsStream::url('root/scripts/test'), 'yes');	
	  
	  mkdir(vfsStream::url('root/test/scripts'), 0777, true);
	  Generator::$nimble_root = vfsStream::url('root/test');
	  
	  Generator::generate_scripts(vfsStream::url('root/test/scripts'));
	  
	  $this->assertFileExists(vfsStream::url('root/test/scripts/test'));
	  $this->assertFileNotExists(vfsStream::url('root/test/scripts/nimblize'));
	}
	
	/**
	 * @covers Generator::generate_database_config
	 */
	function testDatabaseConfig() {
		mkdir(vfsStream::url('root/test'), 0777, true);
		file_put_contents(vfsStream::url('root/template/database.json'), "[env]");
		
		Generator::generate_database_config(vfsStream::url('root/test/database'), 'test');
		
		$this->assertFileExists(vfsStream::url('root/test/database'));
		$this->assertEquals('test', file_get_contents(vfsStream::url('root/test/database')));
	}
	
	function testGenerateModel() {
		mkdir(vfsStream::url('root/test/unit'), 0777, true);
		mkdir(vfsStream::url('root/app/model'), 0777, true);
		file_put_contents(vfsStream::url('root/template/model.tmpl'), "{class_name}");
		file_put_contents(vfsStream::url('root/template/unit_test.tmpl'), "{class_name}");
	 	
	 	Generator::generate_model("Test");
	 	
		$this->assertFileExists(vfsStream::url('root/app/model/test.php'));
		$this->assertEquals('Test', file_get_contents(vfsStream::url('root/app/model/test.php')));
		$this->assertFileExists(vfsStream::url('root/test/unit/TestTest.php'));	 	
	}

	function providerTestGenerateController() {
		return array(
			array(true), array(false)
		);	
	}

  /**
   * @dataProvider providerTestGenerateController
   * @covers Generator::generate_controller
   */
  function testGenerateController($generate_views) {
    mkdir(vfsStream::url('root/app/controller'), 0777, true);
    mkdir(vfsStream::url('root/app/view'), 0777, true);
    mkdir(vfsStream::url('root/test/functional'), 0777, true);
		file_put_contents(vfsStream::url('root/template/controller.tmpl'), "{class_name}");
    
    Generator::generate_controller('Test', $generate_views);
    
    $this->assertFileExists(vfsStream::url('root/app/controller/test_controller.php'));
    $this->assertEquals('Test', file_get_contents(vfsStream::url('root/app/controller/test_controller.php')));
    
    $this->assertEquals($generate_views, file_exists(vfsStream::url('root/app/view/test'))); 
		$this->assertFileExists(vfsStream::url('root/test/functional/TestTest.php'));	 	
  }

  function providerTestViewFunction() {
    return array(
      array(false, true)
    ); 
  }

  /**
   * @dataProvider providerTestViewFunction
   * @covers Generator::view_function
   */
  function testViewFunction($id) {
    $result = Generator::view_function('index', $id);
    $this->assertEquals(($id ? 1 : 0), preg_match('#\($id\)#', $result));
  }
  
  /**
   * @dataProvider providerTestGenerateMailer
   * @covers Generator::generate_mailer
   */
  function testGenerateMailer() {
		file_put_contents(vfsStream::url('root/template/mailer.tmpl'), "{class_name}");
    mkdir(vfsStream::url('root/app/view'), 0777, true);
    mkdir(vfsStream::url('root/app/model'), 0777, true);

    Generator::generate_mailer('Test', array('create'));
    
    $this->assertFileExists(vfsStream::url('root/app/model/Test.php'));
    $this->assertEquals('Test', file_get_contents(vfsStream::url('root/app/model/Test.php')));
    $this->assertFileExists(vfsStream::url('root/app/view/test'));
  }
}

?>
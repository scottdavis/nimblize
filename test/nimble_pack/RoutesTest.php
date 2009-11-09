<?php
require_once(dirname(__FILE__) . '/config.php');
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../../nimblize.php');
/**
 * @package FrameworkTest
 */
class RoutesTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$_SESSION = array();
		$_GET = array();
		$_SESSION['flashes'] = array();
		$this->Nimble = Nimble::getInstance();
		$this->Nimble->test_mode = true;
		$this->Nimble->routes = array();
		$this->Nimble->uri = '';
		$this->url = '';
	}

	/**
	 * @dataProvider providerRubyOnRailsRoutes
	 */
	public function testRubyOnRailsRoutes($ror_route, $expected_pattern) {
		$_SESSION = array();
		$_SESSION['flashes'] = array();
		$this->Nimble->add_url($ror_route, "Class", "method");
		$this->assertEquals("/^" . str_replace('/', '\/', $expected_pattern) . "$/", $this->Nimble->routes[0]->rule);

	}

	public function providerRubyOnRailsRoutes() {
		$pattern = "[\sa-zA-Z0-9_-]+";

		return array(
		array(":id", "(?P<id>{$pattern})"),
		array("view/:id", "view/(?P<id>{$pattern})"),
		array("view/:id1", "view/(?P<id1>{$pattern})"),
		array("view/:1id", "view/(?P<1id>{$pattern})"),
		array("view/:i_d", "view/(?P<i_d>{$pattern})"),
		array("view/:i-d", "view/(?P<i>{$pattern})-d"),
		array("view/:id/action", "view/(?P<id>{$pattern})/action"),
		array("view/:id/action/:id2", "view/(?P<id>{$pattern})/action/(?P<id2>{$pattern})"),
		array(":id:id2", "(?P<i>{$pattern})d(?P<id2>{$pattern})")
		);
	}

	public function testFormatRoutes() {
		$this->Nimble->url = "test";
		$this->Nimble->add_url('', "Class", "method");
		$this->assertEquals("/^$/", $this->Nimble->routes[0]->rule);

		$this->Nimble->routes = array();
		$this->Nimble->url = "test.xml";
		$this->Nimble->add_url('', "Class", "method");
		$this->assertEquals("/^\.(?P<format>[a-zA-Z0-9]+)$/", $this->Nimble->routes[0]->rule);
	}

	public function testResources() {
		Route::resources('Form');
		$this->assertEquals(count($this->Nimble->routes), 7);
	}

	public function testUrlFor() {
		Nimble::set_config('uri', '');
		$this->Nimble->url = '/class/1';
		$this->Nimble->add_url('/class/:method', "Class", "Method");
		$this->assertEquals('/class/1', url_for('Class', 'Method', 1));
	}

	/**
	 * @expectedException NimbleException
	 */
	public function testUrlForFailsWrongParams() {
		Nimble::set_config('url', '/class/1');
		$this->Nimble->add_url('/class/:method', "Class", "Method");
		$this->assertEquals('/class/1', url_for('Class', 'Method', 1, 2));
	}

	public function testGETisSetFromUrlArgs() {
		global $_GET;
		Nimble::set_config('url', '/class/1');
		$this->Nimble->add_url('/class/:method', "MyTestClass", "method");
		$this->Nimble->dispatch(true);
		$this->assertTrue(isset($_GET['method']));
		$this->assertEquals($_GET['method'], '1');
	}

	public function testRFunctionSingleParameter() {
		$result = R('test');
		$this->assertEquals('test', $result->pattern);
		$this->assertTrue(empty($result->controller));
		$this->assertEquals(0, count($this->Nimble->routes));
	}

	public function testRFunctionFourParameters() {
		$result = R('GET', 'GET', 'GET', 'GET');
		foreach (array('pattern', 'controller', 'action', 'http_method') as $param) {
			$this->assertEquals('GET', $result->{$param});
		}
		$this->assertEquals(1, count($this->Nimble->routes));
	}

	public function providerTestRFunctionBadParamCounts() {
		return array(
		array(array()),
		array(array('1', '2')),
		array(array('1', '2', '3')),
		);
	}

	/**
	 * @expectedException NimbleException
	 * @dataProvider providerTestRFunctionBadParamCounts
	 */
	public function testRFunctionBadParamCounts($params) {
		call_user_func_array('R', $params);
	}

	public function testSetShortRoute() {
		$route = R('GET', 'GET', 'GET', 'GET');
		$this->assertTrue(empty($route->short_url));
		$route->short_url('test');
		$this->assertEquals('test', $route->short_url);
	}

	public function testBindShortRoute() {
		$this->assertTrue(empty($this->Nimble->routes));
		$route = new Route('test');
		$route->controller('test')->action('test')->on('GET')->short_url('test');
		$this->assertTrue(count($this->Nimble->routes) == 1);
		$this->assertEquals('test', $this->Nimble->routes[0]->short_url);
	}

	public function testRemoveURLById(){
		$this->assertTrue(empty($this->Nimble->routes));
		$this->assertEquals(0, $id = $this->Nimble->add_url('GET', 'GET', 'GET', 'GET'));
		$this->assertEquals(1, $id = $this->Nimble->add_url('POST', 'POST', 'POST', 'POST'));
		$this->assertTrue($this->Nimble->remove_url_by_id(0));
		$this->assertEquals(1, count($this->Nimble->routes));
		$this->assertFalse($this->Nimble->remove_url_by_id(2));
	}

	public function testRebind() {
		$route = R('GET', 'GET', 'GET', 'GET');
		$this->assertEquals(1, count($this->Nimble->routes));
		$this->assertFalse(isset($this->Nimble->routes[0]->short_url));
		$route->short_url('test');
		$this->assertEquals(1, count($this->Nimble->routes));
		$this->assertTrue(isset($this->Nimble->routes[0]->short_url));
	}
}

class MyTestClass extends Controller {
	public function method() {

	}
}

?>

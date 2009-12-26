<?php

/**
 * Run PHPUnit tests on Nimble-specific entities.
 * @package testing
 */
abstract class NimbleUnitTestCase extends PHPUnit_Framework_TestCase {
  const XpathExists = "~!exists!~";
  const XpathNotExists = "~!not exists!~";
  const XpathCount = "~!count!~";
  private $_cached_xml = array();
  private $_redirects = array();
  
  
  public function __construct() {
    parent::__construct();
    NimbleRecord::start_transaction();
  }
  
  public function __destruct() {
  	NimbleRecord::rollback_transaction();	
  }
  
  
  
  /**
   * Assert that an XPath query matches a node in a particular way.
   * @param string $source The XML source to search.
   * @param $path The XPath to search for.
   * @param $match The type of search to perform.
   * @param $count If $match is self::XpathCount, the number of nodes expected in the result.
   */
  public function assertXpath($source, $path, $match = self::XpathExists, $count = 0) {
    if (($xml = $this->stringToXML($source)) !== false) {
      if (($result = $xml->xpath($path)) !== false) {
        if ($match === true) { $match = self::XpathExists; }
        if ($match === false) { $match = self::XpathNotExists; }
        switch ($match) {
          case self::XpathExists:
            $this->assertTrue(count($result) > 0, "no css selector matches found for <${path}>");
            break;
          case self::XpathNotExists:
            $this->assertTrue(count($result) == 0, "css selector matches found for <${path}>");
            break;
          case self::XpathCount:
            $this->assertEquals($count, count($result), "css selector count of <" . count($result) . "> does not match expected <${count}>");
            break;
          default:
            $this->assertEquals($match, (string)reset($result), "css selector value <" . (string)reset($result) . "> does not match expected <${match}>");
            break;
        }
      } else {
        $this->assertTrue(false, "css selector <${path}> is not valid");
      }
    }
  }
  
  /**
   * Assert the provided text is valid XHTML.
   * @param string $source The text to validate.
   */
  public function assertValidXHTML($source) {
    $this->assertTrue(self::stringToXML($source) !== false, "source is not valid XML");
  }
  
  /**
   * Convert a string to a cached SimpleXMLElement.
   * Print out an error message with the source code if a validation error occurs.
   * @param string $source The text to convert.
   */
  public function stringToXML($source) {
    if (!is_string($source)) { throw new Exception("source must be a string"); }
    $hash = md5($source);
    if (!isset($this->_cached_xml[$hash])) {
      try {
        $xml = new SimpleXMLElement("<x>" . $this->clean_xml_string($source) . "</x>");
        $this->_cached_xml[$hash] = $xml;
      } catch (Exception $e) {
        $this->_cached_xml[$hash] = false;
        var_dump($e->getMessage());
        
        $lines = explode("\n", $source);
        for ($i = 0, $il = count($lines); $i < $il; ++$i) {
          echo str_pad($i + 1, strlen($il), " ", STR_PAD_LEFT) . ":" . $lines[$i] . "\n"; 
        }
      }
    }
    return $this->_cached_xml[$hash];
  }
  
  /**
   * Clean a string containing HTML entities for XML parsing for test purposes.
   * @param string $source The string to clean.
   * @return string The cleaned string.
   */
  public function clean_xml_string($source) {
    $source = str_replace(
      array("&nbsp;", "&mdash;", "&ndash;"),
      array(" ", "--", "-"),
      $source
    );
    $source = preg_replace_callback('#&[^\;]+;#', array($this, 'callback_replace_non_xml_entities'), $source);
    return $source;
  }
  
  private function callback_replace_non_xml_entities($matches) {
    return (in_array($matches[0], array("&quot;", "&amp;", "&apos;", "&lt;", "&gt;")) ? $matches[0] : "");
  }
  
  /**
   * Render a controller method using the provided template, if necessary.
   * @param Controller $controller The controller to use.
   * @param string $method The method to call on the controller.
   * @param string $template The template to render.
   * @param array $parameters Additional parameters to pass to the controller.
   */
  public function render($controller, $method, $template = "", $parameters = array()) {
    ob_start();
    call_user_func_array(array($controller, $method), $parameters);
    if ($controller->has_rendered === false) {
      if ($template !== false) {
        if (empty($controller->layout_template) && $controller->layout) {
          $controller->set_layout_template();
        }
        $controller->render($template);
      }
    }
    return ob_get_clean();
  }
}

?>
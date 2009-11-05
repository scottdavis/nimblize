<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../../nimble_support/lib/inflector.php');

class InflectorTest extends PHPUnit_Framework_TestCase {
  function testMagicMethods() {
    $this->assertEquals('quizzes', Inflector::pluralize('quiz'));
  } 
}

?>
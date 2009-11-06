#!/usr/bin/env php
<?php

require_once(dirname(__FILE__) . '/../../nimble_support/lib/inflector.php');

$data_set = array(
  array(
    array('pluralize', 'axis'),
    array('singularize', 'analyses'),
    array('ordinalize', '1')
  )
);

$number_of_iterations = 1000000;

$start = time();

for ($i = 0; $i < $number_of_iterations; ++$i) {
  foreach ($data_set as $set) {
    list($method, $input) = $set;
    call_user_func("Inflector::$method", $input, false);
  }
}

$t1 = time();

for ($i = 0; $i < $number_of_iterations; ++$i) {
  foreach ($data_set as $set) {
    list($method, $input) = $set;
    call_user_func("Inflector::$method", $input, true);
  }
}

$t2 = time();

echo "Inflector profile test, ${number_of_iterations} iterations:\n\n";
printf("No caching: %d sec.\n", $t1 - $start);
printf("Caching: %d sec.\n", $t2 - $t2);
echo "\n";

?>
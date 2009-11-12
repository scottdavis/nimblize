#!/usr/bin/env php
<?php

	require_once(dirname(__FILE__) . '/../../test/nimble_record/config.php');
	NimbleRecord::$debug = false;
	$start = time();
	$start_memory = memory_get_usage();
	$number_of_iterations = 10000;
	
	for($i=0; $i< $number_of_iterations; ++$i) {
		User::find(1);
	}
	
	$t1 = time();
	$m1 = memory_get_usage();
	
	for($i=0; $i< $number_of_iterations; ++$i) {
		User::_find(1);
	}
	
	$t2 = time();
	$m2 = memory_get_usage();
	
	
	printf("Finder profile test, ${number_of_iterations} iterations:\n\n");
	printf("Caching Memusage: %d\n", $m1 - $start_memory);
	printf("Caching: %d sec.\n", $t1 - $start);
	printf("No Caching: %d sec.\n", $t2 - $t1);
	printf("No Caching Memusage: %d\n", $m2 - $m1);
	printf('Peak Memusage %d', memory_get_peak_usage());
	echo "\n";
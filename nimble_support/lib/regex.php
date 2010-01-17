<?php

class NimbleRegex {
  var $regex = NULL;
  public function __construct($regex) {
    $this->regex = $regex;
  }
  
  public function __toString() {
    return $this->regex;
  }
  
  public function __invoke() {
    return $this->regex;
  }
  
}
<?php
interface NimbleRecordCommandInterface {
  public static function do_method($method, $class, $table, $options = array());
  public static function methods();
}

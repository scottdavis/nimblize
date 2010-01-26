<?php
/**
  * @package NimbleRecord
  */
class NimbleMath implements NimbleRecordCommandInterface {
  public static $methods = array('max', 'sum', 'min', 'avg', 'count');
  public static function methods() {
    return static ::$methods;
  }
  public static function do_method($method, $class, $table, $options = array()) {
    if (!array_include($method, static ::$methods)) {
      throw new NimbleException("$method is not a math method");
    }
    $defaults = array('column' => 'id', 'cache' => true, 'conditions' => NULL);
    $options = array_merge($defaults, $options);
    static ::check_args_for_math_functions($options);
    $query = new NimbleQuery();
    $query->select = $method . '(' . $table . '.' . $options['column'] . ') AS ' . $method;
    $query->from = $table;
    if (isset($options['joins']) && !empty($options['joins'])) {
      $query->join = $options['joins'];
    }
    if (isset($options['group']) && !empty($options['group'])) {
      $query->group_by = $options['group'];
    }
    if (isset($options['conditions']) && !empty($options['conditions'])) {
      $query->where = NimbleRecord::build_conditions($options['conditions']);
    }
    $sql = $query->build();
    return $class::execute_query($sql, false, $options['cache'])->{$method};
  }
  /**
   * @param $options array('column' => 'name', 'conditions' => array('id' => 1))
   */
  private static function check_args_for_math_functions(array $options) {
    //verify options contains a column value
    if (!is_array($options) || !isset($options['column'])) {
      throw new NimbleRecordException('InvalidArguments - please include a column ex. array(\'column\' => \'id\')');
    }
  }
}
?>
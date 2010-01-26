<?php
/**
 * @package NimbleRecord
 */
class NimblePagination implements NimbleRecordCommandInterface {
  public static $methods = array('paginate');
  public static function methods() {
    return static ::$methods;
  }
  public static function do_method($method, $class, $table, $options = array()) {
    $defaults = array('per_page' => 25, 'page' => 1);
    $options = array_merge($defaults, $options);
    $options['page'] = is_null($options['page']) ? 1 : $options['page'];
    $options['conditions'] = isset($options['conditions']) ? $options['conditions'] : array();
    $total_count = call_user_func(array($class, 'count'), $options);
    $per_page = $options['per_page'];
    $page = $options['page'];
    unset($options['page'], $options['per_page']);
    $limit = (int)$per_page * ((int)$page - 1);
    $limit = ($limit > 0) ? $limit : 0;
    $limit = implode(',', array($limit, (int)$per_page));
    $options['limit'] = $limit;
    unset($limit);
    $return = call_user_func(array($class, 'build_find_sql'), array('all', $options));
    $return = call_user_func_array(array($class, 'execute_query'), $return);
    $return->total_count = $total_count;
    $return->per_page = $per_page;
    $return->page = $page;
    return $return;
  }
}

<?php
/**
  * @package NimbleRecord
  */
class NimbleLogger {
  public static $enabled = false;
  public static $instance = NULL;
  public function __construct() {
    if (static ::$enabled) {
      FileUtils::mkdir_p(FileUtils::join(FileUtils::join(NIMBLE_ROOT, 'log')));
      $this->log_file = FileUtils::join(NIMBLE_ROOT, 'log', NIMBLE_ENV . '.log');
    }
  }
  public static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new self();
    }
    return self::$instance;
  }
  public static function log($string) {
    if (static ::$enabled) {
      $ins = self::getInstance();
      file_put_contents($ins->log_file, $string . "\n", FILE_APPEND);
    }
  }
}
?>
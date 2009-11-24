<?php
	require_once(dirname(__FILE__) . '/../../../nimble_support/lib/command_line_colors.php');
	class MigrationRunner {
		
		static $dir = 'test';
		
		public static function migration_table_name() {
			return 'migrations';
		}
		
		public static function get_all_versions() {
			$table = NimbleRecord::$adapter->quote_table_name(static::migration_table_name());
			$query = new NimbleQuery();
			$query->select = 'version';
			$query->from = $table;
			$query->order_by = 'version ASC';
			$sql = $query->build();
			$results = NimbleRecord::$adapter->query($sql);
			$versions = array();
			while($row = $results->fetch_assoc()) {
				$versions[$row['version']] = $row['version'];
			}
			$results->free();
			ksort($versions);
			return $versions;
		}
		
		public static function current_version() {
			$table = static::migration_table_name();
			if(NimbleRecord::$adapter->table_exists($table)) {
				$versions = static::get_all_versions();
				$version = static::get_max_version($versions);
			}else{
				$version = 0;
			}
			return (int) $version;
		}
		
		public static function create_version($version) {
			$table = NimbleRecord::$adapter->quote_table_name(static::migration_table_name());
			$query = new NimbleQuery(NimbleQuery::INSERT);
			$query->insert_into = $table;
			$query->columns = array('version');
			$query->values = array($version);
			$sql = $query->build();
			Migration::execute($sql);
		}
		
		public static function delete_version($version) {
			$table = NimbleRecord::$adapter->quote_table_name(static::migration_table_name());
			$query = new NimbleQuery(NimbleQuery::DELETE);
			$query->from = $table;
			$query->where = NimbleQuery::condition('version', $version);
			$sql = $query->build();
			Migration::execute($sql);
		}
		
		
		public static function migrate($target_version = null) {
			if(is_string($target_version) && !is_numeric($target_version)) {
				if($target_version == 'down') {
					static::down(-1);
				}else{
					static::up();
				}
				return static::current_version();
			}
			if(is_null($target_version)) {
				static::up($target_version);
			}elseif((int) static::current_version() > (int) $target_version) {
				static::down($target_version);
			}else{
				static::up($target_version);
			}
			return static::current_version();
		}
		
		public static function setup_table() {
			if(!NimbleRecord::$adapter->table_exists(static::migration_table_name())) {
				static::create_migration_table();
			}
		}
		
		public static function up($to_version = NULL) {
			$table = static::migration_table_name();
			$data = static::load_files(static::$dir);
			$current = static::current_version();
			foreach($data as $version => $class) {
				if((int) $current >= (int) $version) {continue;}
				if((!is_null($to_version) &&  !empty($to_version)) && (int) $version > (int) $to_version) {continue;}
				print(CommandLineColor::underline('Running') . " " . CommandLineColor::underline_blue($class) . " - " . CommandLineColor::yellow($version) . "\n");
				$klass = new $class();
				$klass->up();
				static::create_version($version);
			}
		}
		
		
	public static function down($to_version) {
		$current = static::current_version();
		$table = static::migration_table_name();
		$data = static::load_files(static::$dir);
		$data = array_reverse($data, true);
		foreach($data as $version => $class) {
			if((int) $version < (int) $to_version) {continue;}
			if((int) $current < (int) $version) {continue;}
			print(CommandLineColor::underline('Running') . " " . CommandLineColor::underline_blue($class) . " - " . CommandLineColor::yellow($version) . "\n");
			$klass = new $class();
			$klass->down();
			static::delete_version($version);
		}
	}
	
	public static function drop_migration_table() {
		Migration::$show_sql = false;
		$mig = new Migration();
		$mig->drop_table(static::migration_table_name());
	}
	
	public static function create_migration_table() {
		Migration::$show_sql = false;
		$exists = static::migration_table_exists();
		$table = static::migration_table_name();
		$mig = new Migration();
		$t = $mig->create_table($table);
			$t->string('version');
		if(!$exists) {
			$t->go();
		}
	}
	
	public static function load_files() {
		$dir = static::$dir;
		$classes = array();
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(preg_match('/\.php$/' , $file)) {
					$temp_array = preg_match('/^([0-9_]+)_(.+)\.php/', $file, $matches);
					$version = $matches[1];
					$class_name = $matches[2];
					$classes[$version] = Inflector::classify($class_name);
					require_once($dir . '/' . $file);
				}
			}
			closedir($dh);
		}
		return $classes;
	}
	
	
	public static function migration_table_exists() {
		$table = static::migration_table_name();
		return NimbleRecord::$adapter->table_exists($table);
	}
	
	
	public static function get_max_version($array) {
		ksort($array);
		end($array);
		$key = key($array);
		return $key;
	}
		
	}

?>
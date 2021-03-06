<?php
	require_once(dirname(__FILE__) . '/base.php');
	/**
	* Asset tag helpers
	* @package NimbleSupport
	* @todo cacheing and combining
	*/
	class AssetTag extends TagHelper {
	
		/**
		* Requires:
		* Nimble::set_config('stylesheet_folder', <path>);
		* Nimble::set_config('stylesheet_folder_url', <url>);
		* Inculdes your stylesheet assests and also enables the style.css?<timestamp> format for browser cacheing
		* @uses <?php echo AssetTag::stylesheet_link_tag('style', 'application'); ?>
		* @uses <?php echo AssetTag::stylesheet_link_tag('http://prototype.com/my.css', 'application'); ?>
		* @todo Allow differnet media types
		* @returns string
		*/
		public static function stylesheet_link_tag() {
			$args = func_get_args();
			$style_sheet_path = Nimble::getInstance()->config['stylesheet_folder'];
			$style_sheet_base_url = Nimble::getInstance()->config['stylesheet_folder_url'];
			
			(string) $out = '';
			foreach($args as $css) {
					if(strpos($css, ".css") === false) {
						$css = $css . '.css';
					}
					$url = self::compute_public_path($css, $style_sheet_path, $style_sheet_base_url);
					$out .= self::stylesheet_tag($url) . "\n";
			}
			return $out;
		}
		
		
		/**
		* Requires:
		* Nimble::set_config('javascript_folder', <path>);
		* Nimble::set_config('javascript_folder_url', <url>);
		* Inculdes your javascript assests and also enables the java.js?<timestamp> format for browser cacheing
		* @uses <?php echo AssetTag::javascript_include_tag('prototype', 'application'); ?>
		* @uses <?php echo AssetTag::javascript_include_tag('http://prototype.com/my.js', 'application'); ?>
		* @returns string
		*/
		public static function javascript_include_tag() {
			$args = func_get_args();
			$javascript_path = Nimble::getInstance()->config['javascript_folder'];
			$javascript_base_url = Nimble::getInstance()->config['javascript_folder_url'];
			
			(string) $out = '';
			foreach($args as $js) {
					if(strpos($js, ".js") === false) {
						$js = $js . '.js';
					}
					$url = self::compute_public_path($js, $javascript_path, $javascript_base_url);
					$out .= self::javascript_tag($url) . "\n";
			}
			return $out;
		}
		
		/**
		* Creates a stylesheet tag
		* @param string $url Url of stylesheet you wish to include
		* @param string $media Type of media to accociate this stylesheet with
		* @return string
		*/
		public static function stylesheet_tag($url, $media='screen') {
			return self::tag('link', array('rel' => 'stylesheet', 'type' => Mime::CSS, 'media' => $media, 'href' => htmlspecialchars($url)));
		}
		/**
		* Creates a javescript tag
		* @param string $url Url of the javascript you wish to include
		* @return string
		*/
		public static function javascript_tag($url) {
			return self::content_tag('script', '', array('type' => Mime::JS, 'src' => $url));
		}
		/** 
		* Creates file timestamp
		* @param string $source Filename
		* @param string $dir Path to base directory of file
		* @access private
		* @return string
		*/
		private static function asset_id($source, $dir) {
			$key = $source . '-mtime';
			return filemtime(FileUtils::join($dir, $source));
		}
		/**
		* Creates a timestamped URL
		* @param string $source Filename
		* @param string $dir Path to base directory of file
		* @access private
		* @return string
		*/
		private static function rewrite_asset_path($source, $dir) {
			$asset_id = self::asset_id($source, $dir);
			if(empty($asset_id)) {
				return $source;
			}else{
				return $source . '?' . $asset_id;
			}
		}
		/**
		* Figures out weither the file is local of remote
		* @param string $source Filename
		* @param string $dir Path to base directory of file
		* @param string $url prefix base url of asset folder
		* @access private
		* @return string
		*/
		private static function compute_public_path($source, $dir, $url) {
			if(!preg_match('{^[-a-z]+://}', $source)) {
				return $url . '/' . self::rewrite_asset_path($source, $dir);
			}else{
				return $source;
			}
		
		}
		
		/**
		* Add an image from the image folder or other path
		* @param string $image - path to image
		* @param string $alt
		* @param array $options - tag options
		*/
		public static function image($image, $alt='', $options = array()) {
			$nmbl = Nimble::getInstance()->config;
			$url = $nmbl['image_url'];
			$image_path = $nmbl['image_path'];
			$other_path = FileUtils::join(NIMBLE_ROOT, 'public', $image);
			empty($alt) ? $alt = $image : $alt;
			if(file_exists(FileUtils::join($image_path, $image))) {
				$public_path = static::rewrite_asset_path($image, $image_path);
				$url = $url . '/' . $public_path;
			}elseif(file_exists($other_path)) {
				$uri = $nmbl['uri'];
				$public_path = static::rewrite_asset_path($other_path, '');
				$url = $uri . '/public/' . $image;
			}else{
				throw new NimbleException('No Image Found: ' . $other_path);
			}
			return TagHelper::tag('img', array_merge(array('src' => $url, 'alt' => $alt), $options));
	}
}


?>
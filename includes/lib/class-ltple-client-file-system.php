<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_File_System {

		/**
		 * The single instance of LTPLE_Client_Admin_Notices.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * The main plugin object.
		 * @var 	object
		 * @access  public
		 * @since 	1.0.0
		 */
		public $parent = null;	

		/**
		 * Constructor function
		 */
		public function __construct ($parent) {
			
			$this->parent = $parent;
		}
		
		private function get_secret_iv(){
			
			$secret_iv = md5( 'another-secret' );	

			return $secret_iv;
		}	
		
		public function encrypt_str($string, $secret_key = ''){
			
			$output = false;

			$encrypt_method = "AES-256-CBC";
			
			if( empty($secret_key) ){
			
				$secret_key = md5( $this->parent->key );
			}
			
			$secret_iv = $this->get_secret_iv();
			
			// hash
			$key = hash('sha256', $secret_key);
			
			// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
			$iv = substr(hash('sha256', $secret_iv), 0, 16);

			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = $this->base64_urlencode($output);

			return $output;
		}
		
		public function decrypt_str($string, $secret_key = ''){
			
			$output = false;

			$encrypt_method = "AES-256-CBC";
			
			if( empty($secret_key) ){
				
				$secret_key = md5( $this->parent->key );
			}
			
			$secret_iv = $this->get_secret_iv();

			// hash
			$key = hash( 'sha256', $secret_key);
			
			// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
			$iv = substr( hash( 'sha256', $secret_iv ), 0, 16);

			$output = openssl_decrypt($this->base64_urldecode($string), $encrypt_method, $key, 0, $iv);

			return $output;
		}
		
		public function base64_urlencode($inputStr=''){

			return strtr(base64_encode($inputStr), '+/=', '-_,');
		}

		public function base64_urldecode($inputStr=''){

			return base64_decode(strtr($inputStr, '-_,', '+/='));
		}
		
		public function copy_dir($src, $dst, $skip_list = array() ){ 
			
			//$src 	= trailingslashit($src);
			//$dst 	= trailingslashit($dst);

			if( !is_dir($dst) ){
				
				if( !$this->create_writeable_folder($dst,$src) ){
					
					return false;
				}
			}
			
			$dir = opendir($src);
			
			while(false !== ( $file = readdir($dir)) ) { 
			
				if( ( $file != '.' ) && ( $file != '..' ) && !in_array( $file, $skip_list ) ) { 
				
					if ( is_dir($src . '/' . $file) ) { 
					
						$this->copy_dir( $src . '/' . $file, $dst . '/' . $file); 
					} 
					else{
						
						$this->copy_file($src . '/' . $file,$dst . '/' . $file); 
					} 
				} 
			} 
			
			closedir($dir); 
			
			return true;
		}
		
		/**
		 *
		 *
		 * @param unknown $folder
		 * @param string  $method  Which method to use when creating
		 * @param string  $url     Where to redirect after creation
		 * @param bool|string $context folder to create folder in
		 */

		static public function create_writeable_folder( $folder, $from_folder = '' ) {
			
			if( self::create_folder( $folder, $from_folder ) ){

				$permissions = array( 0755, 0775, 0777 );

				for ( $set_index = 0; $set_index < count( $permissions ); $set_index++ ) {
					
					if ( is_writable( $folder ) )
						break;

					self::chmod( $folder, $permissions[$set_index] );
				}
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * Copy file using WordPress filesystem functions.
		 *
		 * @param unknown $source_filename
		 * @param unknown $destination_filename
		 * @param string  $method               Which method to use when creating
		 * @param string  $url                  Where to redirect after creation
		 * @param bool|string $context             folder to copy files too
		 */
		static public function copy_file( $source_filename, $destination_filename ) {
			
			$contents = @file_get_contents( $source_filename );

			if( self::request_filesystem_credentials() ){
				
				global $wp_filesystem;
				
				if ( $wp_filesystem->put_contents( $destination_filename, $contents,FS_CHMOD_FILE ) ) {
					
					return true;					
				}
			}

			return false;
		}
		
		static public function rename( $from, $to ) {
			
			if( self::request_filesystem_credentials() ){
				
				global $wp_filesystem;
				
				if ( $wp_filesystem->move( $from, $to ) ) {
					
					return true;					
				}
			}
			
			return false;
		}
		
		static public function delete( $path, $recursive = true ) {
			
			if( self::request_filesystem_credentials() ){
				
				global $wp_filesystem;
				
				if ( $wp_filesystem->delete( $path, $recursive ) ) {
					
					return true;					
				}
			}
			
			return false;
		}
		
		public function create_folder_recursively($dir){
			
			if( !is_dir($dir) ){
				
				$folders = explode('/' ,$dir);
				$to = '';

				foreach($folders as $folder){

					$to = $to . $folder . '/';
					
					if( !is_dir($to) ) { 
						
						if( !$this->create_writeable_folder($to) ){
					
							return false;
						}
					}
				}
			}

			return true;
		}
		
		public function put_contents( $filename, $contents ) {
			
			$dir = dirname($filename);

			if( $this->create_folder($dir) && self::request_filesystem_credentials() ){
				
				global $wp_filesystem;
				
				if ( $wp_filesystem->put_contents( $filename, $contents, FS_CHMOD_FILE ) ) {
					
					return true;					
				}
				elseif( file_put_contents($filename, $contents, LOCK_EX) ){
					
					return true;
				}
			}

			return false;
		}

		/**
		 *
		 *
		 * @param unknown $folder
		 * @param string  $method  Which method to use when creating
		 * @param string  $url     Where to redirect after creation
		 * @param bool|string $context folder to create folder in
		 */
		static private function create_folder( $folder, $from_folder = '' ) {
			
			if ( @is_dir( $folder ) )
				return true;
			
			if( !empty($from_folder) ){
				
				if ( self::mkdir_from( $folder, $from_folder ) )
					return true;
			}
			
			if( self::request_filesystem_credentials() ){

				global $wp_filesystem;
				
				if ( !$wp_filesystem->mkdir( $folder, FS_CHMOD_DIR ) ) {
					
					if ( !@mkdir($folder) ) {
						
						return false;
					}
					else{
						
						chmod($folder,decoct(FS_CHMOD_DIR));
					}
				}
				
				return true;
			}

			return false;
		}

		/**
		 * Recursive creates directory from some directory
		 * Does not try to create directory before from
		 *
		 * @param string  $path
		 * @param string  $from_path
		 * @param integer $mask
		 * @return boolean
		 */
		static public function mkdir_from( $path, $from_path = '', $mask = 0777 ) {
			$path = self::realpath( $path );

			$from_path = self::realpath( $from_path );
			if ( substr( $path, 0, strlen( $from_path ) ) != $from_path )
				return false;

			$path = substr( $path, strlen( $from_path ) );

			$path = trim( $path, '/' );
			$dirs = explode( '/', $path );

			$curr_path = $from_path;

			foreach ( $dirs as $dir ) {
				if ( $dir == '' ) {
					return false;
				}

				$curr_path .= ( $curr_path == '' ? '' : '/' ) . $dir;

				if ( !@is_dir( $curr_path ) ) {
					if ( !@mkdir( $curr_path, $mask ) ) {
						return false;
					}
				}
			}

			return true;
		}
		
		/**
		 * Get WordPress filesystems credentials. Required for WP filesystem usage.
		 *
		 * @param string  $method  Which method to use when creating
		 * @param string  $url     Where to redirect after creation
		 * @param bool|string $context path to folder that should be have filesystem credentials.
		 */
		static private function request_filesystem_credentials( $method = '', $url = '', $context = false ) {
			
			if( !function_exists('request_filesystem_credentials') ){
				
				include_once(ABSPATH . 'wp-admin/includes/file.php');
			}
			
			if( !function_exists('wp_generate_password') ){
				
				include_once(ABSPATH . 'wp-includes/pluggable.php');
			}
			
			if ( strlen( $url ) <= 0 )
				$url = $_SERVER['REQUEST_URI'];

			$success = true;
			
			ob_start();
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, $context, array() ) ) ) {
				$success =  false;
			}
			$form = ob_get_contents();
			ob_end_clean();

			ob_start();
			// If first check failed try again and show error message
			if ( !WP_Filesystem( $creds ) && $success ) {
				request_filesystem_credentials( $url, $method, true, false, array() );
				$success =  false;
				$form = ob_get_contents();
			}
			ob_end_clean();

			if ( !$success ) {
				
				if ( preg_match( "/<div([^c]+)class=\"error\">(.+)<\/div>/", $form, $matches ) ) {
					$error = $matches[2];
					$form = str_replace( $matches[0], '', $form );
				}				
				
				$this->parent->notices->add_error($error);
				return false;
			}

			return $success;
		}
		
		/**
		 *
		 *
		 * @param string  $filename
		 * @param int     $permission
		 * @return void
		 */
		static private function chmod( $filename, $permission ) {
			
			if ( @chmod( $filename, $permission ) )
				return;
			
			if( self::request_filesystem_credentials() ){

				global $wp_filesystem;
				if ( !$wp_filesystem->chmod( $filename, $permission, true ) ) {
					
					return;
				}
			}
			
			return true;
		}

		/**
		 * Returns real path of given path
		 *
		 * @param string  $path
		 * @return string
		 */
		static public function realpath( $path ) {
			$path = self::normalize_path( $path );
			$parts = explode( '/', $path );
			$absolutes = array();

			foreach ( $parts as $part ) {
				if ( '.' == $part ) {
					continue;
				}
				if ( '..' == $part ) {
					array_pop( $absolutes );
				} else {
					$absolutes[] = $part;
				}
			}

			return implode( '/', $absolutes );
		}

		/**
		 * Converts win path to unix
		 *
		 * @param string  $path
		 * @return string
		 */
		static public function normalize_path( $path ) {
			$path = preg_replace( '~[/\\\]+~', '/', $path );
			$path = rtrim( $path, '/' );

			return $path;
		}

		private static function folder_to_zip($folder, &$zipFile, $exclusiveLength) { 
			
			$handle = opendir($folder); 
			
			while (false !== $f = readdir($handle)) { 
			  
				if ($f != '.' && $f != '..') { 
				  
					$filePath = "$folder/$f"; 
					
					// Remove prefix from file path before add to zip. 
					
					$localPath = substr($filePath, $exclusiveLength); 
					
					if (is_file($filePath)) { 
					  
						$zipFile->addFile($filePath, $localPath); 
					} 
					elseif (is_dir($filePath)) { 
					  
						// Add sub-directory. 
					  
						$zipFile->addEmptyDir($localPath); 
					  
						self::folder_to_zip($filePath, $zipFile, $exclusiveLength); 
					} 
				} 
			} 
			
			closedir($handle); 
		} 

		public function zip_dir($sourcePath, $outZipPath){ 
		  
			$pathInfo 	= pathInfo($sourcePath); 
			$parentPath = $pathInfo['dirname']; 
			$dirName 	= $pathInfo['basename']; 

			$zip = new ZipArchive(); 
			
			if( $zip->open($outZipPath, ZIPARCHIVE::CREATE) ){
			
				$zip->addEmptyDir($dirName); 
				
				self::folder_to_zip($sourcePath, $zip, strlen("$parentPath/")); 
				
				$zip->close();
				
				return true;
			}
			
			return false;
		}
		
		static public function unzip_dir($sourcePath, $outUnZipPath){

			$zip = new ZipArchive;
			
			if( $zip->open($sourcePath) ){
				
				$zip->extractTo(dirname($outUnZipPath));
				$zip->close();
			 
				return true;
			}

			return false;
		}			
		
		public static function instance ( $parent ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $parent );
			}
			return self::$_instance;
		} // End instance()

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
		} // End __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
		} // End __wakeup()
	}

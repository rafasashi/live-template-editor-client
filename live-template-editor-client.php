<?php
/*
 * Plugin Name: Live Template Editor Client
 * Version: 1.1.0
 * Plugin URI: https://github.com/rafasashi
 * Description: Live Template Editor allows you to edit and save HTML5 and CSS3 templates.
 * Author: Rafasashi
 * Author URI: https://github.com/rafasashi
 * Requires at least: 4.6
 * Tested up to: 4.7
 *
 * Text Domain: ltple-client
 * Domain Path: /lang/
 *
 * GitHub Plugin URI: rafasashi/live-template-editor-client
 * GitHub Branch:     master
 *
 * @package WordPress
 * @author Rafasashi
 * @since 1.0.0
 */
 
	/**
	* Add documentation link
	*
	*/
	
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	if(!function_exists('is_dev_env')){
		
		function is_dev_env( $dev_ip = '109.28.69.143' ){ 
			
			if( $_SERVER['REMOTE_ADDR'] == $dev_ip || ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $dev_ip ) ){
				
				return true;
			}

			return false;		
		}			
	}	
	
	if(!function_exists('ltple_row_meta')){
	
		function ltple_row_meta( $links, $file ){
			
			if ( strpos( $file, basename( __FILE__ ) ) !== false ) {
				
				$new_links = array( '<a href="https://github.com/rafasashi" target="_blank">' . __( 'Documentation', 'cleanlogin' ) . '</a>' );
				$links = array_merge( $links, $new_links );
			}
			return $links;
		}
	}
	
	add_filter('plugin_row_meta', 'ltple_row_meta', 10, 2);
	
	$mode = ( is_dev_env() ? '-dev' : '');
	
	if( $mode == '-dev' ){
		
		ini_set('display_errors', 1);
	}
	
	// Load plugin functions
	require_once( 'includes'.$mode.'/functions.php' );	
	
	// Load plugin class files

	require_once( 'includes'.$mode.'/class-ltple-client.php' );
	require_once( 'includes'.$mode.'/class-ltple-client-settings.php' );
	require_once( 'includes'.$mode.'/class-ltple-client-object.php' );
		
	// Autoload plugin libraries
	
	$lib = glob( __DIR__ . '/includes'.$mode.'/lib/class-ltple-client-*.php');
	
	foreach($lib as $file){
		
		require_once( $file );
	}
	
	/**
	 * Returns the main instance of LTPLE_Client to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object LTPLE_Client
	 */
	function LTPLE_Client ( $version = '1.0.0' ) {
		
		register_activation_hook( __FILE__, array( 'LTPLE_Client', 'install' ) );
		
		$instance = LTPLE_Client::instance( __FILE__, $version );
		
		if ( is_null( $instance->_dev ) ) {
			
			$instance->_dev = ( is_dev_env() ? '-dev' : '');
		}				
 
		if ( is_null( $instance->settings ) ) {
			
			$instance->settings = LTPLE_Client_Settings::instance( $instance );
		}

		return $instance;
	}
	
	// start plugin
	
	if( $mode == '-dev' ){
		
		LTPLE_Client('1.1.1');
	}
	else{
		
		LTPLE_Client('1.1.0');
	}
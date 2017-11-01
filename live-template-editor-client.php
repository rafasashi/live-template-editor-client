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
	
	if ( ! defined( 'LTPLE_MARKETPLACE' ) ){
		
		define( 'LTPLE_MARKETPLACE', false);
	}
	
	$dev_ip = '';
	
	if( defined('MASTER_ADMIN_IP') ){
		
		$dev_ip = MASTER_ADMIN_IP;
	}
		
	$mode = ( ($_SERVER['REMOTE_ADDR'] == $dev_ip || ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $dev_ip )) ? '-dev' : '');
	
	// Load plugin functions
	
	require_once( 'includes'.$mode.'/functions.php' );	
	
	// Load plugin class files

	require_once( 'includes' . $mode . '/class-ltple-client.php' );
	require_once( 'includes' . $mode . '/class-ltple-client-settings.php' );
	require_once( 'includes' . $mode . '/class-ltple-client-object.php' );
		
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
	 
	function LTPLE_Client ( $version = '1.0.0', $mode = '' ) {
		
		register_activation_hook( __FILE__, array( 'LTPLE_Client', 'install' ) );
		
		$instance = LTPLE_Client::instance( __FILE__, $version );
		
		if ( is_null( $instance->_dev ) ) {
			
			$instance->_dev = $mode;
		}				

		if ( is_null( $instance->settings ) ) {
			
			$instance->settings = LTPLE_Client_Settings::instance( $instance );
		}

		return $instance;
	}
	
	// start plugin
	
	if( $mode == '-dev' ){
		
		LTPLE_Client( '1.2.2', $mode ); 
	}
	else{
		
		LTPLE_Client( '1.1.9', $mode );
	}
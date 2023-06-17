<?php 
/**
 * Plugin Name: Live Template Editor Client
 * Version: 1.1.14.7
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
 * GitHub Branch: master
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
	
	$plugins = apply_filters('active_plugins', get_option('active_plugins'));
	
	if( in_array('live-template-editor/live-template-editor.php', $plugins)){
		
		if( in_array('live-template-editor-server/live-template-editor-server.php', $plugins) && isset($_REQUEST['uri']) && isset($_REQUEST['pu']) && isset($_REQUEST['lk']) && isset($_REQUEST['lo']) ){
				
			//local editor session start
		}
		else{

			// Load plugin functions
			
			require_once( 'includes/functions.php' );	
			
			// Load plugin class files

			require_once( 'includes/class-ltple-client.php' );
			require_once( 'includes/class-ltple-client-settings.php' );
			require_once( 'includes/class-ltple-client-object.php' );
			require_once( 'includes/class-ltple-client-app.php' );
			require_once( 'includes/class-ltple-client-integrator.php' );
			
			// Autoload plugin libraries
			
			$lib = glob( __DIR__ . '/includes/lib/class-ltple-client-*.php');
			
			foreach($lib as $file){
				
				require_once( $file );
			}
			
			/**
			 * Returns the main instance of LTPLE_Client to prevent the need to use globals.
			 *
			 * @since  1.0.0
			 * @return object LTPLE_Client
			 */
			 
			function LTPLE_Client( $version = '1.0.0' ) {
				
				register_activation_hook( __FILE__, array( 'LTPLE_Client', 'install' ) );
				
				$instance = LTPLE_Client::instance( __FILE__, $version );
				
				if ( is_null( $instance->filesystem ) ) {
					
					$instance->filesystem = LTPLE_Client_File_System::instance( $instance );
				}			
				
				if ( is_null( $instance->settings ) ) {
					
					$instance->settings = LTPLE_Client_Settings::instance( $instance );
				}

				return $instance;
			}
			
			LTPLE_Client('1.1.13.77');
		}
	}
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Server {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		if( is_plugin_active( 'live-template-editor-server/live-template-editor-server.php' ) ){
			
			$this->url 	= WP_SITEURL;
		}
		else{
			
			$this->url = get_option( $this->parent->_base . 'server_url');
		}
		
		if( !empty($this->url) ){
			
			if( defined('REW_DEV_PATH') && REW_DEV_PATH === true ){
				
				// set dev url
				
				$this->url .= '.d1.recuweb.com';
				
				$this->url = str_replace('https://','http://',$this->url); 
			}
			
			// set access control
			
			$url = parse_url($this->url);
						
			header('Access-Control-Allow-Origin: ' . $url['scheme']."://".$url['host'], false);
			header('Access-Control-Allow-Credentials: true', false);			
		}		
		
		add_filter('ltple_remote_script_url', array( $this, 'get_script_url' ));
	}
	
	public function get_script_url($url){
	
		$url = $this->url . '/server/';
		
		return $url;
	}
}
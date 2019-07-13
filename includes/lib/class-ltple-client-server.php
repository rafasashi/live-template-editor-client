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
			
			// set access control
			
			$url = parse_url($this->url);
						
			header('Access-Control-Allow-Origin: ' . $url['scheme']."://".$url['host'], false);
			header('Access-Control-Allow-Credentials: true', false);			
		}		
		
		// set api url
		
		$this->api = $this->url . '/' . rest_get_url_prefix() . '/';
	}
}
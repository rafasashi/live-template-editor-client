<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Client {

	public $parent;
	public $url = '';
	public $key = '';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$site_url = defined('REW_PRIMARY_SITE') ? REW_PRIMARY_SITE : site_url();

		// check path
		
		$pathinfo = pathinfo($site_url);
		
		if( !empty($pathinfo['basename']) ){
			
			$this->url = $site_url.'/';
		}
		else{
			
			$this->url = $site_url;
		}
		
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		if( is_plugin_active( 'live-template-editor-server/live-template-editor-server.php' ) ){
		
			$this->key = md5($this->url);
		}
		else{
			
			$this->key = get_option( $this->parent->_base . 'client_key' );
		}
	}
}
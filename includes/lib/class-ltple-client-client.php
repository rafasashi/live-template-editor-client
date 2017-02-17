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
		
		$site_url = site_url();
		
		// check path
		
		$pathinfo = pathinfo($site_url);
		
		if( !empty($pathinfo['basename']) ){
			
			$this->url = $site_url.'/';
		}
		else{
			
			$this->url = $site_url;
		}
		
		$this->key = get_option( $this->parent->_base . 'client_key' );
	}
}
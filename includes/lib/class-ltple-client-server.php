<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Server {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		if( $this->url = get_option( $this->parent->_base . 'server_url') ){
			
			// set access control
			
			$url = parse_url($this->url);
						
			header('Access-Control-Allow-Origin: ' . $url['scheme']."://".$url['host'], false);
			header('Access-Control-Allow-Credentials: true', false);			
		}
		
		// set api url
		
		$this->api = $this->url . '/wp-json/';
	}
}
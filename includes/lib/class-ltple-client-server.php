<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Server {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->url = get_option( $this->parent->_base . 'server_url');
		
		$this->api = $this->url . '/wp-json/';
	}
}
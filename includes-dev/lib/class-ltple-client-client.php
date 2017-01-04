<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Client {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct () {
		
		$this->key = get_option( 'ltple_client_key' );
	}
}
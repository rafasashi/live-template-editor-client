<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Server {

	public $url = 'https://saas.recuweb.com';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->url = apply_filters('rew_server_url',$this->url);
		
		add_action('send_headers', array($this, 'add_cors_header'),999 );
	
		add_filter('ltple_remote_script_url', array( $this, 'get_script_url' ));
	}
	
	public function get_script_url($url){
		
		return apply_filters('rew_server_url',$url);
	}
	
	public function add_cors_header( $request = null ) {
		
		// set CORS
		
		$url = parse_url($this->url);
		
		header('Access-Control-Allow-Origin: ' . $url['scheme']."://".$url['host'], false);
		header('Access-Control-Allow-Credentials: true', false);
		
		return $request;
	}
}

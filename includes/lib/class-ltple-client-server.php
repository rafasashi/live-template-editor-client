<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Server {

	public $url = 'https://saas.recuweb.com';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		if( defined('REW_DEV_ENV') && REW_DEV_ENV === true ){
			
			// set dev url
			
			$this->url = str_replace('.','--',untrailingslashit($this->url)) . '.' . REW_SERVER;				 
		}	
		
		add_action('send_headers', array($this, 'add_cors_header'),999 );
	
		add_filter('ltple_remote_script_url', array( $this, 'get_script_url' ));
	}
	
	public function get_script_url($url){
		
		if( defined('REW_DEV_ENV') && REW_DEV_ENV === true ){
			
			$args = parse_url($url);

			$url = $args['scheme'] . '://' .  str_replace('.','--',$args['host']) . '.' . REW_SERVER . $args['path'];
		}
		
		return $url;
	}
	
	public function add_cors_header( $request = null ) {
		
		// set CORS
		
		$url = parse_url($this->url);
		
		header('Access-Control-Allow-Origin: ' . $url['scheme']."://".$url['host'], false);
		header('Access-Control-Allow-Credentials: true', false);
		
		return $request;
	}
}

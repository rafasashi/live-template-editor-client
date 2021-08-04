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
		
		$this->url = 'https://saas.recuweb.com';
		
		if( !empty($this->url) ){
			
			if( defined('REW_DEV_ENV') && REW_DEV_ENV === true ){
				
				// set dev url
				
				$this->url = str_replace('.','--',untrailingslashit($this->url)) . '.' . REW_SERVER;				 
			}
			
			// set CORS
			
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
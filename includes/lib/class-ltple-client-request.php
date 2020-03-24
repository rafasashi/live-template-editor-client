<?php

if ( ! defined( 'ABSPATH' ) ) exit;
 
class LTPLE_Client_Request {

	var $parent;
	var $url;
	var $ref_id;
	var $proto = 'http://';

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		if(!isset($_SERVER['HTTP_USER_AGENT'])){
			
			$_SERVER['HTTP_USER_AGENT'] = '';
		}
		
		// get proto
		
		if( is_ssl() ){
		
			$this->proto = 'https://';
		}
		
		// get remote request
		
		$this->is_remote = false;
		
		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['HTTP_X_FORWARDED_KEY']) && isset($_SERVER['HTTP_X_FORWARDED_USER']) && $_SERVER['HTTP_X_FORWARDED_KEY'] == md5('remote'.$this->ip) ){
			
			$this->is_remote = true;
		}

		// get user agent
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		// get referral id
		
		$this->ref_key 	= '_' . $this->parent->_base . 'ref_id';
		
		// get referral id
		
		$ref_id 	= '';

		if( !empty( $_COOKIE[$this->ref_key] ) ){
			
			$ref_id = sanitize_text_field($_COOKIE[$this->ref_key]);
		}
		elseif( !empty( $_REQUEST['ri'] ) ){
			
			$ref_id = sanitize_text_field($_REQUEST['ri']);
			
			// set cookie
			
			setcookie($this->ref_key, $ref_id, time() + 2678400, COOKIEPATH, COOKIE_DOMAIN); // for one month
		}

		if( !empty( $ref_id ) ){

			$ref = explode('RI-', $this->parent->ltple_decrypt_uri($ref_id) );
			
			if( isset($ref[1]) && is_numeric($ref[1]) ){

				$this->ref_id = intval($ref[1]);
			}
		}
		
		add_action('wp_login', function(){
			
			// reset cookie
			
			setcookie($this->ref_key, '', time() + 2678400, COOKIEPATH, COOKIE_DOMAIN);
			
		}, 10, 2);
		
		add_action('ltple_loaded', function(){
			
			if( $this->parent->user->loggedin ){
			
				// reset cookie
			
				setcookie($this->ref_key, '', time() + 2678400, COOKIEPATH, COOKIE_DOMAIN);
			}
		});
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) exit;
 
class LTPLE_Client_Request {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct () {
		
		// get user ip
		
		$this->ip = $this->ltple_get_user_ip();
		
		// get remote request
		
		$this->is_remote = false;
		
		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['HTTP_X_FORWARDED_KEY']) && isset($_SERVER['HTTP_X_FORWARDED_USER']) && $_SERVER['HTTP_X_FORWARDED_KEY'] == md5('remote'.$this->ip) ){
			
			$this->is_remote = true;
		}

		// get user agent
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
	}
	
	public function ltple_get_user_ip() {
		
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
			
			if (array_key_exists($key, $_SERVER) === true){
				
				foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip){
					
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
						
						return $ip;
					}
				}
			}
		}
	}	
}
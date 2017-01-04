<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Request {

	public $url = '';

	/**
	 * Constructor function
	 */
	public function __construct () {
		
		//get user ip
		
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			
			$this->ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(!empty($_SERVER['HTTP_FORWARDED'])) {
			
			$this->ip = trim(str_replace('for=','',$_SERVER['HTTP_FORWARDED']));
		} 
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			
			if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false){
				
				$ips=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
				
				$this->ip = trim($ips[0]);
			}
			else{
				
				$this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		} 
		else{
			
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}		
		
		// get remote request
		
		$this->is_remote = false;
		
		if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['HTTP_X_FORWARDED_KEY']) && isset($_SERVER['HTTP_X_FORWARDED_USER']) && $_SERVER['HTTP_X_FORWARDED_KEY'] == md5('remote'.$this->ip) ){
			
			$this->is_remote = true;
		}

		// get user agent
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Apps {
	
	var $parent;
	var $app;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent, $currentApp = '' ) {
		
		$this->parent 	= $parent;

		// get all apps
		
		$this->appList = get_terms( array(
				
			'taxonomy' 		=> 'app-type',
			'hide_empty' 	=> false,
			'order' 		=> 'DESC',
		));
		
		// get custom fields
		
		foreach($this->appList as $app){
		
			$app->thumbnail = get_option('thumbnail_'.$app->slug);
			$app->types 	= get_option('types_'.$app->slug);
		}
		
		// get current app
		
		if(!empty($currentApp)){
			
			$this->app = $currentApp;
		}		
		elseif(!empty($_REQUEST['app'])){
			
			$this->app = $_REQUEST['app'];
		}
		elseif(!empty($_SESSION['app'])){
			
			$this->app = $_SESSION['app'];
			
			// flush app session
			
			$_SESSION['app'] = '';
		}
		
		if(!empty($this->app)){
			
			foreach($this->appList as $app){
				
				if( $this->app == $app->slug ){
					
					$string = preg_replace_callback(
						'/[-_](.)/', 
						function ($matches) {
							
							return '_'.strtoupper($matches[1]);
						},
						$app->slug
					);
					
					$className = 'LTPLE_Client_App_'. ucfirst($string) ;
					
					if(class_exists($className)){
						
						include($this->parent->vendor . '/autoload.php');

						$this->{$app->slug} = new $className($app->slug, $parent, $this);
					}
					
					break;
				}
			}
		}
	}
	
	public function newAppConnected(){
		
		do_action( $this->parent->_base . 'new_app_connected' );
	}
	
	public function getAppData($app_id, $user_id = NULL, $array = false ){
		
		$app_data = NULL;
		
		if( is_numeric($app_id) ){
			
			$app = get_post(($app_id));				
			
			if( isset($app->post_author) ){
				
				if( is_numeric($user_id) && intval($app->post_author) != intval($user_id) ){
					
					echo 'User app access restricted...';
					exit;
				}
				else{
					
					$app_data = json_decode(get_post_meta( $app->ID, 'appData', true ),$array);
				}
			}
			else{
				
				echo 'User app not found...';
				exit;				
			}
		}
		else{
			
			echo 'Wrong app id request...';
			exit;
		}

		return $app_data;
	}
} 
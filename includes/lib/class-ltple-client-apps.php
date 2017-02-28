<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Apps {
	
	var $parent;
	var $app;
	var $mainApps;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent) {
		
		$this->parent 	= $parent;
		
		// get current app
		
		if(!empty($_REQUEST['app'])){
			
			$this->app = $_REQUEST['app'];
		}
		elseif(!empty($_SESSION['app'])){
			
			$this->app = $_SESSION['app'];
			
			// flush app session
			
			$_SESSION['app'] = '';
		}
		
		add_filter('wp_loaded', array( $this, 'apps_init'));
		
		add_filter("user-app_custom_fields", array( $this, 'get_fields' ));		
	}
	
	// Add app data custom fields

	public function get_fields($fields=[]){
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appData"),
				'id'			=>	"appData",
				'label'			=>	"",
				'type'			=>	'textarea',
				'placeholder'	=>	"JSON object",
				'description'	=>	''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appSettings"),
				'id'			=>	"appSettings",
				'label'			=>	"",
				'type'			=>	'textarea',
				'placeholder'	=>	"JSON object",
				'description'	=>	''
		);
		
		return $fields;
	}
	
	public function apps_init(){
		
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
		
		// get main apps
		
		$this->mainApps = get_posts(array(

			'post_type'   	=> 'user-app',
			'post_status' 	=> 'publish',
			'post__in' 		=> array( get_option( $this->parent->_base . 'wpcom_main_account' ), get_option( $this->parent->_base . 'twt_main_account' ) ),
			'numberposts' 	=> -1
		));

		if(!empty($this->app)){
			
			foreach($this->appList as $app){
				
				if( $this->app == $app->slug ){
					
					$this->includeApp($this->app);
					
					break;
				}
			}
		}
		elseif( is_admin() && isset($_REQUEST['post']) ){
			
			$terms = wp_get_post_terms( $_REQUEST['post'], 'app-type' );
			
			if(isset($terms[0]->slug)){
				
				$this->app = $terms[0]->slug;
				
				$this->includeApp($this->app);
			}
		}
	}
	
	public function includeApp($appSlug){
		
		// get api client
		
		$apiClient = preg_replace_callback(
			'/[-_](.)/', 
			function ($matches) {
				
				return '_'.strtoupper($matches[1]);
			},
			get_option('api_client_'.$appSlug)
		);

		// include api client
		
		$className = 'LTPLE_Client_App_'.  $apiClient;
		
		if(class_exists($className)){
			
			include( $this->parent->vendor . '/autoload.php' );

			$this->{$appSlug} = new $className($appSlug, $this->parent, $this);
		}
		else{

			echo 'Could not found API Client: "'.$apiClient.'"';
			exit;
		}		
	}

	public function get_niche_terms(){
	
		$terms = get_option( $this->parent->_base . 'niche_terms' );
		
		if(is_string($terms)){
			
			$terms 	= explode( PHP_EOL, $terms );
			$terms	= array_map('trim',$terms);
			$terms 	= array_filter($terms);
		}
		
		return $terms;
	}
	
	public function get_niche_hashtags(){
	
		$terms = get_option( $this->parent->_base . 'niche_hashtags' );
		
		if(is_string($terms)){
			
			$terms 	= explode( PHP_EOL, $terms );
			$terms	= array_map('trim',$terms);
			$terms	= array_map('strtolower',$terms);
			$terms 	= array_filter($terms);
		}
		
		return $terms;
	}
	
	public function newAppConnected(){
		
		do_action( 'ltple_new_app_connected' );
	}
	
	public function getAppUrl( $appSlug, $action='connect' ){
		
		$ref_url = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
		
		$url = $this->parent->urls->editor . '?media&app='.$appSlug.'&action='.$action.'&ref='.str_replace(urlencode('output=widget'), urlencode('output=default'),$ref_url);
		
		return $url;
	}
	
	public function getUserApps($user_id = NULL, $app_slug=''){
		
		$apps = null;
		
		if(is_numeric($user_id)){
			
			$apps = get_posts(array(
					
				'author'      => $user_id,
				'post_type'   => 'user-app',
				'post_status' => 'publish',
				'numberposts' => -1
			));

			if( !empty($apps) && !empty($app_slug) ){
				
				foreach($apps as $i => $app){

					if( $app_slug != strtok($app->post_title, ' - ')){

						unset($apps[$i]);
					}
				}
			}
		}
		
		return $apps;
	}
	
	public function getAppData($app_id, $user_id = NULL, $array = false ){
		
		$app_data = NULL;
		
		if( is_numeric($app_id) ){
			
			$app = get_post($app_id);				

			if( isset($app->post_author) ){

				if( intval($app->post_author) != intval($user_id) && !in_array_field($app->ID, 'ID', $this->mainApps) ){
					
					echo 'User app access restricted...';
					exit;
				}
				else{

					$app_data = json_decode(get_post_meta( $app->ID, 'appData', true ),$array);
				}
			}
		}
		else{
			
			echo 'Wrong app id request...';
			exit;
		}

		return $app_data;
	}
} 
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Apps extends LTPLE_Client_Object {
	
	var $parent;
	var $app;
	var $mainApps;
	var $taxonomy;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent) {
		
		$this->parent 	= $parent;
		
		$this->taxonomy = 'app-type';
		
		$this->parent->register_post_type( 'user-app', __( 'User Apps', 'live-template-editor-client' ), __( 'User Apps', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-app',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'author'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_taxonomy( 'app-type', __( 'App Type', 'live-template-editor-client' ), __( 'App Type', 'live-template-editor-client' ),  array('user-image','user-bookmark','user-app'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort'					=> '',
		));
		
		add_action( 'add_meta_boxes', function(){
			
			$this->parent->admin->add_meta_box (
				
				'appData',
				__( 'App Data', 'live-template-editor-client' ), 
				array("user-app"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'appSettings',
				__( 'App Settings', 'live-template-editor-client' ), 
				array("user-app"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'appRequests',
				__( 'App Requests', 'live-template-editor-client' ), 
				array("user-app"),
				'advanced'
			);
			
		});
		
		// get current app
		
		if(!empty($_REQUEST['app'])){
			
			$this->app = $_REQUEST['app'];
		}
		elseif(!empty($_SESSION['app'])){
			
			$this->app = $_SESSION['app'];
			
			// flush app session
			
			$_SESSION['app'] = '';
		}
		
		add_filter('wp_loaded', array( $this, 'init_apps'));
		
		add_filter("user-app_custom_fields", array( $this, 'get_fields' ));
	}
	
	// Add app data custom fields

	public function get_fields( $fields = [] ){
		
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
	
	public function init_apps(){
		
		// get all apps
		
		$this->list = $this->get_terms( $this->taxonomy, array(
			
			'blogger' => array(
			
				'name' 		=> 'Blogger',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/blogger.png',
					'types' 	=> array('networks','images'),
					'api_client'=> 'blogger',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password', 'password' ),
						'key' 	=> array ( 'goo_api_project', 'goo_consumer_key', 'goo_consumer_secret' ),
						'value' => array ( '', '', ''),
					),
				),
			),
			'google-plus' => array(
			
				'name' 		=> 'Google +',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/google-plus.png',
					'types' 	=> array('networks','images'),
					'api_client'=> 'google-plus',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password', 'password' ),
						'key' 	=> array ( 'goo_api_project', 'goo_consumer_key', 'goo_consumer_secret' ),
						'value' => array ( '', '', ''),
					),
				),
			),
			'imgur' => array(
			
				'name' 		=> 'Imgur',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/imgur.jpg',
					'types' 	=> array('images'),
					'api_client'=> 'imgur',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password' ),
						'key' 	=> array ( 'imgur_consumer_key', 'imgur_consumer_secret' ),
						'value' => array ( '', '' ),
					),
				),
			),
			'paypal-me' => array(
			
				'name' 		=> 'Paypal.me',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/payme.png',
					'types' 	=> array('payment'),
					'api_client'=> 'bookmark',
					'parameters'=> array (
					
						'input' => array ( 'url', 'filename' ),
						'key' 	=> array ( 'resource', 'amount' ),
						'value' => array ( 'https://www.paypal.me/{username}', '0'),
					),
				),
			),
			'tumblr' => array(
			
				'name' 		=> 'Tumblr',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/tumblr.png',
					'types' 	=> array('networks','images','blogs'),
					'api_client'=> 'tumblr',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password' ),
						'key' 	=> array ( 'tblr_consumer_key', 'tblr_consumer_secret' ),
						'value' => array ( '', ''),
					),
				),
			),
			'twitter' => array(
			
				'name' 		=> 'Twitter',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/twitter.jpg',
					'types' 	=> array('networks','images'),
					'api_client'=> 'twitter',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password' ),
						'key' 	=> array ( 'twt_consumer_key', 'twt_consumer_secret' ),
						'value' => array ( '', ''),
					),
				),
			),
			'venmo' => array(
			
				'name' 		=> 'Venmo',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/venmo.jpg',
					'types' 	=> array('payment'),
					'api_client'=> 'bookmark',
					'parameters'=> array (
					
						'input' => array ( 'url', 'parameter', 'parameter', 'parameter', 'parameter' ),
						'key' 	=> array ( 'resource', 'txn', 'audience', 'amount', 'note' ),
						'value' => array ( 'https://venmo.com/{username}', 'pay', 'public|friends|private', '0', ''),
					),
				),
			),
			'wordpress' => array(
			
				'name' 		=> 'Wordpress',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/wordpress.png',
					'types' 	=> array('images','blogs'),
					'api_client'=> 'wordpress',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password' ),
						'key' 	=> array ( 'wpcom_consumer_key', 'wpcom_consumer_secret' ),
						'value' => array ( '', ''),
					),
				),
			),
			'youtube' => array(
			
				'name' 		=> 'Youtube',
				'options' 	=> array(
				
					'thumbnail' => $this->parent->assets_url . 'images/apps/youtube.jpg',
					'types' 	=> array('images','videos'),
					'api_client'=> 'youtube',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password', 'password' ),
						'key' 	=> array ( 'goo_api_project', 'goo_consumer_key', 'goo_consumer_secret' ),
						'value' => array ( '', '', ''),
					),
				),
			),
			
		),'DESC');

		// get custom fields
		
		foreach( $this->list as $app ){
			
			$app->thumbnail = get_option('thumbnail_'.$app->slug);
			$app->types 	= get_option('types_'.$app->slug);
			//$app->parameters= get_option('parameters_'.$app->slug);
		}
		
		// get main apps
		
		$this->mainApps = get_posts(array(

			'post_type'   	=> 'user-app',
			'post_status' 	=> 'publish',
			'post__in' 		=> array( get_option( $this->parent->_base . 'wpcom_main_account' ), get_option( $this->parent->_base . 'twt_main_account' ) ),
			'numberposts' 	=> -1
		));

		if(!empty($this->app)){
			
			foreach($this->list as $app){
				
				if( $this->app == $app->slug ){
					
					$this->includeApp($this->app);
					
					break;
				}
			}
		}
		elseif( is_admin() && isset($_REQUEST['post']) ){
			
			$terms = wp_get_post_terms( $_REQUEST['post'], $this->taxonomy );
			
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
	
	public function parse_url_fields($url,$prefix='_'){
		
		$fields = array();
	
		preg_match_all('#{(.*?)}#', $url, $matches);
		
		if( isset($matches[1]) ){	

			foreach($matches[1] as $match){
					
				$fields[$match] = array(
				
					'type'				=> 'text',
					'id'				=> $prefix.$match,
					'name'				=> $prefix.$match,
					'placeholder'		=> $match,
					'style'				=> 'width:100px;display:inline-block;',
					'description'		=> ''
				);
			}
		}
		
		return $fields;
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
	
	public function getAppUrl( $appSlug, $action='connect', $tab='image-library' ){
		
		$ref_url = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
		
		$url = $this->parent->urls->editor . '?media='.$tab.'&app='.$appSlug.'&action='.$action.'&ref='.str_replace(urlencode('output=widget'), urlencode('output=default'),$ref_url);
		
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
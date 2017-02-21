<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client {

	/**
	 * The single instance of LTPLE_Client.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	public $_dev = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;
	public $_base;
	
	public $_time;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	public $server;
	public $user;
	public $layer;
	public $message;
	public $triggers;
	
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	 
	public function __construct ( $file = '', $version = '1.0.0' ) {
		
		$this->_version = $version;
		$this->_token 	= 'ltple';
		$this->_base 	= 'ltple_';
		
		if( isset($_GET['_']) && is_numeric($_GET['_']) ){
			
			$this->_time = intval($_GET['_']);
		}
		else{
			
			$this->_time = time();
		}

		$this->message = '';
		
		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->vendor  		= WP_CONTENT_DIR . '/vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix = '';
		
		register_activation_hook( $this->file, array( $this, 'install' ) );

		$this->client 		= new LTPLE_Client_Client( $this );
		$this->request 		= new LTPLE_Client_Request( $this );
		$this->urls 		= new LTPLE_Client_Urls( $this );
		$this->stars 		= new LTPLE_Client_Stars( $this );
		$this->login 		= new LTPLE_Client_Login( $this );
		
		// Load API for generic admin functions
		
		$this->admin 	= new LTPLE_Client_Admin_API( $this );
		$this->cron 	= new LTPLE_Client_Cron( $this );
		$this->email 	= new LTPLE_Client_Email( $this );
		
		$this->api 		= new LTPLE_Client_Json_API( $this );
		$this->server 	= new LTPLE_Client_Server( $this );
		
		$this->apps 	= new LTPLE_Client_Apps( $this );
		
		$this->whois 	= new LTPLE_Client_Whois( $this );
		
		$this->leads 	= new LTPLE_Client_Leads( $this );
		
		$this->plan 	= new LTPLE_Client_Plan( $this );		
		
		if( is_admin() ) {
			
			// Load admin JS & CSS
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
			
			add_filter( 'page_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );
			add_filter( 'post_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );

			$this->users 	= new LTPLE_Client_Users( $this );

			$this->channels = new LTPLE_Client_Channels( $this );
			
			$this->rights 	= new LTPLE_Client_Rights( $this );
			
			add_action( 'init', array( $this, 'ltple_client_backend_init' ));	
		}
		elseif(isset($_POST['imgData']) && isset($_POST["submitted"])&& isset($_POST["download_image_nonce_field"]) && $_POST["submitted"]=='true'){
			
			// dowload meme image
			
			//wp_verify_nonce($_POST["download_image_nonce_field"], "download_image_nonce");
			
			$data = sanitize_text_field($_POST['imgData']);
			
			list($type, $data) = explode(';', $data);
			list(, $data)      = explode(',', $data);
			
			header('Content-Description: File Transfer');
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename= ltple_meme_image.png");
			
			exit(base64_decode($data));
		}
		else{
			
			// Load frontend JS & CSS
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			$this->layer 	= new LTPLE_Client_Layer( $this );
			$this->image 	= new LTPLE_Client_Image();
			$this->profile 	= new LTPLE_Client_Profile( $this );

			add_action( 'init', array( $this, 'ltple_client_frontend_init' ));	
			
			add_action( 'wp_head', array( $this, 'ltple_client_header') );
			
			add_action( 'wp_footer', array( $this, 'ltple_client_footer') );
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// add user taxonomy custom fields
		
		add_action( 'show_user_profile', array( $this, 'get_user_plan_and_pricing' ) );
		add_action( 'edit_user_profile', array( $this, 'get_user_plan_and_pricing' ) );
		
		// save user taxonomy custom fields
		
		add_action( 'personal_options_update', array( $this, 'save_custom_user_taxonomy_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_taxonomy_fields' ) );	
		
		// add editor shortcodes
		
		add_shortcode('ltple-client-editor', array( $this , 'add_shortcode_editor' ) );
		add_shortcode('subscription-plan', array( $this, 'add_shortcode_subscription_plan' ) );
		
		// add subscription-plan
		
		add_filter("subscription-plan_custom_fields", array( $this, 'add_subscription_plan_custom_fields' ));		
		add_filter('manage_subscription-plan_posts_columns', array( $this, 'set_subscription_plan_columns'));
		add_action('manage_subscription-plan_posts_custom_column', array( $this, 'add_subscription_plan_column_content'), 10, 2);
		add_filter('nav_menu_css_class', array( $this, 'change_subscription_plan_menu_classes'), 10,2 );
		
		// add email-campaign
		
		add_filter("email-campaign_custom_fields", array( $this, 'add_campaign_trigger_custom_fields' ));
		
		// add user-plan
		
		add_filter("user-plan_custom_fields", array( $this, 'add_user_plan_custom_fields' ));
		
		// add user-image
	
		add_filter('manage_user-image_posts_columns', array( $this, 'set_user_image_columns'));
		add_action('manage_user-image_posts_custom_column', array( $this, 'add_user_image_column_content'), 10, 2);

		// Custom default layer template

		add_filter('template_include', array( $this, 'editor_templates'), 1 );
		
		add_action('template_redirect', array( $this, 'editor_output' ));

		add_filter( 'pre_get_posts', function($query) {

			if ($query->is_search && !is_admin() ) {
				
				$query->set('post_type',array('post','page'));
			}

			return $query;
		});

	} // End __construct ()
	
	private function ltple_get_secret_iv(){
		
		//$secret_iv = md5( $this->user_agent . $this->user_ip );
		//$secret_iv = md5( $this->user_ip );
		$secret_iv = md5( 'another-secret' );	

		return $secret_iv;
	}	
	
	private function ltple_encrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this->client->key );
		
		$secret_iv = $this->ltple_get_secret_iv();
		
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = $this->base64_urlencode($output);

		return $output;
	}
	
	private function ltple_decrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this->client->key );
		
		$secret_iv = $this->ltple_get_secret_iv();

		// hash
		$key = hash( 'sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16);

		$output = openssl_decrypt($this->base64_urldecode($string), $encrypt_method, $key, 0, $iv);

		return $output;
	}
	
	public function ltple_encrypt_uri($uri,$len=250,$separator='/'){
		
		$uri = wordwrap($this->ltple_encrypt_str($uri),$len,$separator,true);
		
		return $uri;
	}
	
	public function ltple_decrypt_uri($uri,$separator='/'){
		
		$uri = $this->ltple_decrypt_str(str_replace($separator,'',$uri));
		
		return $uri;
	}
	
	public function base64_urlencode($inputStr=''){

		return strtr(base64_encode($inputStr), '+/=', '-_,');
	}

	public function base64_urldecode($inputStr=''){

		return base64_decode(strtr($inputStr, '-_,', '+/='));
	}
	
	public function ltple_client_frontend_init(){
		
		//get current user
		
		if( $this->request->is_remote ){

			$this->user = wp_set_current_user( get_user_by( 'id', $this->ltple_decrypt_str($_SERVER['HTTP_X_FORWARDED_USER'])));
		}
		elseif(1==2 && !empty($this->_dev) ){
			
			//debug user session
			
			$this->user = wp_set_current_user(15);
		}
		else{
			
			$this->user = wp_get_current_user();
		}
		
		$this->user->loggedin = is_user_logged_in();		
		
		if($this->user->loggedin){
		
			// get is admin
			
			$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );
			
			// get user last seen
			
			$this->user->last_seen = intval( get_user_meta( $this->user->ID, $this->_base . '_last_seen',true) );
			
			// get user layers
			
			$this->user->layers = get_posts(array(
			
				'author'      => $this->user->ID,
				'post_type'   => 'user-layer',
				'post_status' => 'publish',
				'numberposts' => -1
			));	
			
			// get user stars
			
			$this->user->stars = $this->stars->get_count($this->user->ID);
			
			// get user ref id
			
			$this->user->refId = $this->ltple_encrypt_uri( 'RI-' . $this->user->ID );
			
			// get user rights
			
			$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );

			//get user layer
			
			if( $this->layer->type == 'user-layer' ){
				
				if( $this->user->is_admin ){
				
					$q = get_posts(array(
					
						'name'        => $this->layer->slug,
						'post_type'   => 'user-layer',
						'post_status' => 'publish',
						'numberposts' => 1
					));						
				}
				else{
					
					$q = $this->user->layers;				
				}
				
				//var_dump( $q );exit;
				
				if(isset($q[0])){
					
					$this->user->layer=$q[0];
				}
				
				unset($q);
			}
		}
		
		// newsletter unsubscription
		
		if(!empty($_GET['unsubscribe'])){
		
			$unsubscriber_id = $this->ltple_decrypt_uri(sanitize_text_field($_GET['unsubscribe']));
			
			if(is_numeric($unsubscriber_id)){
				
				update_user_meta(intval($unsubscriber_id), $this->_base . '_can_spam', 'false');

				$this->message ='<div class="alert alert-success">';

					$this->message .= '<b>Congratulations</b>! You successfully unsbuscribed from the newsletter';

				$this->message .='</div>';
			}
		}
	}
	
	
	public function ltple_client_backend_init(){
		
		//get current user
		
		$this->user = wp_get_current_user();
		
		// get is admin
		
		$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );

		// get user stars
		
		$this->user->stars = $this->stars->get_count($this->user->ID);		
		
		// get user rights
		
		$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );
		
		// get editedUser
		
		if(strpos($_SERVER['SCRIPT_NAME'],'user-edit.php')>0 && isset($_REQUEST['user_id']) ){
			
			$this->editedUser = get_userdata(intval($_REQUEST['user_id']));
	
			$this->editedUser->rights  = json_decode( get_user_meta( $this->editedUser->ID, $this->_base . 'user-rights',true) );
		}
		else{
			
			$this->editedUser = $this->user;
		}
	}
	
	public function remove_custom_post_quick_edition( $actions, $post ){

		if( $post->post_type != 'page' && $post->post_type != 'post' ){
		
			//unset( $actions['edit'] );
			//unset( $actions['view'] );
			unset( $actions['trash'] );
			unset( $actions['inline hide-if-no-js'] );
		}
		
		return $actions;
	}		
	
	public function ltple_schedule_series( $series_id, $user){
					
		$email_series = get_post_meta( $series_id, 'email_series',true);

		// trigger register email

		if( isset( $email_series['model'] ) && isset( $email_series['days'] ) ){
			
			foreach($email_series['model'] as $e => $model_id){
				
				if( is_numeric($model_id) ){
					
					$model_id = intval($model_id);
					
					if( $model_id > 0 ){
						
						if( intval($email_series['days'][$e]) == 0){
							
							wp_schedule_single_event( ( time() + ( 60 * 1 ) ) , 'ltple_send_email_event' , [$model_id,$user->user_email] );
						}
						else{
							
							wp_schedule_single_event( ( time() + ( intval( $email_series['days'][$e] ) * 3600 * 24 ) ), 'ltple_send_email_event', [$model_id,$user->user_email] );
						}									
					}
				}
			}
		}
	}

	public function change_subscription_plan_menu_classes($classes, $item){
		
		global $post;
		
		if(get_post_type($post) == 'subscription-plan'){
			
			$page = get_page_by_path('editor');

			if($page->ID == get_post_meta( $item->ID, '_menu_item_object_id', true )){
				
				$classes = str_replace( 'menu-item-'.$item->ID, 'menu-item-'.$item->ID.' current-menu-item', $classes ); // add the current_page_parent class to the page you want
			}
			else{
				
				$classes = str_replace( array('current-menu-item','current_page_parent'), '', $classes ); // remove all current_page_parent classes			
			}
		}
		
		return $classes;
	}

	public function get_app_types(){
		
		return array(
		
			'networks'  => [],
			'images'	=> [],
			'videos' 	=> [],
			'blogs' 	=> [],
		);
	}
	
	// Add campaign trigger custom fields

	public function add_campaign_trigger_custom_fields(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get image types

		$terms=get_terms( array(
				
			'taxonomy' => 'campaign-trigger',
			'hide_empty' => false,
		));
		
		$options=[];
		
		foreach($terms as $term){
			
			$options[$term->slug]=$term->name;
		}
		
		//get current email campaign
		
		$terms = wp_get_post_terms( $post_id, 'campaign-trigger' );
		
		$default='';

		if(isset($terms[0]->slug)){
			
			$default=$terms[0]->slug;
		}
		
		$fields[]=array(
			"metabox" =>
				array('name'=>"tagsdiv-campaign-trigger"),
				'id'=>"new-tag-campaign-trigger",
				'name'=>'tax_input[campaign-trigger]',
				'label'=>"",
				'type'=>'select',
				'options'=>$options,
				'selected'=>$default,
				'description'=>''
		);
		
		// get email models
		
		$q = get_posts(array(
		
			'post_type'   => 'email-model',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' 	  => 'title',
			'order' 	  => 'ASC'
		));
		
		$email_models=['' => 'no email model selected'];
		
		if(!empty($q)){
			
			foreach( $q as $model ){
				
				$email_models[$model->ID] = $model->post_title;
			}
		}
		
		//var_dump($email_models);exit;
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "email_series"),
				'type'				=> 'email_series',
				'id'				=> 'email_series',
				'label'				=> '',
				'email-models' 		=> $email_models,
				'model-selected'	=> '',
				'days-from-sub' 	=> 0,
				'description'		=> ''
		);
		
		return $fields;
	}

	
	// Add user plan data custom fields

	public function add_user_plan_custom_fields(){
		
		$user_plan  = get_post();
		$layer_plan = $this->get_user_plan_info( intval($user_plan->post_author) );

		$fields=[];
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'	=>"userPlanValue"),
				'id'			=>	"userPlanValue",
				'label'			=>	"",
				'type'			=>	'plan_value',
				'plan'			=>	$layer_plan,
				'placeholder'	=>	"Plan Value",
				'description'	=>	''
		);
		
		return $fields;
	}
	
	public function add_subscription_plan_custom_fields(){
			
		$fields = [];
		
		//get options
		
		$options = $this -> get_layer_custom_taxonomies_options();
		
		//var_dump($options);exit;
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "plan_options"),
				'type'		=> 'checkbox_multi_plan_options',
				'id'		=> 'plan_options',
				'label'		=> '',
				'options'	=> $options,
				'description'=> ''
		);
		
		// get email models
		
		$q = get_posts(array(
		
			'post_type'   => 'email-model',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' 	  => 'title',
			'order' 	  => 'ASC'
		));
		
		$email_models=['' => 'no email model selected'];
		
		if(!empty($q)){
			
			foreach( $q as $model ){
				
				$email_models[$model->ID] = $model->post_title;
			}
		}
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "email_series"),
				'type'				=> 'email_series',
				'id'				=> 'email_series',
				'label'				=> '',
				'email-models' 		=> $email_models,
				'model-selected'	=> '',
				'days-from-sub' 	=> 0,
				'description'		=> ''
		);
		
		return $fields;
	}
	
	public function set_subscription_plan_columns($columns){

		// Remove description, posts, wpseo columns
		$columns = [];
		
		$columns['cb'] = '<input type="checkbox" />';
		$columns['title'] = 'Title';
		$columns['shortcode'] = 'Shortcode';
		$columns['date'] = 'Date';

		return $columns;		
	}
	
	public function add_subscription_plan_column_content($column_name, $post_id){

		if($column_name === 'shortcode') {
			
			echo '<input style="width:200px;" type="text" name="shortcode" value="[subscription-plan id=\'' . $post_id . '\']" ' . disabled( true, true, false ) . ' />';
		}		
	}
	
	public function set_user_image_columns($columns){

		// Remove description, posts, wpseo columns
		$columns = [];
		
		$columns['cb'] 					= '<input type="checkbox" />';
		$columns['title'] 				= 'Title';
		$columns['author'] 				= 'Author';
		$columns['taxonomy-app-type'] 	= 'App';
		$columns['image'] 				= 'Image';
		$columns['date'] 				= 'Date';

		return $columns;		
	}
	
	public function add_user_image_column_content($column_name, $post_id){

		if($column_name === 'image') {
			
			$post = get_post($post_id);
			
			echo '<img src="' . $post->post_content . '" style="width:100px;" />';
		}		
	}
	
	public function editor_templates( $template_path ){

		if( isset($_GET['pr']) && is_numeric($_GET['pr']) ){
			
			$template_id = get_user_meta( intval($_GET['pr']) , 'ltple_profile_template', true );
			
			$template_id = floatval($template_id);
			
			if( ( $template_id > 0 && isset($this->profile->layer->ID) ) || $template_id == -2 ){
				
				$template_path = $this->views . $this->_dev . '/layer-profile.php';
			}
		}	
		elseif( is_single() ) {
			
			$post_type	= get_post_type();
			$post_id	= get_the_ID();
			$post_author= intval(get_post_field( 'post_author', $post_id ));
			
			$path = $template_path;
			
			if( isset( $_SERVER['HTTP_X_REF_KEY'] ) ){
				
				if( $_SERVER['HTTP_X_REF_KEY'] ){ //TODO improve ref rey validation via header
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					echo 'Malformed layer headers...';
					exit;
				}
			}
			elseif( $post_type == 'cb-default-layer' ){
				
				if( $this->user->loggedin && $this->user_has_layer( $post_id ) === true ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					$path = $this->views . $this->_dev .'/preview.php';
				}					
			}
			elseif( $post_type == 'user-layer' ){
				
				if( $this->user->loggedin && ( $this->user->is_admin || $post_author == $this->user->ID )){
				
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					echo 'You don\'t have access to this template...';
					exit;
				}				
			}
			elseif( file_exists($this->views . $this->_dev .'/'.$post_type.'.php') ){
				
				$path = $this->views . $this->_dev .'/'.$post_type.'.php';
			}
			
			if( file_exists( $path ) ) {

				$template_path = $path;
			}
		}
		
		return $template_path;
	}
	
	public function editor_output() {
			
		$this->all = new stdClass();
		
		// get all layer types
		
		$this->all->layerType = get_terms( array(
				
			'taxonomy' => 'layer-type',
			'hide_empty' => true,
		));
		
		// get all layer ranges
		
		$this->all->layerRange = get_terms( array(
				
			'taxonomy' => 'layer-range',
			'hide_empty' => true,
		));
		
		$this->all->imageType = get_terms( array(
				
			'taxonomy' => 'image-type',
			'hide_empty' => true,
		));
			
		// get layer type
				
		//$terms = wp_get_object_terms( $this->layer->id, 'layer-type' );
		//$this->layer->type = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');

		// get layer range
				
		$terms = wp_get_object_terms( $this->layer->id, 'layer-range' );
		$this->layer->range = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');
		
		// get layer price
		
		$this->layer->price = ( !empty($this->layer->range) ? intval( get_option('price_amount_' . $this->layer->range->slug) ) : 0 );
		
		// get user connected apps
		
		$this->user->apps = $this->apps->getUserApps($this->user->ID);
		
		// get triggers
		
		$this->triggers = new LTPLE_Client_Triggers( $this );

		// get user profile
			
		$this->user->profile = new LTPLE_Client_User_Profile( $this );
		
		// get user domains
			
		$this->user->domains = new LTPLE_Client_User_Domains( $this );
					
		// get user marketing channel
		
		$terms = wp_get_object_terms( $this->user->ID, 'marketing-channel' );
		$this->user->channel = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0]->slug : '');

		// get user plan

		$this->user->plan = $this->get_user_plan_info( $this->user->ID );
		$this->user->has_layer = $this->user_has_layer( $this->layer->id, $this->layer->type );
		
		// count user templates
			
		$this->user->layerCount = intval( count_user_posts( $this->user->ID, 'user-layer' ) );
		
		// Custom default layer post
		
		if($this->layer->type != '' && $this->layer->slug != ''){
			
			remove_all_filters("content_save_pre");
			remove_filter( 'the_content', 'wpautop' );
			
			// update user layer
			
			$this->update_user_layer();
		}
		
		//update user channel
		
		$this->update_user_channel();			
		
		//update user image
		
		$this->update_user_image();
		
		//get user plan
		
		$this->plan->update_user();
		
		// get editor iframe

		if( $this->user->loggedin===true && $this->layer->slug!='' && $this->layer->type!='' && $this->layer->key!='' && $this->server->url!==false ){
			
			if( $this->layer->key == md5( 'layer' . $this->layer->uri . $this->_time )){
				
				//include( $this->views . $this->_dev .'/editor-iframe.php' );
				include( $this->views . $this->_dev .'/editor-proxy.php' );
			}
			else{
				
				echo 'Malformed iframe request...';
				exit;					
			}
		}
		
		// Custom outputs
		
		if( isset( $_GET['output']) && $_GET['output'] == 'widget' ){
			
			include( $this->views . $this->_dev .'/widget.php' );
		}
		elseif( isset($_GET['api']) ){

			include($this->views . $this->_dev .'/api.php');
		}			
	}

	public function ltple_client_header(){

		//echo '<link rel="stylesheet" href="https://raw.githubusercontent.com/dbtek/bootstrap-vertical-tabs/master/bootstrap.vertical-tabs.css">';	
	}	
	
	public function ltple_client_footer(){
		
		?>

		<script>
		
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $this->settings->options->analyticsId; ?>', 'auto');
			ga('send', 'pageview');

		</script>
		<?php
	}
	
	public function add_shortcode_editor(){
		
		// vertical tab styling
		
		echo '<style>';
			echo '.pgheadertitle{display:none;}.tabs-left,.tabs-right{border-bottom:none;padding-top:2px}.tabs-left{border-right:0px solid #ddd}.tabs-right{border-left:0px solid #ddd}.tabs-left>li,.tabs-right>li{float:none;margin-bottom:2px}.tabs-left>li{margin-right:-1px}.tabs-right>li{margin-left:-1px}.tabs-left>li.active>a,.tabs-left>li.active>a:focus,.tabs-left>li.active>a:hover{border-left: 5px solid #F86D18;border-top:0;border-right:0;border-bottom:0; }.tabs-right>li.active>a,.tabs-right>li.active>a:focus,.tabs-right>li.active>a:hover{border-bottom:0px solid #ddd;border-left-color:transparent}.tabs-left>li>a{border-radius:4px 0 0 4px;margin-right:0;display:block}.tabs-right>li>a{border-radius:0 4px 4px 0;margin-right:0}.sideways{margin-top:50px;border:none;position:relative}.sideways>li{height:20px;width:120px;margin-bottom:100px}.sideways>li>a{border-bottom:0px solid #ddd;border-right-color:transparent;text-align:center;border-radius:4px 4px 0 0}.sideways>li.active>a,.sideways>li.active>a:focus,.sideways>li.active>a:hover{border-bottom-color:transparent;border-right-color:#ddd;border-left-color:#ddd}.sideways.tabs-left{left:-50px}.sideways.tabs-right{right:-50px}.sideways.tabs-right>li{-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg)}.sideways.tabs-left>li{-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg)}';
			echo 'span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {background-color: #F86D18;border: 1px solid #FF5722;}';
		echo '</style>';	
		
		if($this->user->loggedin){		

			include( $this->views . $this->_dev .'/navbar.php' );	
			
			if( empty( $this->user->channel ) && !isset($_POST['marketing-channel']) ){
				
				include($this->views . $this->_dev .'/channel-modal.php');
			}			

			if( isset($_GET['pr']) && !isset($this->profile->layer->ID) ){

				include($this->views . $this->_dev .'/profile.php');
			}				
			elseif( isset($_GET['media']) ){
				
				include($this->views . $this->_dev .'/media.php');
			}
			elseif( isset($_GET['app']) || !empty($_SESSION['app']) ){

				include($this->views . $this->_dev .'/apps.php');
			}
			elseif( isset($_GET['domain']) || !empty($_SESSION['domain']) ){

				include($this->views . $this->_dev .'/domains.php');
			}				
			elseif( isset($_GET['rank']) ){
				
				include($this->views . $this->_dev .'/ranking.php');
			}
			elseif( isset($_GET['my-profile']) ){
				
				include($this->views . $this->_dev .'/settings.php');
			}			
			elseif( $this->layer->uri != ''){
				
				if( $this->user->has_layer ){
					
					include( $this->views . $this->_dev .'/editor.php' );
				}
				else{
					
					include($this->views . $this->_dev .'/upgrade.php');
					include($this->views . $this->_dev .'/gallery.php');					
				}
			}
			else{
				
				include($this->views . $this->_dev .'/gallery.php');		
			}			
		}
		elseif( isset($_GET['pr']) && !isset($this->profile->layer->ID) ){

			include($this->views . $this->_dev .'/profile.php');
		}
		elseif( isset($_GET['rank']) ){
			
			include($this->views . $this->_dev .'/ranking.php');
		}
		else{
			
			echo'<div style="font-size:20px;padding:20px;" class="alert alert-warning">';
				
				echo'You need to log in first...';
				
				echo'<div class="pull-right">';

					echo'<a style="margin:0 2px;" class="btn-lg btn-success" href="'. wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( 'http://'.$_SERVER['SCRIPT_URI'] ) .'&action=register">Register</a>';
				
				echo'</div>';
				
			echo'</div>';		
			
			include($this->views . $this->_dev .'/gallery.php');
		}
	}
	
	public function get_user_plan_custom_taxonomies(){
		
		$taxonomies=[];
		$taxonomies[] = array(
			'name'		=> 'Layer Types',
			'taxonomy'	=> 'layer-type',
			'hierarchical' => false
		);
		$taxonomies[] = array(
			'name'		=> 'Layer Ranges',
			'taxonomy'	=> 'layer-range',
			'hierarchical' => true
		);
		$taxonomies[] = array(
			'name'		=> 'Account Options',
			'taxonomy'	=> 'account-option',
			'hierarchical' => false
		);

		return $taxonomies;
	}
	
	public function get_layer_taxonomy_options($taxonomy,$term,$price_currency='$'){

		if(!$price_amount = get_option('price_amount_' . $term->slug)){
			
			$price_amount = 0;
		} 
		
		if(!$price_period = get_option('price_period_' . $term->slug)){
			
			$price_period = 'month';
		}
		
		if(!$storage_amount = get_option('storage_amount_' . $term->slug)){
			
			$storage_amount = 0;
		}
		
		if(!$storage_unit = get_option('storage_unit_' . $term->slug)){
			
			$storage_unit = 'templates';
		}
		
		if(!$form = get_option('meta_' . $term->slug)){
			
			$form = [];
		} 
		
		$options=[];
		$options['price_currency']	= $price_currency;
		$options['price_amount']	= $price_amount;
		$options['price_period']	= $price_period;
		$options['storage_amount']	= $storage_amount;
		$options['storage_unit']	= $storage_unit;
		$options['form']			= $form;
		
		return $options;
	}
	
	public function get_layer_custom_taxonomies_options(){
		
		//get custom taxonomies
		
		$taxonomies= $this -> get_user_plan_custom_taxonomies();

		// get custom taxonomies options
		
		$options=[];
		
		foreach($taxonomies as $t){
		
			$taxonomy = $t['taxonomy'];
			$taxonomy_name = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			//get custom taxonomy terms
			
			$terms=get_terms( array(
					
				'taxonomy' => $taxonomy,
				'hide_empty' => false
			));

			//var_dump($terms);exit;
			
			foreach($terms as $term){

				$options[$taxonomy_name][]=$term;
			}
		}

		return 	$options;	
	}
	
	public function get_user_plan_id( $user_id, $create=false ){	
	
		// get user plan id
	
		$q = get_posts(array(
		
			'author'      => $user_id,
			'post_type'   => 'user-plan',
			'post_status' => 'publish',
			'numberposts' => 1
		));
		
		if(!empty($q)){
			
			$user_plan_id = $q[0]->ID;
		}		
		elseif($create===true){
			
			$user_plan_id = wp_insert_post(array(
			
			  'post_title'   	 => wp_strip_all_tags( 'user_' . $user_id ),
			  'post_status'   	=> 'publish',
			  'post_type'  	 	=> 'user-plan',
			  'post_author'   	=> $user_id
			));
		}
		else{
			
			$user_plan_id = 0;
		}
		
		return $user_plan_id;
	}
	
	public function get_layer_plan_info($item_id){	

		$taxonomies = $this -> get_user_plan_custom_taxonomies();
		//var_dump($taxonomies);exit;
		
		$user_plan = [];
		
		$user_plan['id'] = $item_id;
		
		$user_plan['info']['total_price_amount'] 	= 0;
		$user_plan['info']['total_fee_amount'] 		= 0;
		$user_plan['info']['total_price_period'] 	= 'month';
		$user_plan['info']['total_fee_period'] 		= 'once';
		$user_plan['info']['total_price_currency'] 	= '$';
		
		foreach($taxonomies as $i => $t){
			
			$taxonomy 		 = $t['taxonomy'];
			$taxonomy_name 	 = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$user_plan['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
			$user_plan['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
			$user_plan['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
			$user_plan['taxonomies'][$taxonomy]['terms']			= [];
			
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
			
			if ( !empty($terms) ) {
				
				foreach ( $terms as $term ) {						

					$term_slug = $term->slug;
					
					$has_term = $in_term = is_object_in_term( $item_id, $taxonomy, $term->term_id );

					if($is_hierarchical === true && $term->parent > 0 && $has_term === false ){
						
						$parent_id = $term->parent;
						
						while( $parent_id > 0 ){
							
							if($has_term === false){
								
								foreach($terms as $parent){
									
									if( $parent->term_id == $parent_id ){
										
										$has_term = is_object_in_term( $item_id, $taxonomy, $parent->term_id );
										
										$parent_id = $parent->parent;
										
										break;
									}
								}								
							}
							else{
								
								break;
							}
						}					
					}
					
					// push terms
				
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]				= $term_slug;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]				= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]			= $term->term_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			 	= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]	= $term->term_taxonomy_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		 	= $term->taxonomy;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	 	= $term->description;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			 	= $term->count;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]			= $has_term;
					
					if( $in_term === true ){
						
						$options = $this->get_layer_taxonomy_options( $taxonomy, $term );
						
						$user_plan['info']['total_fee_amount']	 = $this -> sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_fee_amount'], $options, $user_plan['info']['total_fee_period'] );
						$user_plan['info']['total_price_amount'] = $this -> sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_price_amount'], $options, $user_plan['info']['total_price_period'] );
						$user_plan['info']['total_storage'] 	 = $this -> sum_custom_taxonomy_total_storage( $user_plan['info']['total_storage'], $options);
					}					
				}
			}
		}
		
		//echo'<pre>';
		//var_dump($user_plan);exit;
		
		return $user_plan;	
	}
	
	public function get_user_plan_info( $user_id ){	

		$user_plan_id 	 = $this->get_user_plan_id( $user_id );

		$taxonomies = $this -> get_user_plan_custom_taxonomies();
		//var_dump($taxonomies);exit;
		
		$user_plan = [];
		
		$user_plan['id'] = $user_plan_id;
		
		$user_plan['info']['total_price_amount'] 	= 0;
		$user_plan['info']['total_fee_amount'] 		= 0;
		$user_plan['info']['total_price_period'] 	= 'month';
		$user_plan['info']['total_fee_period'] 		= 'once';
		$user_plan['info']['total_price_currency'] 	= '$';
		
		foreach($taxonomies as $i => $t){
			
			$taxonomy 		 = $t['taxonomy'];
			$taxonomy_name 	 = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$user_plan['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
			$user_plan['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
			$user_plan['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
			$user_plan['taxonomies'][$taxonomy]['terms']			= [];
			
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
			
			if ( !empty($terms) ) {
				
				foreach ( $terms as $term ) {						

					$term_slug = $term->slug;
					
					$has_term = $in_term = false;
					
					if($user_plan_id > 0 ){
						
						$has_term = $in_term = is_object_in_term( $user_plan_id, $taxonomy, $term->term_id );
					}
					
					if($is_hierarchical === true && $term->parent > 0 && $has_term === false ){
						
						$parent_id = $term->parent;
						
						while( $parent_id > 0 ){
							
							if($has_term === false){
								
								foreach($terms as $parent){
									
									if( $parent->term_id == $parent_id ){
										
										if($user_plan_id > 0 ){
											
											$has_term = is_object_in_term( $user_plan_id, $taxonomy, $parent->term_id );
										}
										
										$parent_id = $parent->parent;
										
										break;
									}
								}								
							}
							else{
								
								break;
							}
						}					
					}
					
					// push terms
				
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]			= $term_slug;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]			= $term->term_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]= $term->term_taxonomy_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		= $term->taxonomy;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	 	= $term->description;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			= $term->count;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]		= $has_term;
					
					if( $in_term === true ){
						
						$options = $this->get_layer_taxonomy_options( $taxonomy, $term );

						$user_plan['info']['total_fee_amount']	 = $this -> sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_fee_amount'], $options, $user_plan['info']['total_fee_period'] );
						$user_plan['info']['total_price_amount'] = $this -> sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_price_amount'], $options, $user_plan['info']['total_price_period'] );
						$user_plan['info']['total_storage'] 	 = $this -> sum_custom_taxonomy_total_storage( $user_plan['info']['total_storage'], $options);
					}					
				}
			}
		}
		
		// get stored user plan value
		
		$user_plan_value = get_post_meta( $user_plan_id, 'userPlanValue',true );
		
		// compare it with current value
		
		if( $user_plan_value=='' || $user_plan['info']['total_price_amount'] != intval($user_plan_value) ){

			update_post_meta( $user_plan_id, 'userPlanValue', $user_plan['info']['total_price_amount'] );
		}
		
		//echo'<pre>';
		//var_dump($user_plan);exit;
		
		return $user_plan;	
	}
	
	public function user_has_layer( $item_id, $layer_type = 'default-layer' ){
		
		$user_has_layer = false;
		
		if($layer_type == 'default-layer'){
			
			$user_has_layer = false;
			
			$layer_plan = $this->get_layer_plan_info( $item_id );
			
			foreach($layer_plan['taxonomies'] as $taxonomy => $tax){

				foreach($tax['terms'] as $term_slug => $term){
					
					if(!isset($this->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug])){
						
						//var_dump($this->user->plan['taxonomies'][$taxonomy]);exit;
					}
					
					if( $term['has_term']===true ){
						
						$user_has_layer = true;
						
						if( !isset( $this->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug] ) ){
							
							$user_has_layer = false;
							break 2;
						}
						elseif( $this->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug]['has_term'] !== $term['has_term'] ){
							
							$user_has_layer = false;
							break 2;
						}				
					}
				}		
			}			
		}
		elseif($layer_type == 'user-layer'){
			
			$user_has_layer = true;
		}
		
		return $user_has_layer;
	}
	
	public function user_has_plan( $plan_id ){
		
		$user_has_plan = false;
		
		$plan_options = get_post_meta( $plan_id, 'plan_options', true );
		$plan_options = array_flip($plan_options);
		
		if(!empty($this->user->plan['taxonomies'])){
			
			foreach($this->user->plan['taxonomies'] as $taxonomy => $tax){
				
				foreach($tax['terms'] as $term_slug => $term){

					if(isset($plan_options[$term_slug])){
						
						$user_has_plan = true;

						if( $term['has_term']!==true ){
							
		
							$user_has_plan = false;
							break 2;
						}
					}
				}			
			}
		}

		return $user_has_plan;
	}
	
	public function get_price_periods(){
		
		$periods=[];
		$periods['day']		='day';
		$periods['month']	='month';
		$periods['year']	='year';
		$periods['once']	='once';
		
		return $periods;
	}
	
	public function get_storage_units(){
		
		$units=[];
		$units['templates']='templates';
		//$units['octet']='octet';
		
		return $units;
	}
	
	public function sum_custom_taxonomy_total_price_amount( &$total_price_amount=0, $options, $period='month'){
		
		if($period == $options['price_period']){
			
			$total_price_amount = $total_price_amount + floatval($options['price_amount']);
		}
		elseif($period == 'month'){
			
			if($options['price_period'] == 'day'){
				
				$total_price_amount = $total_price_amount + ( 30 * floatval($options['price_amount']) );
			}
			elseif($options['price_period'] == 'year'){
				
				$total_price_amount = $total_price_amount + (  floatval($options['price_amount']) / 12 );
			}
		}
		
		return $total_price_amount;
	}
	
	
	public function sum_custom_taxonomy_total_storage( &$total_storage=[], $options){
		
		$storage_unit=$options['storage_unit'];
		$storage_amount=round(intval($options['storage_amount']),0);
		
		if(!isset($total_storage[$storage_unit])){
			
			$total_storage[$storage_unit] = $storage_amount;
		}
		else{
			
			$total_storage[$storage_unit]= $total_storage[$storage_unit] + $storage_amount;
		}
		
		return $total_storage;
	}
	
	public function get_user_plan_and_pricing( $user, $context='admin-dashboard' ) {
		
		if( current_user_can( 'administrator' ) ){
				
			$user_plan_id = $this->get_user_plan_id($user->ID);
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		='month';
			$total_fee_period		='once';
			$total_price_currency	='$';
			
			$taxonomies = $this -> get_user_plan_custom_taxonomies();

			echo '<div class="postbox">';
				
				echo '<h3 style="margin:10px;">' . __( 'Plan & Pricing', 'live-template-editor-client' ) . '</h3>';
			
				echo '<table class="widefat fixed striped" style="border:none;">';
					
					if( $user_plan_id > 0 ){
					
						foreach($taxonomies as $t){
						
							$taxonomy = $t['taxonomy'];
							$taxonomy_name = $t['name'];
							$is_hierarchical = $t['hierarchical'];
						
							$tax = get_taxonomy( $taxonomy );

							/* Make sure the user can assign terms of the user taxonomy before proceeding. */
							if ( !current_user_can( $tax->cap->assign_terms ) )
								return;

							/* Get the terms of the user taxonomy. */
							$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

							echo '<tr>';
							
								echo '<th style="width:300px;">';
									
									echo '<label for="'.$taxonomy.'">'. __( $taxonomy_name, 'live-template-editor-client' ) . '</label>';
								
								echo '</th>';

								/* If there are any layer-type terms, loop through them and display checkboxes. */
								if ( !empty( $terms ) ) {
									
									echo '<td style="width:250px;">';
									
										foreach ( $terms as $term ) {							
											
											$input_name = $taxonomy.'[]';
											$input_value = esc_attr( $term->slug );
											$input_label =  $term->name;
											
											$checked = checked( true, is_object_in_term( $user_plan_id, $taxonomy, $term->term_id ), false );
											
											if( 1==1 ){
												
												$disabled = '';
											}
											else{
												
												$disabled = disabled( true, true, false ); // untill subscription edition implemented	
											}

											echo '<input type="checkbox" name="'.$input_name.'" id="'.$taxonomy.'-'. $input_value .'" value="'. $input_value .'" '.$disabled.' '. $checked .' />'; 
											echo '<label for="'.$taxonomy.'-'. $input_value .'">'. $input_label .'</label> ';
											echo '<br />';
										
										}
									
									echo'</td>';
									
									echo '<td style="width:120px;">';
									
									$options=[];
									
									foreach ( $terms as $i => $term ) {
										
										$options[$i] = $this -> get_layer_taxonomy_options( $taxonomy, $term );
										
										if( is_object_in_term( $user_plan_id, $taxonomy, $term->term_id ) ){
											
											$total_fee_amount 	= $this -> sum_custom_taxonomy_total_price_amount( $total_fee_amount, $options[$i], $total_fee_period);
			
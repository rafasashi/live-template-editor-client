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
			
			$post_type	 = get_post_type();
			$post_id	 = get_the_ID();
			$post_author = intval(get_post_field( 'post_author', $post_id ));
			
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
				
				$visibility = get_post_meta( $post_id, 'layerVisibility', true );
				
				if( $visibility == 'anyone' ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				elseif( $visibility == 'registered' && $this->user->loggedin ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				elseif( $this->user_has_layer( $post_id ) === true && $this->user->loggedin ){
					
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
			
			remove_all_filters('content_save_pre');
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
		
		?>
		<!-- Facebook Pixel Code -->
		
		<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '135366043652148'); // Insert your pixel ID here.
		fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none"
		src="https://www.facebook.com/tr?id=135366043652148&ev=PageView&noscript=1"
		/></noscript>
		
		<!-- End Facebook Pixel Code -->
		
		<?php
	
	}	
	
	public function ltple_client_footer(){
		
		?>
		<script> 
		
			<!-- Google Analytics Code -->
		
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $this->settings->options->analyticsId; ?>', 'auto');
			ga('send', 'pageview');
			
			<!-- End Google Analytics Code -->
			
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
											$total_price_amount = $this -> sum_custom_taxonomy_total_price_amount( $total_price_amount, $options[$i], $total_price_period);
											$total_storage 		= $this -> sum_custom_taxonomy_total_storage( $total_storage, $options[$i]);
										}
										
										echo '<span style="display:block;padding:1px 0;margin:0;">';
											
											if($options[$i]['storage_unit']=='templates'&&$options[$i]['storage_amount']==1){
												
												echo '+'.$options[$i]['storage_amount'].' template';
											}
											elseif($options[$i]['storage_amount']>0){
												
												echo '+'.$options[$i]['storage_amount'].' '.$options[$i]['storage_unit'];
											}
											else{
												
												echo $options[$i]['storage_amount'].' '.$options[$i]['storage_unit'];
											}
									
										echo '</span>';	
											
									}

									echo'</td>';
									
									echo '<td>';
									
										foreach ( $terms as $i => $term ) {
									
											echo '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
											
												echo $options[$i]['price_amount'].$options[$i]['price_currency'].' / '.$options[$i]['price_period'];
											
											echo '</span>';
										}
										
									echo'</td>';
									
								}
								else {
									
									echo '<td>';
									
										echo __( 'There are no layer-types available.', 'live-template-editor-client' );
									
									echo'</td>';
								}

							echo'</tr>';
						}
					}
					
					echo '<tr style="font-weight:bold;">';
					
						echo '<th style="font-weight:bold;"><label for="price">'. __( 'TOTALS', 'live-template-editor-client' ) . '</label></th>';

						echo '<td style="width:120px;">';

						echo'</td>';
						
						echo '<td>';
							
							if(isset($total_storage)){
								
								foreach($total_storage as $storage_unit => $total_storage_amount){
									
									echo '<span style="display:block;">';
									
										if($storage_unit=='templates'&&$total_storage_amount==1){
											
											echo '+'.$total_storage_amount.' template';
										}
										elseif($total_storage_amount>0){
											
											echo '+'.$total_storage_amount.' '.$storage_unit;
										}
										else{
											
											echo $total_storage_amount.' '.$storage_unit;
										}
										
									echo '</span>';
								}							
							}
							else{
								
								echo '<span style="display:block;">';
									
									echo '+0 templates';
									
								echo '</span>';
							}
							
						echo'</td>';
						
						echo '<td>';
						
							echo '<span style="font-size:16px;">';
							
								if( $total_fee_amount > 0 ){
									
									echo htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
									echo '<br>+';
								}
								
								echo round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;
							
							echo '</span>';
							
						echo'</td>';
		
					echo'</tr>';
						
				echo'</table>';
				
			echo'</div>';

			//get list of emails sent to user
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Emails sent', 'live-template-editor-client' ) . '</h3>';
				
				$emails = get_user_meta($user->ID, $this->_base . '_email_sent', true);
				
				if( !empty($emails) ){
					
					$emails = json_decode($emails,true);
					
					echo '<ul style="padding-left:10px;">';
					
						foreach($emails as $slug => $time){
							
							echo '<li>';
							
								echo date( 'd/m/y', $time) . ' - ' . ucfirst(str_replace('-',' ',$slug));
							
							echo '</li>';
						}
					
					echo '</ul>';
				}				
				
			echo'</div>';			
		}	
	}
	
	public function get_layer_taxonomy_price_fields($taxonomy_name,$args=[]){
		
		//get periods
		
		$periods = $this -> get_price_periods();
		
		//get price_amount
		
		$price_amount=0;
		if(isset($args['price_amount'])){
			
			$price_amount=$args['price_amount'];
		}

		//get price_period
		
		$price_period='';
		if(isset($args['price_period'])&&is_string($args['price_period'])){
			
			$price_period=$args['price_period'];
		}
		
		//get price_fields
		
		$price_fields='';

		$price_fields.='<div class="input-group">';

			$price_fields.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">$</span>';
			
			$price_fields.='<input type="number" step="0.1" min="-1000" max="1000" placeholder="0" name="'.$taxonomy_name.'-price-amount" id="'.$taxonomy_name.'-price-amount" style="width: 60px;" value="'.$price_amount.'"/>';
			
			$price_fields.='<span> / </span>';
			
			$price_fields.='<select name="'.$taxonomy_name.'-price-period" id="'.$taxonomy_name.'-price-period">';
				
				foreach($periods as $k => $v){
					
					$selected='';
					
					if($k == $price_period){
						
						$selected='selected';
					}
					elseif($price_period=='' && $k=='month'){
						
						$selected='selected';
					}
					
					$price_fields.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$price_fields.='</select>';					
			
		$price_fields.='</div>';
		
		$price_fields.='<p class="description">The '.str_replace(array('-','_'),' ',$taxonomy_name).' price used in table pricing & plans </p>';
		
		return $price_fields;
	}
	
	public function get_layer_taxonomy_storage_fields($taxonomy_name,$args=[]){

		//get storage units
		
		$storage_units = $this -> get_storage_units();	
	
		//get storage_amount
		
		$storage_amount=0;
		if(isset($args['storage_amount'])){
			
			$storage_amount=$args['storage_amount'];
		}

		//get storage_unit
		
		$storage_unit='';
		if(isset($args['storage_unit'])&&is_string($args['storage_unit'])){
			
			$storage_unit=$args['storage_unit'];
		}
	
		$storage_field='';
		
		$storage_field.='<div class="input-group">';

			$storage_field.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">+</span>';
			
			$storage_field.='<input type="number" step="1" min="-10" max="10" placeholder="0" name="'.$taxonomy_name.'-storage-amount" id="'.$taxonomy_name.'-storage-amount" style="width: 50px;" value="'.$storage_amount.'"/>';
			
			$storage_field.='<select name="'.$taxonomy_name.'-storage-unit" id="'.$taxonomy_name.'-storage-unit">';
				
				foreach($storage_units as $k => $v){
					
					$selected='';
					
					if($k == $storage_unit){
						
						$selected='selected';
					}
					elseif($storage_unit=='' && $k=='templates'){
						
						$selected='selected';
					}
					
					$storage_field.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$storage_field.='</select>';	
			
		$storage_field.='</div>';
		
		$storage_field.='<p class="description">The amount of additional user account storage</p>';
		
		return $storage_field;		
	}
	
	public function save_custom_user_taxonomy_fields( $user_id ) {
		
		$taxonomies = $this -> get_user_plan_custom_taxonomies();
		
		$user_has_subscription = 'false';
		
		$all_updated_terms = [];
		
		foreach($taxonomies as $t){
		
			$taxonomy = $t['taxonomy'];
			$taxonomy_name = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$tax = get_taxonomy( $taxonomy );

			/* Make sure the current user can edit the user and assign terms before proceeding. */
			if ( !current_user_can( 'administrator', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
				return false;
			
			if(isset($_POST)){
			
				$terms = [];
			
				if(isset($_POST[$taxonomy]) && is_array($_POST[$taxonomy])){
					
					$terms = $_POST[$taxonomy];
					
					$all_updated_terms[]=$_POST[$taxonomy];

					if(!empty($terms)){
						
						$user_has_subscription = 'true';
					}						
				}
			
				$user_plan_id = $this->get_user_plan_id( $user_id );
			
				wp_set_object_terms( $user_plan_id, $terms, $taxonomy);

				clean_object_term_cache( $user_plan_id, $taxonomy );
			}
		}
		
		update_user_meta( $user_id , 'has_subscription', $user_has_subscription);
		
		//send admin notification
							
		wp_mail($this->settings->options->emailSupport, 'Plan edited from dashboard - user id ' . $user_id . ' - ip ' . $this->request->ip, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($all_updated_terms,true) . PHP_EOL  . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);
	}
	
	public function add_shortcode_subscription_plan( $atts ){
		
		$atts = shortcode_atts( array(
		
			'id'		 	=> NULL,
			'widget' 		=> 'false',
			'title' 		=> NULL,
			'content' 		=> NULL,
			'button' 		=> NULL,
			'show-storage' 	=> true
			
		), $atts, 'subscription-plan' );		
		
		$subscription_plan = '';
		
		if(!is_null($atts['id'])&&is_numeric($atts['id'])){
			
			$id=intval($atts['id']);
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		='month';
			$total_fee_period		='once';
			$total_price_currency	='$';
			
			$option_name='plan_options';
			
			$options = $this -> get_layer_custom_taxonomies_options();
			
			if($data = get_post_meta( $id, $option_name, true )){
				
				//get plan
				
				$plan = get_post($id);
				
				//get plan title
				
				if(is_string($atts['title'])){
					
					$plan_title = $atts['title'];
				}
				else{
					
					$plan_title = $plan->post_title;
				}
				
				//get plan content
				
				if(is_string($atts['content'])){
					
					$plan_form 		= '';
					$plan_content 	= $atts['content'];
					$style='font-weight: bold;color: rgb(138, 206, 236);';
				}
				else{
					
					$plan_form 		= '';
					$plan_content 	= $plan->post_content;
					$style='padding: 30px 30px;font-weight: bold;background: rgba(158, 158, 158, 0.24);color: rgb(138, 206, 236);';
				}

				// get total_price_amount & total_storage
				
				foreach( $options as $taxonomy => $terms ) {
					
					$taxonomy_options = [];
					
					foreach($terms as $i => $term){

						$taxonomy_options[$i] = $this -> get_layer_taxonomy_options( $taxonomy, $term );

						if ( in_array( $term->slug, $data ) ) {						
							
							$total_price_amount = $this -> sum_custom_taxonomy_total_price_amount( $total_price_amount, $taxonomy_options[$i], $total_price_period);	
							$total_fee_amount 	= $this -> sum_custom_taxonomy_total_price_amount( $total_fee_amount, $taxonomy_options[$i], $total_fee_period);				
							$total_storage 		= $this -> sum_custom_taxonomy_total_storage( $total_storage, $taxonomy_options[$i]);

							if( !empty($taxonomy_options[$i]['form']) && ( count($taxonomy_options[$i]['form']['input'])>1 || !empty($taxonomy_options[$i]['form']['name'][0]) ) ){

								if( !empty($_POST['meta_'.$term->slug]) ){
									
									// store data in session
									
									$_SESSION['pm_' . $plan->ID]['meta_'.$term->slug] = $_POST['meta_'.$term->slug];
								}
								else{
									
									$plan_form .= $this->admin->display_field( array(
							
										'type'				=> 'form',
										'id'				=> 'meta_'.$term->slug,
										'name'				=> $term->taxonomy . '-meta',
										'array' 			=> $taxonomy_options[$i],
										'action' 			=> '',
										'method' 			=> 'post',
										'description'		=> ''
										
									), false, false );
								}
							}
						}
					}
				}
				
				// round total_price_amount
				
				$total_fee_amount 	= round($total_fee_amount, 2);
				$total_price_amount = round($total_price_amount, 2);
				
				//get plan_data
				
				sort($data);
				ksort($total_storage);
				
				$plan_data=[];
				$plan_data['id'] 		= $plan->ID;
				$plan_data['name'] 		= $plan->post_title;
				$plan_data['options'] 	= $data;
				$plan_data['price'] 	= $total_price_amount;
				$plan_data['fee'] 		= $total_fee_amount;
				$plan_data['currency']	= $total_price_currency;
				$plan_data['period'] 	= $total_price_period;
				$plan_data['fperiod']	= $total_fee_period;
				$plan_data['storage'] 	= $total_storage;
				$plan_data['subscriber']= $this->user->user_email;
				$plan_data['client']	= $this->client->url;
				$plan_data['meta']		= ( !empty($_SESSION['pm_' . $plan->ID]) ? $_SESSION['pm_' . $plan->ID] : '' );
				
				$plan_data=esc_attr( json_encode( $plan_data ) );
				
				//var_dump($plan_data);exit;

				$plan_key=md5( 'plan' . $plan_data . $this->_time . $this->user->user_email );
				
				$agreement_url = $this->server->url . '/agreement/?pk='.$plan_key.'&pd='.$this->base64_urlencode($plan_data) . '&_=' . $this->_time;
				
				$iframe_height 	= 500;
				
				if( !is_null($atts['widget']) && $atts['widget']==='true' ){

					if( !empty($plan_form) ){
						
						$subscription_plan.= '<div class="row panel-body" style="background:#fff;">';
						
							$subscription_plan.= '<div class="col-xs-12 col-md-6">';

									$subscription_plan.= $plan_form;

							$subscription_plan.= '</div>';
							
						$subscription_plan.= '</div>';						
					}
					else{

						$subscription_plan.= '<iframe src="'.$agreement_url.'" style="width:100%;bottom: 0;border:0;height:' . ($iframe_height - 10 ) . 'px;overflow: hidden;"></iframe>';													
					}
				}
				else{
					
					$subscription_plan.='<h2 id="plan_title" style="'.$style.'">' . $plan_title . '</h2>';
					
					$subscription_plan.=$plan_content;
					
					$subscription_plan.='<div id="plan_form">';
					//$subscription_plan.='<form id="plan_form" action="" method="POST">'.PHP_EOL;

						// Output iframe
						
						if($atts['show-storage']===true){
						
							$subscription_plan.= '<div id="plan_storage" style="display:block;">';				
								
								foreach($total_storage as $storage_unit => $total_storage_amount){
									
									if($total_storage_amount > 0 ){
										
										$subscription_plan.='<span style="display:block;">';
										
											if($storage_unit=='templates' && $total_storage_amount==1 ){
												
												$subscription_plan.= $total_storage_amount.' template';
											}
											else{
												
												$subscription_plan.= $total_storage_amount.' '.$storage_unit;
											}
											
										$subscription_plan.='</span>';
									}
								}

							$subscription_plan.= '</div>';
							
							$subscription_plan.='<hr id="plan_hr" style="display:block;"></hr>';
						}
						
						$subscription_plan.= '<div id="plan_price">';				
							
							if( $total_fee_amount > 0 ){
								
								$subscription_plan.= htmlentities(' ').$total_fee_amount.$total_price_currency.' '. ( $total_fee_period == 'once' ? 'one time fee' : $total_fee_period );
								
								if($total_price_amount > 0 ){
									
									$subscription_plan.= '<br>+';
								}
							}
							
							if($total_price_amount > 0 ){
							
								$subscription_plan.= $total_price_amount.$total_price_currency.' / '.$total_price_period;
							}
							elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
								
								$subscription_plan.= 'Free';
							}
							
						$subscription_plan.= '</div>';
						
						$subscription_plan.= '</br>';
						
						$subscription_plan.= '<div id="plan_button">';				
							
							$subscription_plan.='<span class="payment-errors"></span>'.PHP_EOL;
							
							$modal_id='modal_'.md5($agreement_url);
							
							$subscription_plan.='<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
								
								if(!empty($atts['button'])){
									
									$subscription_plan.= ucfirst($atts['button']).PHP_EOL;
								}
								elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
									
									$subscription_plan.='Start'.PHP_EOL;
								}
								elseif($total_price_amount == 0 && $total_fee_amount > 0 ){
									
									$subscription_plan.='Order'.PHP_EOL;
								}
								else{
									
									$subscription_plan.='Subscribe'.PHP_EOL;
								}

							$subscription_plan.='</button>'.PHP_EOL;

							$subscription_plan.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
								
								$subscription_plan.='<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
									
									$subscription_plan.='<div class="modal-content">'.PHP_EOL;
									
										$subscription_plan.='<div class="modal-header">'.PHP_EOL;
											
											$subscription_plan.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											$subscription_plan.= '<h4 class="modal-title" id="myModalLabel">';
											
												$subscription_plan.= $plan->post_title;
												
												if( $total_price_amount > 0 ){
												
													$subscription_plan.= ' ('.$total_price_amount.$total_price_currency.' / '.$total_price_period.')'.PHP_EOL;
											
												}
											
											$subscription_plan.= '</h4>'.PHP_EOL;
										
										$subscription_plan.='</div>'.PHP_EOL;

											if( $this->user->loggedin ){
												
												//echo '<pre>';
												//var_dump($this->user->has_subscription);exit;
												//var_dump($this->user_has_layer( $plan->ID ));exit;
												
												if( $total_price_amount == 0 && $total_fee_amount == 0 && $this->user_has_plan( $plan->ID ) === true ){
													
													$subscription_plan.='<div class="modal-body">'.PHP_EOL;
												
														$subscription_plan.= '<div class="alert alert-info">';
															
															$subscription_plan.= 'You already have access to this set of features...';
															
															$subscription_plan.= '<div class="pull-right">';

																$subscription_plan.= '<a class="btn-sm btn-success" href="' . $this->urls->editor . '" target="_parent">Start editing</a>';
																
															$subscription_plan.= '</div>';
															
														$subscription_plan.= '</div>';	

													$subscription_plan.='</div>'.PHP_EOL;
												}
												else{
													
													$subscription_plan.= '<div class="loadingIframe" style="height: 50px;width: 100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

													$subscription_plan.= '<iframe data-src="' . get_permalink( $plan->ID ) . '?output=widget'.'" style="width: 100%;position:relative;top:-50px;margin-bottom:-60px;bottom: 0;border:0;height:'.$iframe_height.'px;overflow: hidden;"></iframe>';													
												}
		
											}
											else{
												
												$subscription_plan.='<div class="modal-body">'.PHP_EOL;
												
													$subscription_plan.= '<div style="font-size:20px;padding:20px;" class="alert alert-warning">';
														
														$subscription_plan.= 'You need to log in first...';
														
														$subscription_plan.= '<div class="pull-right">';

															$subscription_plan.= '<a style="margin:0 2px;" class="btn-lg btn-success" href="' . wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) . '">Login</a>';
															
															$subscription_plan.= '<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'&action=register">Register</a>';
														
														$subscription_plan.= '</div>';
														
													$subscription_plan.= '</div>';
												
												$subscription_plan.='</div>'.PHP_EOL;
											}

									$subscription_plan.='</div>'.PHP_EOL;
									
								$subscription_plan.='</div>'.PHP_EOL;
								
							$subscription_plan.='</div>'.PHP_EOL;
							
						$subscription_plan.= '</div>'.PHP_EOL;
						
					//$subscription_plan.='</form>'.PHP_EOL;
					$subscription_plan.='</div>'.PHP_EOL;						
				}
			}
		}		
		
		return $subscription_plan;
	}	
	
	public function ltple_get_dropdown_posts( $args ){
		
		$defaults = array(
		
			'post_type' 		=> 'post', 
			'show_option_none'  => 'Select a post', 
			'name' 				=> null, 
			'selected' 			=> '',
			'style' 			=> '', 
			'echo' 				=> true, 
			'orderby' 			=> 'title', 
			'order' 			=> 'ASC' 
		);

		$args = array_merge($defaults, $args);
		
		$posts = get_posts(
			array(
			
				'post_type'  	=> $args['post_type'],
				'numberposts' 	=> -1,
				'orderby'		=> $args['orderby'], 
				'order' 		=> $args['order']
			)
		);
		 
		$dropdown = '';
		
		if( $posts ){
			
			if( !is_string($args['name']) ){
				
				$args['name'] = $args['post_type'].'_select';
			}
			
			$dropdown .= '<select' . ( !empty($args['style']) ? ' style="' . $args['style'] . '"' : '' ).' id="'.$args['name'].'" name="'.$args['name'].'">';
				
				$dropdown .= '<option value="-1">'.$args['show_option_none'].'</option>';
				
				$args['selected'] = intval($args['selected']);
				
				foreach( $posts as $p ){
					
					$selected = '';
					if( $p->ID == $args['selected'] ){
						
						$selected = ' selected';
					}
					
					$dropdown .= '<option value="' . $p->ID . '"'.$selected.'>' . esc_html( $p->post_title ) . '</option>';
				}
			
			$dropdown .= '</select>';			
		}
		
		if($args['name'] === false){
			
			return $dropdown;
		}
		else{
			
			echo $dropdown;
		}
	}
	
	public function update_user_layer(){	
		
		if( $this->user->loggedin ){

			if( $this->layer->type == 'user-layer' && empty( $this->user->layer ) ){
				
				//--------cannot be found --------
				
				$this->message ='<div class="alert alert-danger">';

					$this->message .= 'This layer cannot be found...';

				$this->message .='</div>';
				
				include( $this->views . $this->_dev .'/message.php' );					
			}
			elseif( $this->layer->type == 'user-layer' && $this->user->layer->post_author != $this->user->ID && !$this->user->is_admin ){
				
				//--------permission denied--------
				
				$this->message ='<div class="alert alert-danger">';

					$this->message .= 'You don\'t have the permission to edit this template...';

				$this->message .='</div>';
				
				include( $this->views . $this->_dev .'/message.php' );					
			}
			elseif( $this->layer->type == 'user-layer' && isset($_GET['postAction'])&& $_GET['postAction']=='delete' ){
					
				//--------delete layer--------
				
				wp_delete_post( $this->user->layer->ID, $bypass_trash = false );
				
				$this->layer->id = -1;
					
				$this->message ='<div class="alert alert-success">';

					$this->message .= 'Template successfully deleted!';

				$this->message .='</div>';
				
				//include( $this->views . $this->_dev .'/message.php' );

				//redirect page
				
				$parsed = parse_url($_SERVER['SCRIPT_URI'] .'?'. $_SERVER['QUERY_STRING']);

				parse_str($parsed['query'], $params);

				unset($params['uri'],$params['postAction']);
				
				$url = $_SERVER['SCRIPT_URI'];
				
				$query = http_build_query($params);
				
				if( !empty($query) ){
					
					$url .= '?'.$query;		
				}

				wp_redirect($url);
				exit;
			}
			elseif( isset($_POST['postContent']) && !empty($this->layer->type) ){
				
				// get post content
				
				$post_content 	= $this->layer->sanitize_content( $_POST['postContent'] );
				$post_css 		= ( !empty($_POST['postCss']) 	? stripcslashes( $_POST['postCss'] ) : '' );
				$post_title 	= ( !empty($_POST['postTitle']) ? wp_strip_all_tags( $_POST['postTitle'] ) : '' );
				$post_name 		= $post_title;			

				if( $_POST['postAction'] == 'update' && $this->user->is_admin ){
					
					//update layer
					
					if( $this->layer->type == 'user-layer' ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
					}
					else{
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
					}
					
					if(!empty($layer)){
					
						$layerId	= intval( $layer->ID );

						if( is_int($layerId) && $layerId !== -1 ){
						
							global $wpdb;
						
							$wpdb->update( $wpdb->posts, array( 'post_content' => $post_content), array( "ID" => $layerId));
						
							update_post_meta($layerId, 'layerCss', $post_css);
						}
					}
				}
				elseif( $_POST['postAction'] == 'duplicate' && $this->user->is_admin ){
					
					//duplicate layer
					
					if( $this->layer->type == 'user-layer' ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
					}
					else{
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
					}
					
					if(!empty($layer)){
					
						$layerId = intval( $layer->ID );

						if( is_int($layerId) && $layerId !== -1 ){
						
							$post_information = array(
								
								'post_author' 	=> $this->user->ID,
								'post_title' 	=> $post_title,
								'post_name' 	=> $post_name,
								'post_content' 	=> $layer->post_content,
								'post_type' 	=> $layer->post_type,
								'post_status' 	=> 'publish'
							);
							
							$post_id = wp_insert_post( $post_information );

							if( is_numeric($post_id) ){
								
								// duplicate all post meta
								
								$layerMeta = get_post_meta($layerId);
						
								foreach($layerMeta as $name => $value){
									
									if( isset($value[0]) ){
										
										update_post_meta( $post_id, $name, $value[0] );
									}
								}
								
								// duplicate all taxonomies
								
								$taxonomies = get_object_taxonomies($layer->post_type);
								
								foreach ($taxonomies as $taxonomy) {
									
									$layerTerms = wp_get_object_terms($layerId, $taxonomy, array('fields' => 'slugs'));
									
									wp_set_object_terms($post_id, $layerTerms, $taxonomy, false);
								}					
								
								//redirect to user layer

								$layer_url = $_SERVER['SCRIPT_URI'] . '?uri=' . $this->layer->type . '/' . get_post_field( 'post_name', $post_id ) . '/';
								
								//var_dump($layer_url);exit;
								
								wp_redirect($layer_url);
								echo 'Redirecting editor...';
								exit;
							}							
						}
					}
				}
				elseif( $_POST['postAction'] == 'save'){				
					
					//save layer
					
					$post_id = '';
					$defaultLayerId = -1;
					
					if( $this->layer->type == 'user-layer' ){
					
						$post_id		= $this->user->layer->ID;
						$post_title		= $this->user->layer->post_title;
						$post_name		= $this->user->layer->post_name;
						$defaultLayerId	= intval(get_post_meta( $post_id, 'defaultLayerId', true));
					}
					else{
						
						$defaultLayer = get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
						
						if( !empty($defaultLayer) ){
						
							if( empty($post_title) ){
							
								$post_title = $defaultLayer->post_title;
							}
							
							if( $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates'] ){
								
								$this->message ='<div class="alert alert-danger">';
								
									if( $this->user->plan['info']['total_storage']['templates'] == 1 ){
										
										$this->message .= 'You can\'t save more than ' . $this->user->plan['info']['total_storage']['templates'] . ' template with the current plan...';
									}
									elseif( $this->user->plan['info']['total_storage']['templates'] == 0 ){
										
										$this->message .= 'You can\'t save templates with the current plan...';
									}
									else{
										
										$this->message .= 'You can\'t save more than ' . $this->user->plan['info']['total_storage']['templates'] . ' templates with the current plan...';
									}

								$this->message .='</div>';
								
								include( $this->views . $this->_dev .'/message.php' );
							}

							$defaultLayerId	= intval( $defaultLayer->ID );
						}
						else{
							
							http_response_code(404);
							
							$this->message ='<div class="alert alert-danger">';
									
								$this->message .= 'This default layer doesn\'t exists...';

							$this->message .='</div>';
							
							include( $this->views . $this->_dev .'/message.php' );							
						}
					}

					if( $post_title!='' && $post_content!='' && is_int($defaultLayerId) && $defaultLayerId !== -1 ){
						
						$post_information = array(
							
							'post_author' 	=> $this->user->ID,
							'ID' 			=> $post_id,
							'post_title' 	=> $post_title,
							'post_name' 	=> $post_name,
							'post_content' 	=> $post_content,
							'post_type' 	=> 'user-layer',
							'post_status' 	=> 'publish'
						);
						
						$post_id = wp_update_post( $post_information );

						if( is_numeric($post_id) ){
							
							update_post_meta($post_id, 'defaultLayerId', $defaultLayerId);
							
							update_post_meta($post_id, 'layerCss', $post_css);
							
							//redirect to user layer
							
							$user_layer_url = $_SERVER['SCRIPT_URI'] . '?uri=' . 'user-layer/' .  get_post_field( 'post_name', $post_id) . '/';
							
							//var_dump($user_layer_url);exit;
							
							wp_redirect($user_layer_url);
							echo 'Redirecting editor...';
							exit;
						}
					}
					else{
						
						http_response_code(404);
						
						$this->message ='<div class="alert alert-danger">';
								
							$this->message .= 'Error saving user layer...';

						$this->message .='</div>';
						
						include( $this->views . $this->_dev .'/message.php' );
					}
				}
				else{
					
					http_response_code(404);
					
					$this->message ='<div class="alert alert-danger">';
							
						$this->message .= 'This action doesn\'t exists...';

					$this->message .='</div>';
					
					include( $this->views . $this->_dev .'/message.php' );					
				}
			}			
		}
	}

	public function update_user_channel(){	
		
		if( $this->user->loggedin ){
			
			$taxonomy = 'marketing-channel';

			if( isset($_POST[$taxonomy]) &&  is_numeric($_POST[$taxonomy]) ){
				
				//-------- save channel --------
				
				$terms = intval($_POST[$taxonomy]);		
				
				$response = wp_set_object_terms( $this->user->ID, $terms, $taxonomy);
				
				clean_object_term_cache( $this->user->ID, $taxonomy );	

				if( empty($response) ){

					echo 'Error saving user channel...';
					exit;
				}				
			}			
		}
	}
	
	public function update_user_image(){	
		
		if( $this->user->loggedin ){

			if( isset($_GET['imgAction']) && $_GET['imgAction']=='delete' ){
				
				//--------delete image--------
				
				wp_delete_post( $this->image->id, $bypass_trash = false );
				
				$this->image->id = -1;
					
				$this->message ='<div class="alert alert-success">';

					$this->message .= 'Image url successfully deleted!';

				$this->message .='</div>';
				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='upload' && isset($_POST['imgHost'])){
				
				// valid host
				
				$app_title = wp_strip_all_tags( $_POST['imgHost'] );
				
				$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
				
				if( empty($app_item) || ( intval( $app_item->post_author ) != $this->user->ID && !in_array_field($app_item->ID, 'ID', $this->apps->mainApps)) ){
					
					echo 'This image host doesn\'t exists...';
					exit;
				}
				elseif(!empty($_FILES)) {
					
					foreach ($_FILES as $file => $array) {
						
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
							
							echo "upload error : " . $_FILES[$file]['error'];
							exit;
						}
						else{
							
							$mime=explode('/',$_FILES[$file]['type']);
							
							if($mime[0] !== 'image') {
								
								echo 'This is not a valid image type...';
								exit;							
							}
							
							if( $data = file_get_contents($_FILES[$file]['tmp_name'])){
								
								// rename file
								
								$_FILES[$file]['name'] = md5($data) . '.' . $mime[1];

								// get current app
								
								$app = explode(' - ', $app_title );
								
								// set session
								
								$_SESSION['app'] 	= $app[0];
								$_SESSION['action'] = 'upload';
								$_SESSION['file'] 	= $_FILES[$file]['name'];
																		
								//check if image exists
								
								$img_exists = false;
								
								$q = new WP_Query(array(
									
									'post_author' => $this->user->ID,
									'post_type' => 'user-image',
									'numberposts' => -1,
								));

								while ( $q->have_posts() ) : $q->the_post(); 
							
									global $post;
									
									if( $post->post_title == $_FILES[$file]['name'] ){
										
										$img_exists = true;
										break;
									}
									
								endwhile; wp_reset_query();
								
								if( !$img_exists ){
									
									//require the needed files
									
									require_once(ABSPATH . "wp-admin" . '/includes/image.php');
									require_once(ABSPATH . "wp-admin" . '/includes/file.php');
									require_once(ABSPATH . "wp-admin" . '/includes/media.php');
									
									//upload image
									
									$attach_id = media_handle_upload( $file, 0 );
									
									if(is_numeric($attach_id)){
									
										// get image url
										
										$image_url = wp_get_attachment_url( $attach_id );
										
										// add local image	
										
										/*
										if($post_id = wp_insert_post( array(
											
											'post_author' 	=> $this->user->ID,
											'post_title' 	=> $_FILES[$file]['name'],
											'post_name' 	=> $_FILES[$file]['name'],
											'post_content' 	=> $image_url,
											'post_type'		=> 'user-image',
											'post_status' 	=> 'publish'
										))){
											
										}
										*/
										
										// upload image to host
										
										$appSlug = $app[0];
										
										if( !isset( $this->apps->{$appSlug} ) ){
											
											$this->apps->includeApp($appSlug);
										}

										if($this->apps->{$appSlug}->appUploadImg( $app_item->ID, $image_url )){
											
											// output success message
											
											$this->message ='<div class="alert alert-success">';
													
												$this->message .= 'Congratulations! Image succefully uploaded to your library.';

											$this->message .='</div>';											
										}
										else{
											
											// output error message
											
											$this->message ='<div class="alert alert-danger">';
													
												$this->message .= 'Oops, something went wrong...';

											$this->message .='</div>';													
										}
										
										// remove image from local library
										
										wp_delete_attachment( $attach_id, $force_delete = true );
									}
									else{
										
										echo 'Error handling upload...';
										exit;											
									}
								}
								else{
									
									// output warning message
									
									$this->message ='<div class="alert alert-warning">';
											
										$this->message .= 'This image already exists...';

									$this->message .='</div>';										
								}
							}
							else{
								
								echo 'Error uploading your image...';
								exit;									
							}
						}
					}   
				}				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='save' && isset($_POST['imgTitle']) && isset($_POST['imgUrl']) ){
				
				//-------- save image --------
				
				$img_id = $img_title = $img_name = $img_content = '';
				
				if($_POST['imgTitle']!=''){

					$img_title = $img_name = wp_strip_all_tags( $_POST['imgTitle'] );
				}
				else{ 
					
					echo 'Empty image title...';
					exit;
				}

				if($_POST['imgUrl']!=''){
				
					$img_content=wp_strip_all_tags( $_POST['imgUrl'] );
				}
				else{
					
					echo 'Empty image url...';
					exit;
				}
				
				if( $img_title!='' && $img_content!=''){
					
					$img_valid = true;
					
					if($img_valid === true){
						
						// check if is valid url
						
						if (filter_var($img_content, FILTER_VALIDATE_URL) === FALSE) {
							
							$img_valid = false;
						}
					}
					
					if($img_valid === true){
						
						// check if image exists
						
						$q = new WP_Query(array(
							
							'post_author' => $this->user->ID,
							'post_type' => 'user-image',
							'numberposts' => -1,
						));
						
						//var_dump($q);exit;
						
						while ( $q->have_posts() ) : $q->the_post(); 
					
							global $post;
							
							if( $post->post_content == $img_content ){
								
								$img_valid = false;
								break;
							}
							
						endwhile; wp_reset_query();	
					}
					
					if( $img_valid === true ){
					
						if($post_id = wp_insert_post( array(
							
							'post_author' 	=> $this->user->ID,
							'post_title' 	=> $img_title,
							'post_name' 	=> $img_name,
							'post_content' 	=> $img_content,
							'post_type'		=> 'user-image',
							'post_status' 	=> 'publish'
						))){
							
							$this->message ='<div class="alert alert-success">';
									
								$this->message .= 'Congratulations! Image url succefully added to your library.';

							$this->message .='</div>';						
						}						
					}
					else{

						$this->message ='<div class="alert alert-danger">';
								
							$this->message .= 'This image url already exists...';

						$this->message .='</div>';
					}
				}
				else{
					
					$this->message ='<div class="alert alert-danger">';
							
						$this->message .= 'Error saving user image...';

					$this->message .='</div>';
				}
			}			
		}
	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LTPLE_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LTPLE_Client_Taxonomy( $this, $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {

		wp_register_style( $this->_token . '-jquery-ui', esc_url( $this->assets_url ) . 'css/jquery-ui.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-jquery-ui' );		
	
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	
		wp_register_style( $this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'css/bootstrap-table.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap-table' );	
		
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_enqueue_script('jquery-ui-dialog');
		
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
		
		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );	

		wp_register_script($this->_token . '-sprintf', esc_url( $this->assets_url ) . 'js/sprintf.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-sprintf' );		
		
		wp_register_script($this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'js/bootstrap-table.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table' );

		wp_register_script($this->_token . '-bootstrap-table-export', esc_url( $this->assets_url ) . 'js/bootstrap-table-export.js', array( 'jquery', $this->_token . 'sprintf' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table-export' );
		
		wp_register_script($this->_token . '-table-export', esc_url( $this->assets_url ) . 'js/tableExport.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-table-export' ); 
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_enqueue_script('jquery-ui-sortable');
		
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );

		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );			
		
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'live-template-editor-client', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'live-template-editor-client';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main LTPLE_Client Instance
	 *
	 * Ensures only one instance of LTPLE_Client is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		
		update_option( $this->_token . '_version', $this->_version );
	} 
	// End _log_version_number ()
}
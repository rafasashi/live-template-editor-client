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
	public $dialog;
	public $canonical_url;
	public $triggers;
	public $inWidget;
	
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
		$this->dialog 	= new stdClass();	
		
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
		$this->vendor  		= trailingslashit( $this->dir ) . 'vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		
		$this->inWidget = ( ( isset($_GET['output']) && $_GET['output'] == 'widget' ) ? true : false );
		
		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		$this->script_suffix = '';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Handle localisation
		
		$this->load_plugin_textdomain();
	
		add_action( 'init', array( $this, 'load_localisation' ), 0 );		

		add_action( 'init', array( $this, 'register_theme' ) );
		
		if(isset($_POST['imgData']) && isset($_POST["submitted"])&& isset($_POST["download_image_nonce_field"]) && $_POST["submitted"]=='true'){
			
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

			// start session
			
			if( !session_id() ) {
				
				session_start();
			}			
			
			$this->client 		= new LTPLE_Client_Client( $this );
			$this->request 		= new LTPLE_Client_Request( $this );
			$this->email 		= new LTPLE_Client_Email( $this );
			$this->session 		= new LTPLE_Client_Session( $this );
			$this->urls 		= new LTPLE_Client_Urls( $this );

			$this->stars 		= new LTPLE_Client_Stars( $this );
			$this->login 		= new LTPLE_Client_Login( $this );
			$this->rights 		= new LTPLE_Client_Rights( $this );
			
			// Load API for generic admin functions
			
			$this->admin 	= new LTPLE_Client_Admin_API( $this );
			$this->cron 	= new LTPLE_Client_Cron( $this );
			
			$this->campaign = new LTPLE_Client_Campaign( $this );
			
			$this->api 		= new LTPLE_Client_Json_API( $this );
			$this->server 	= new LTPLE_Client_Server( $this );
			
			$this->checkout = new LTPLE_Client_Checkout( $this );
			
			$this->dashboard = new LTPLE_Client_Dashboard( $this );
			
			$this->media 	= new LTPLE_Client_Media( $this );
			 
			$this->apps 	= new LTPLE_Client_Apps( $this );

			$this->gallery 	= new LTPLE_Client_Gallery( $this );			
			
			$this->element 	= new LTPLE_Client_Element( $this );
			
			$this->script 	= new LTPLE_Client_Script( $this );
			
			$this->layer 	= new LTPLE_Client_Layer( $this );
			$this->tutorials = new LTPLE_Client_Tutorials( $this );
			
			$this->services = new LTPLE_Client_Services( $this );			
			$this->plan 	= new LTPLE_Client_Plan( $this );
			$this->product 	= new LTPLE_Client_Product( $this );

			$this->image 	= new LTPLE_Client_Image( $this );

			$this->bookmark = new LTPLE_Client_Bookmark( $this );
			
			$this->users 	= new LTPLE_Client_Users( $this );
			$this->programs = new LTPLE_Client_Programs( $this );
			$this->channels = new LTPLE_Client_Channels( $this );
			$this->network 	= new LTPLE_Client_Network( $this );			
			$this->profile 	= new LTPLE_Client_Profile( $this );
			
			if( is_admin() ) {		
				
				add_action( 'init', array( $this, 'init_backend' ));
				
				//add_action( 'edit_form_after_title', array( $this, '' ) );				
			}
			else{
				
				add_action( 'init', array( $this, 'init_frontend' ));
			}
		}

	} // End __construct ()
	
	private function ltple_get_secret_iv(){
		
		//$secret_iv = md5( $this->user_agent . $this->user_ip );
		//$secret_iv = md5( $this->user_ip );
		$secret_iv = md5( 'another-secret' );	

		return $secret_iv;
	}	
	
	public function ltple_encrypt_str($string, $secret_key = ''){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		if( empty($secret_key) ){
		
			$secret_key = md5( $this->client->key );
		}
		
		$secret_iv = $this->ltple_get_secret_iv();
		
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = $this->base64_urlencode($output);

		return $output;
	}
	
	public function ltple_decrypt_str($string, $secret_key = ''){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		if( empty($secret_key) ){
			
			$secret_key = md5( $this->client->key );
		}
		
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
	
	public function init_frontend(){	

		// Load frontend JS & CSS
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		
		add_action( 'login_enqueue_scripts', array( $this, 'get_login_logo' ) );
		add_filter( 'login_headerurl', array( $this, 'get_login_logo_url' ) );
		add_filter( 'login_headertitle', array( $this, 'get_login_logo_url_title' ) );

		// add editor shortcodes
		
		add_shortcode('ltple-client-editor', array( $this , 'get_editor_shortcode' ) );
		
		// add apps shortcodes
		
		add_shortcode('ltple-client-apps', array( $this , 'get_apps_shortcode' ) );		
		
		// add footer
		
		add_action( 'wp_footer', array( $this, 'get_footer') );	
		
		// Custom default layer template
		
		add_filter('template_include', array( $this, 'editor_templates'), 1 );

		add_action('template_redirect', array( $this, 'editor_output' ));				
		
		add_filter( 'pre_get_posts', function($query) {

			if ($query->is_search ) {
				
				//$query->set('post_type',array('post','page'));
			}

			return $query;
		});	
	
		// get current user

		$this->set_current_user();
		
		// loaded hook
		
		do_action( 'ltple_loaded');
		
		
	}
	
	public function set_current_user(){

		if( !empty($_GET['key']) && !empty($_GET['output']) && $_GET['output'] == 'embedded' ){
			
			$this->user = get_user_by( 'email', $this->ltple_decrypt_str($_GET['key']));

			if( !empty($this->user->ID) ){
				
				wp_set_current_user($this->user->ID);
				
				wp_set_auth_cookie($this->user->ID, true);
			}
			else{
				
				echo 'Wrong embedded request...';
				exit;				
			}
		}
		elseif( $this->request->is_remote ){

			$this->user = get_user_by( 'id', $this->ltple_decrypt_str($_SERVER['HTTP_X_FORWARDED_USER']));
		
			if( !empty($this->user->ID) ){
				
				wp_set_current_user($this->user->ID);
			}
			else{
				
				echo 'Wrong remote request...';
				exit;				
			}			
		}
		else{
			
			$this->user = wp_get_current_user();
		}
		
		$this->user->loggedin = is_user_logged_in();		

		if( $this->user->loggedin ){

			// get is admin
			
			$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );
			
			// get is editor
			
			if( $this->user->is_admin ){	
			
				$this->user->is_editor = true;	
			}
			else{
			
				$this->user->is_editor = current_user_can( 'editor', $this->user->ID );			
			}
			
			// get user notification settings
			
			$this->user->notify 		= $this->users->get_user_notification_settings( $this->user->ID );
			$this->user->can_spam 		= $this->user->notify['series'];
			$this->user->can_spam_set 	= ( !empty(get_user_meta($this->user->ID, $this->_base . '_can_spam',true)) ? true : false );
			
			// get user last seen
			
			$this->user->last_seen = intval( get_user_meta( $this->user->ID, $this->_base . '_last_seen',true) );
			
			// get user last user agent
			
			$this->user->last_uagent = get_user_meta( $this->user->ID, $this->_base . '_last_uagent',true);
						
			// get user layers
			
			if( $this->user->layers = get_posts(array(
			
				'author'      => $this->user->ID,
				'post_type'   => 'user-layer',
				'post_status' => 'publish',
				'numberposts' => -1
				
			))){
				
				// get user layer type
				
				foreach( $this->user->layers as $layer ){
					
					$layer->type = $this->layer->get_layer_type($layer);
				}
			}
			
			// get user stars
			
			$this->user->stars = $this->stars->get_count($this->user->ID);
					
			// get user ref id
			
			$this->user->refId = $this->ltple_encrypt_uri( 'RI-' . $this->user->ID );	
			
			// get user referent
			
			$this->user->referredBy = get_user_meta( $this->user->ID, $this->_base . 'referredBy', false );

			// get user rights
			
			//$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );

			//get user layer
			
			if( $this->layer->type != 'cb-default-layer' ){
				
				$this->user->layer = get_post($this->layer->id);
			}
			
			// user programs
			
			$this->user->programs = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-programs',true) );
		
			// get user connected apps
			
			$this->user->apps = $this->apps->getUserApps($this->user->ID);		
			
			// get user marketing channel
			
			$terms = wp_get_object_terms( $this->user->ID, 'marketing-channel' );
			$this->user->channel = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0]->slug : '');

			// get user plan

			$this->user->plan = $this->plan->get_user_plan_info( $this->user->ID );
			
			if( !empty($this->user->plan['holder']) ){
				
				// get user has layer
				
				$this->user->has_layer 	= $this->plan->user_has_layer( $this->layer->id );
				
				// get period end
				
				$this->user->period_end = intval(get_user_meta( $this->user->plan['holder'], $this->_base . 'period_end', true ));
				
				// get remaining days
				
				$this->user->remaining_days = $this->user->period_end > 0 ? ceil( ($this->user->period_end - time()) / (60 * 60 * 24) ) : 0;
			}			
		
			do_action('ltple_user_loaded');
			
			$this->update = new LTPLE_Client_Update( $this );
		}
		else{

			add_action('after_password_reset', array($this,'redirect_password_reset'));
		}
	}	
	
	public function redirect_password_reset($user){

		// set current user
		
		$this->user = wp_set_current_user( $user->ID );
		$this->user->loggedin = true;
		
		// set auth cookie
		
		wp_set_auth_cookie($user->ID, true);
		
		// redirect
		
		if( !empty($_GET['redirect_to']) ){
			
			$url = $_GET['redirect_to'];
		}
		elseif( !empty($_POST['redirect_to']) ){
			
			$url = $_POST['redirect_to'];
		}
		elseif( !empty($_SESSION['redirect_to']) ){
			
			$url = $_SESSION['redirect_to'];
			
			$_SESSION['redirect_to'] = '';
		}			
		else{
			
			$url = $this->urls->profile . $user->ID . '/';
		}
		
        wp_redirect( $url );
        exit;		
	}	
	
	public function init_backend(){	
	
		// Load admin JS & CSS
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		add_action('admin_head', array($this, 'custom_admin_dashboard_css'));
		
		add_filter( 'page_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );
		add_filter( 'post_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );
		
		// add email-campaign
		
		add_filter("email-campaign_custom_fields", array( $this, 'add_campaign_trigger_custom_fields' ));
		
		// add user-image
	
		add_filter('manage_user-image_posts_columns', array( $this, 'set_user_image_columns'));
		add_action('manage_user-image_posts_custom_column', array( $this, 'add_user_image_column_content'), 10, 2);
				
		//get current user
		
		$this->user = wp_get_current_user();
		
		// get is admin
		
		$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );

		// get is editor
			
		if( $this->user->is_admin ){	
			
			$this->user->is_editor = true;	
		}
		else{
			
			$this->user->is_editor = current_user_can( 'editor', $this->user->ID );
		}
		
		if( !$this->user->is_admin || !$this->user->is_editor ){
			
			if(!WP_DEBUG){
			
				$url = $this->urls->profile;
				
				wp_redirect($url);
				exit;
			}
		}
		
		// get user rights
		
		$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );
		
		// set user role
		
		if( $this->user->is_editor && !current_user_can( 'list_users', $this->user->ID ) ){
		
			// get user role

			$edit_editor = get_role('editor'); // Get the user role
			
			// let editor manage users
			
			$edit_editor->add_cap('list_users');
			$edit_editor->add_cap('edit_users');
			//$edit_editor->add_cap('promote_users');
			//$edit_editor->add_cap('create_users');
			//$edit_editor->add_cap('add_users');
			//$edit_editor->add_cap('delete_users');

		}		
				
		// get user stars
		
		$this->user->stars = $this->stars->get_count($this->user->ID);		

		// edit post from admin dashboard
		
		add_action( 'load-post.php', function(){
			
			if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'ltple' ){
				
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
				
				// set current user
				
				$this->set_current_user();				
				
				// output editor
				
				$this->editor_output();
				
				// add editor shortcodes
				
				add_shortcode('ltple-client-editor', array( $this , 'get_editor_shortcode' ) );
				
				// output backend editor
				
				include( $this->views . '/editor-backend.php' );
				
				exit;
			}
		});
		
		// removes admin color scheme options
		
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );		
		
		//Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.

		add_action( 'admin_head', function () {
				
			ob_start( function( $subject ) {
				
				$subject = preg_replace( '#<h[0-9]>' . __("Personal Options") . '</h[0-9]>.+?/table>#s', '', $subject, 1 );
				return $subject;
			});
		});
		
		add_action( 'admin_footer', function(){
			
			ob_end_flush();
		});		
		
		if(strpos($_SERVER['SCRIPT_NAME'],'user-edit.php') > 0 && isset($_REQUEST['user_id']) ){
				
			// get editedUser data
			
			$this->editedUser 			= get_userdata(intval($_REQUEST['user_id']));
			$this->editedUser->rights   = json_decode( get_user_meta( $this->editedUser->ID, $this->_base . 'user-rights',true) );
			$this->editedUser->stars 	= $this->stars->get_count($this->editedUser->ID);
		}
		else{
			
			$this->editedUser = $this->user;
		}
		
		do_action('ltple_user_loaded');

		// loaded hook
		
		do_action( 'ltple_loaded');
	}
	
	public function custom_admin_dashboard_css() {
		
		echo '<style>';
					
			echo '#adminmenu a {color:' . $this->settings->linkColor . ' !important;}';
			echo '#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, .folded #adminmenu li.wp-has-current-submenu { border-left: 5px solid ' . $this->settings->borderColor . '; }';
					
			echo '.displaying-num {  
				background-color: #337ab7;
				display: inline;
				padding: .2em .6em .3em;
				font-size: 90%;
				font-weight: 700;
				line-height: 1;
				color: #fff;
				text-align: center;
				white-space: nowrap;
				vertical-align: baseline;
				border-radius: .25em;
			}';
			
			echo '.pagination-links	{  
				background: #fff;
				display: inline-block;
				padding: 3px;
			}';
			
			echo '.tablenav {  
				min-height: 34px;
			}';	
			
			echo '.tablenav .tablenav-pages {  
				margin: 0;
			}';
			
			echo '.tablenav-pages-navspan {
				height: 100%;
			}';				
			
		echo '</style>';
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

	public function change_subscription_plan_menu_classes($classes, $item){
		
		$post = get_post();
		
		if( get_post_type($post) == 'subscription-plan' ){
			
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
			'payment' 	=> [],
			'streaming' => [],
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
			
			$default = $terms[0]->slug;
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
	
	public function editor_templates( $path ){
		
		if( !empty($_REQUEST['t']) && is_numeric($_REQUEST['t']) ){
			
			$post_id = intval($_REQUEST['t']);
		}
		else{
			
			$post_id = url_to_postid( $this->urls->current );
		}
		
		if( !empty($post_id) ){
			
			$post = get_post($post_id);
			
			if( isset( $_SERVER['HTTP_X_REF_KEY'] ) ){
				
				if( $_SERVER['HTTP_X_REF_KEY'] ){ //TODO improve ref rey validation via header
					
					$path = $this->views . '/layer.php';
				}
				else{
					
					echo 'Malformed layer headers...';
					exit;
				}
			}
			elseif( $post->post_type == 'cb-default-layer' ){
				
				$visibility = get_post_meta( $post->ID, 'layerVisibility', true );
				
				$post->layer_id = $post->ID;
				
				if( $visibility == 'anyone' ){
					
					$path = $this->views . '/layer.php';
				}
				elseif( $visibility == 'registered' && $this->user->loggedin ){
					
					$path = $this->views . '/layer.php';
				}
				elseif( $this->plan->user_has_layer( $post ) === true && $this->user->loggedin ){
					
					$path = $this->views . '/layer.php';
				}
				else{
					
					$path = $this->views . '/preview.php';
				}					
			}
			elseif( $post->post_type == 'user-layer' ){
				
				if(!isset($post->layer_id)){
					
					$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
				}
				
				if( $this->user->loggedin && ( $this->user->is_admin || intval($post->post_author ) == $this->user->ID )){
					
					$path = $this->views . '/layer.php';
				}
				elseif( isset($_REQUEST['t']) && isset($_REQUEST['tk']) ){
					
					if( $_REQUEST['t'] == $this->ltple_decrypt_str($_REQUEST['tk']) ){
					
						$path = $this->views . '/layer.php';
					}
					else{
						
						echo 'Wrong template token...';
						exit;
					}
				}
				else{
					
					echo 'You don\'t have access to this template...';
					exit;
				}				
			}
			elseif( !empty($_REQUEST['t']) ){
				
				$path = $this->views . '/layer.php';
			}
			elseif( $this->layer->is_local_page($post) ){
				
				if(!is_numeric($post->layer_id)){
				
					$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true));
				}
				
				if( $post->layer_id > 0 ){
					
					// custom page
					
					return $path;
				}
			}
			elseif( file_exists($this->views . '/'.$post->post_type . '.php') ){
				
				$path = $this->views .  '/' . $post->post_type . '.php';
			}
		}
		
		return $path;
	}
	
	public function disable_theme() {
		
		return false;
	}
	
	public function editor_output() {
			
		// get layer type
				
		//$terms = wp_get_object_terms( $this->layer->id, 'layer-type' );
		//$this->layer->type = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');

		// get layer range
				
		$terms = wp_get_object_terms( $this->layer->id, 'layer-range' );
		
		$this->layer->range = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');
		
		// get layer price
		
		$this->layer->price = ( !empty($this->layer->range) ? intval( get_option('price_amount_' . $this->layer->range->slug) ) : 0 );
		
		// get triggers
 		
		$this->triggers = new LTPLE_Client_Triggers( $this );
		
		// Custom default layer post
		
		if( $this->layer->defaultId > 0 ){
				
			remove_all_filters('content_save_pre');
			remove_filter( 'the_content', 'wpautop' );
		}	
		
		if( $this->user->loggedin ){
			
			// update user layer
			
			$this->update_user_layer();	
		
			//update user channel
			
			$this->channels->update_user_channel($this->user->ID);			
			
			//update user image
			
			$this->image->update_user_image();
			
			//get user plan
			
			$this->plan->update_user();
		}
		
		// get editor iframe

		if( $this->user->loggedin === true && $this->layer->slug!='' && $this->layer->type!='' && $this->layer->key!='' && $this->server->url!==false ){

			if( $this->layer->key == md5( 'layer' . $this->layer->id . $this->_time )){
				
				if( !empty($_POST['base64']) && !empty($_POST['domId']) ){
					
					// handle cropped image upload
					
					echo $this->image->upload_editor_image($this->layer->id . '_' . $_POST['domId'] . '.png' ,$_POST['base64']);
					
					exit;
				}
				elseif( !empty($_FILES) && !empty($_POST['location']) && $_POST['location'] == 'media' ){
						
					// handle canvas image upload
					
					echo $this->image->upload_collage_image();
					
					exit;
				}
				else{
					
					include( $this->views . '/editor-proxy.php' );
				}
			}
			else{
				
				echo 'Malformed iframe request...';
				exit;					
			}
		}
		
		// Custom outputs
		
		if( strpos($this->urls->current,$this->urls->admin) === 0 ){
			
			include( $this->views . '/admin.php' );
		}
		elseif( isset( $_GET['output']) && $_GET['output'] == 'widget' ){
			
			include( $this->views . '/widget.php' );
		}
		elseif( isset( $_GET['output']) && $_GET['output'] == 'embedded' ){		
			
			include( $this->views . '/editor-embedded.php' );
		}
		elseif( isset($_GET['api']) ){

			include($this->views . '/api.php');
		}
	}

	public function get_header(){
		
		if( $this->profile->id > 0 ){
		
			$post = $this->profile->get_profile_post();
		}
		else{
			
			$post = get_post();
		}
		
		if( !empty($post) ){
		
			$service_name = get_bloginfo( 'name' );
		
			// output default meta tags
			
			$title = apply_filters('ltple_header_title',ucfirst($post->post_title));
		
			echo '<title>' . $title .  ' | ' . $service_name . '</title>'.PHP_EOL;
			echo '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
			echo '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
			echo '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;
			
			$author_name = get_the_author_meta('display_name', $post->post_author );
			$author_mail = get_the_author_meta('user_email', $post->post_author );
			
			echo '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			echo '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			echo '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
			echo '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;
			
			$locale = get_locale();
			
			echo '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
			
			$robots = 'index,follow';
			
			echo '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
			
			$revised = $post->post_date;
			
			echo '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
			
			//get description
			
			if( !empty($post->post_excerpt) ){
				
				$content = ucfirst($post->post_excerpt);
			}
			elseif( !empty($post->post_content) ){
				
				$content = ucfirst($post->post_content);
			}
			else{
				
				$content = ucfirst($post->post_title);
			}
			
			//normalize description
			
			$content = strip_tags(strip_shortcodes($content));
			$content = preg_replace( '/\r|\n/', '', $content);
			$content = preg_replace('/\s+/', ' ',$content);
			
			//shorten description
			
			$length = 35;
			
			$words = explode(' ', $content, $length + 1);

			if(count($words) > $length) :
			
				array_pop($words);
				array_push($words, '…');
				
				$content = implode(' ', $words);
				
			endif;
			
			echo '<meta name="description" content="'.$content.'" />'.PHP_EOL;
			echo '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
			echo '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
			echo '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
			echo '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
			
			echo '<meta name="classification" content="Business" />' . PHP_EOL;
			//echo '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
			
			echo '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
			echo '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
			
			$this->canonical_url = get_permalink( $post->ID );
			
			do_action('ltple_header_canonical_url');
			
			echo '<meta name="url" content="'.$this->canonical_url.'" />' . PHP_EOL;
			echo '<meta name="canonical" content="'.$this->canonical_url.'" />' . PHP_EOL;
			echo '<meta name="original-source" content="'.$this->canonical_url.'" />' . PHP_EOL;
			echo '<link rel="original-source" href="'.$this->canonical_url.'" />' . PHP_EOL;
			echo '<meta property="og:url" content="'.$this->canonical_url.'" />' . PHP_EOL;
			echo '<meta name="twitter:url" content="'.$this->canonical_url.'" />' . PHP_EOL;
			
			echo '<meta name="rating" content="General" />' . PHP_EOL;
			echo '<meta name="directory" content="submission" />' . PHP_EOL;
			echo '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
			echo '<meta name="distribution" content="Global" />' . PHP_EOL;
			echo '<meta name="target" content="all" />' . PHP_EOL;
			echo '<meta name="medium" content="blog" />' . PHP_EOL;
			echo '<meta property="og:type" content="article" />' . PHP_EOL;
			echo '<meta name="twitter:card" content="summary" />' . PHP_EOL;
			
		}

		?>
		<!-- Facebook Pixel Code -->
		<!--
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
		-->
		<!-- End Facebook Pixel Code -->
		
		<?php
	}
	
	public function get_menu( $items, $args ){
		
		if($args->menu_id == 'main-menu'){

			$home  = '<div id="header_logo">';
			
				$home .= '<a href="' . ( $this->user->loggedin ? $this->urls->dashboard : $this->urls->home )  . '">';
					
					$home .= '<img src="' . ( !empty($this->settings->options->logo_url) ? $this->settings->options->logo_url : $this->assets_url . 'images/home.png' ) . '">';

				$home .= '</a>';
				
			$home .= '</div>';
					
			$items = $home . $items;
		}
		
		return $items;
	}
	
	public function get_footer(){

		// collect information
		
		if( $this->user->loggedin && !is_admin() && !$this->inWidget ){	
			
			// credit account credits from server
			
			$credits_url = $this->server->url . '/agreement/?overview=' . $this->ltple_encrypt_uri($this->user->user_email) . '&credit';
			
			echo'<script>' . PHP_EOL;	
			
				echo';(function($){' . PHP_EOL;	

					echo'$(document).ready(function(){' . PHP_EOL;	
						
						echo'if( $("#accountCreditsValue").length ){' . PHP_EOL;	
							
							echo'function refresh_account_credits(){' . PHP_EOL;
							
								echo'$.get({
									
									url: "'.$credits_url .'", 
									cache: false
									
								},function( data ) {' . PHP_EOL;
									
									echo'$( "#accountCreditsValue" ).html( data );' . PHP_EOL;
								
								echo'});' . PHP_EOL;

							echo'}' . PHP_EOL;						
							
							echo'refresh_account_credits();' . PHP_EOL;
							
							/*
							echo'setInterval(function(){' . PHP_EOL;
							
								echo'refresh_account_credits();' . PHP_EOL;

							echo'}, 60000);' . PHP_EOL;  // every minute
							*/
							
						echo'}' . PHP_EOL;
						
					echo'});' . PHP_EOL;			

				echo'})(jQuery);' . PHP_EOL;			
			
			echo'</script>';			
			
			// collect usr information

			if( empty( $this->user->can_spam_set ) && !isset($_POST['can_spam']) ){
				
				include($this->views . '/modals/newsletter.php');
			} 
			
			/*
			if( empty( $this->user->channel ) && !isset($_POST['marketing-channel']) ){
				
				include($this->views . '/modals/channel.php');
			}
			*/
			
			do_action('ltple_collect_user_information');
		}
		
		do_action('ltple_footer');
	}
	
	public function get_apps_shortcode(){

		include($this->views . '/navbar.php');
		
		if($this->user->loggedin){

			include($this->views . '/apps.php');
		}
		else{
			
			echo $this->login->get_form();
		}
	}
	
	public function get_editor_shortcode(){
		
		include($this->views . '/navbar.php');
		
		if($this->user->loggedin){
			
			if( isset($_GET['rewards']) ){

				include($this->views . '/rewards.php');
			}
			elseif( $this->layer->id > 0 ){
				
				if( $this->user->has_layer ){
					
					if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ){
						
						include( $this->views . '/edit.php' );
					}
					else{
					
						include( $this->views . '/editor.php' );
					}
				}
				else{
					
					//include($this->views . '/upgrade.php');
					
					include($this->views . '/restrected.php');
					
					include($this->views . '/gallery.php');
				}
			}
			elseif( !empty($_REQUEST['list']) ){
				
				include( $this->views . '/list.php' );
			}
			else{
				
				include($this->views . '/gallery.php');
			}
		}
		else{
			
			include($this->views . '/gallery.php'); 
		}
	}

	public function get_demo_message(){
		
		echo'<div class="row" style="background-color: #65c5e8;font-size: 18px;color: #fff;padding: 20px;">';
			
			echo'<div class="col-xs-1 text-right">';
			
				echo'<span style="font-size:40px;" class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
			
			echo'</div>';
			
			echo'<div class="col-xs-9">';

				echo'You are using a Demo version of ' . strtoupper(get_bloginfo( 'name' )) . '. Many features are missing such as: </br>';
				echo'Save & Load templates, Generate Meme images, Insert images from the Media Library, Copy CSS...';
			
			echo'</div>';
			
			echo'<div class="col-xs-2 text-right">';
				
				if( $this->user->plan['holder'] == $this->user->ID ){
				
					echo'<a class="btn btn-success btn-lg" href="' . $this->urls->plans . '"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Upgrade now</a>';
				}
				
			echo'</div>';
			
		echo'</div>';		
	}

	public function get_dropdown_posts( $args ){
		
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
				
				foreach( $posts as $post ){
					
					$selected = '';
					if( $post->ID == $args['selected'] ){
						
						$selected = ' selected';
					}
					
					$dropdown .= '<option value="' . $post->ID . '"'.$selected.'>' . esc_html( $post->post_title ) . '</option>';
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
	
	public function get_dropdown_terms( $args ){
		
		$defaults = array(
		
			'taxonomy' 			=> 'category', 
			'show_option_none'  => 'Select a post', 
			'name' 				=> null, 
			'selected' 			=> '',
			'style' 			=> '', 
			'echo' 				=> true, 
			'orderby' 			=> 'title', 
			'order' 			=> 'ASC' 
		);

		$args = array_merge($defaults, $args);
		
		$terms = get_terms(
			array(
			
				'taxonomy'  	=> $args['taxonomy'],
				'numberposts' 	=> -1,
				'orderby'		=> $args['orderby'], 
				'order' 		=> $args['order']
			)
		);
		 
		$dropdown = '';
		
		if( $terms ){
			
			if( !is_string($args['name']) ){
				
				$args['name'] = $args['taxonomy'].'_select';
			}
			
			$dropdown .= '<select' . ( !empty($args['style']) ? ' style="' . $args['style'] . '"' : '' ).' id="'.$args['name'].'" name="'.$args['name'].'">';
				
				$dropdown .= '<option value="-1">'.$args['show_option_none'].'</option>';
				
				$args['selected'] = intval($args['selected']);
				
				foreach( $terms as $term ){
					
					$selected = '';
					if( $term->term_id == $args['selected'] ){
						
						$selected = ' selected';
					}
					
					$dropdown .= '<option value="' . $term->term_id . '"'.$selected.'>' . esc_html( $term->name ) . '</option>';
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
		 
		if( $this->user->loggedin && !empty($this->layer->id) && $this->layer->id > 0 ){
			
			if( $this->layer->type == $this->layer->layerStorage && $this->user->layer->post_author != $this->user->ID && !$this->user->is_admin ){
				
				//--------permission denied--------
				
				$this->message ='<div class="alert alert-danger">';

					$this->message .= 'You don\'t have the permission to edit this template...';

				$this->message .='</div>';
				
				include( $this->views . '/message.php' );					
			}
			elseif( $this->layer->type == $this->layer->layerStorage && isset($_POST['postAction'])&& $_POST['postAction']=='edit' ){
						
				if( !empty($_POST['id']) ){
					
					// edit post
					
					$post_id = intval($_POST['id']);
					
					if( $post = get_post($post_id) ){
						
						if( $this->user->is_admin || intval($post->post_author) == $this->user->ID ){
							
							if( !empty($_POST['image_url']) ){
								
								//upload image
								
								$this->image->upload_post_image($_POST['image_url'],$post_id,'seller');
							}
							
							//update main data
							
							$args = array();
							
							if( !empty($_POST['post_title']) ){
								
								$args['post_title'] = $_POST['post_title'];
							}
							
							if( !empty($_POST['post_status']) ){
								
								$args['post_status'] = $_POST['post_status'];
							}
							
							if( !empty($args) ){
								
								$time = current_time('mysql');
								
								$args['ID'] 			= $post_id;
								$args['post_date'] 		= $time;
								$args['post_date_gmt'] 	= get_gmt_from_date( $time );
		
								wp_update_post($args);
							}
							
							if( $post->post_type == 'cb-default-layer' ){
							
								$fields = $this->layer->get_default_layer_fields($post);
							}
							else{
								
								$fields = $this->layer->get_user_layer_fields($post);
							}
							
							if( !empty($fields) ){
								
								foreach( $fields as $field ){
								
									if( !empty($field['metabox']['taxonomy']) ){
										
										//update terms
										
										$taxonomy = $field['metabox']['taxonomy'];
										
										if( isset($_POST['tax_input'][$taxonomy]) ){
											
											$terms = array();
											
											if( is_string($_POST['tax_input'][$taxonomy]) ){
												
												$terms = array($_POST['tax_input'][$taxonomy]);
											}
											elseif( is_array($_POST['tax_input'][$taxonomy]) ){
												
												$terms = $_POST['tax_input'][$taxonomy];
											}
											
											wp_set_post_terms( $post_id, $terms, $taxonomy, false );
										}
									}
									elseif( isset($_POST[$field['id']]) ){
											
										//update meta
											
										update_post_meta($post_id,$field['id'],$_POST[$field['id']]);
									}
								}
							}
							
							do_action('ltple_edit_layer');
						}
					}
					
					$redirect_url = add_query_arg( array( 
					
						'edited' => '',
					
					), $this->urls->current);
					
					wp_redirect($redirect_url);
					exit;
				}
			}
			elseif( $this->layer->type == $this->layer->layerStorage && isset($_GET['postAction'])&& $_GET['postAction']=='delete' ){
				
				// get local images
			
				$image_dir = $this->image->dir . $this->user->ID . '/';
				$image_url = $this->image->url . $this->user->ID . '/';	
			
				$images = glob( $image_dir . $this->user->layer->ID . '_*.png');				
				
				if( !isset($_GET['confirmed']) ){
				
					// confirm deletion

					$_SESSION['message'] = '<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
						
						$_SESSION['message'] .= '<h2>Are you sure you want to delete this template?</h2>';
					
						if( !empty($images) ){
							
							$_SESSION['message'] .= '<hr></hr>';

							$_SESSION['message'] .= '<div style="margin-top:20px;" class="alert alert-warning">The following images will be removed</div>';
							
							$_SESSION['message'] .= '<div style="margin-top:20px;">';

								foreach ($images as $image) {
									
									$_SESSION['message'] .= '<div class="row">';
									
					
										$_SESSION['message'] .='<div class="col-xs-3 col-sm-3 col-lg-2">';

											$_SESSION['message'] .='<img class="lazy" data-original="' . $image_url . basename($image) .'" />';
												
										$_SESSION['message'] .='</div>';

										$_SESSION['message'] .='<div class="col-xs-9 col-sm-9 col-lg-10">';

											$_SESSION['message'] .='<b style="overflow:hidden;width:90%;display:block;">' . basename($image) . '</b>';
											$_SESSION['message'] .='<br>';
											$_SESSION['message'] .='<input style="width:100%;padding: 2px;" type="text" value="'. $image_url . basename($image) .'" />';

										$_SESSION['message'] .='</div>';										
									
									$_SESSION['message'] .= '</div>';
								}
								
							$_SESSION['message'] .= '</div>';
						}
							
						$_SESSION['message'] .= '<hr></hr>';	
							
						$_SESSION['message'] .= '<div style="margin-top:10px;text-align:right;">';						
							
							$_SESSION['message'] .= '<a style="margin:10px;" class="btn btn-lg btn-success" href="' . $this->urls->current . '&confirmed">Yes</a>';
							
							$_SESSION['message'] .= '<a style="margin:10px;" class="btn btn-lg btn-danger" href="' . $this->urls->editor . '?uri=' . $this->user->layer->ID . '">No</a>';

						$_SESSION['message'] .= '</div>';
					
					$_SESSION['message'] .= '</div>';
				}
				else{
					
					// get layer type
					
					$layer_type = $this->layer->get_layer_type($this->user->layer);
					
					//delete images
					
					foreach ($images as $image) {
						
						unlink($image);
					}
					
					// delete static files
					
					$this->layer->delete_static_contents( $this->user->layer->ID );
				
					// move layer to trash
					
					//wp_trash_post( $this->user->layer->ID );
					
					// delete layer
					
					wp_delete_post( $this->user->layer->ID, false );
					
					// output message
					
					$this->layer->id = -1;
						
					$_SESSION['message'] ='<div class="alert alert-success">';

						$_SESSION['message'] .= 'Template successfully deleted!';

					$_SESSION['message'] .='</div>';
					
					//include( $this->views . '/message.php' );

					//redirect page
					
					$parsed = parse_url($this->urls->editor .'?'. $_SERVER['QUERY_STRING']);

					parse_str($parsed['query'], $params);

					unset($params['uri'],$params['postAction']);
					
					if( !empty($layer_type->storage) ){
						
						$params['list'] = $layer_type->storage;
					}
					
					$url = $this->urls->editor;
					
					$query = http_build_query($params);
					
					if( !empty($query) ){
						
						$url .= '?' . $query;		
					}

					wp_redirect($url);
					exit;
				}
			}
			elseif( isset($_POST['postAction']) && $_POST['postAction'] == 'download' ){
				
				$this->layer->download_static_contents($this->layer->id);
			}
			elseif( isset($_POST['postContent']) && !empty($this->layer->type) ){
				
				// get post content
				
				$is_static 		= ( ( $this->layer->layerOutput == 'hosted-page' || $this->layer->layerOutput == 'downloadable' ) ? true : false );

				$post_content 	= base64_decode($_POST['postContent']);
				
				$post_content 	= urldecode($post_content);
				
				$post_content 	= $this->layer->sanitize_content( $post_content, $is_static );
				
				$post_css 		= ( !empty($_POST['postCss']) 		? stripcslashes( $_POST['postCss'] ) 		 : '' );
				$post_js 		= ( !empty($_POST['postJs']) 		? stripcslashes( $_POST['postJs'] ) 		 : '' );
				$post_title 	= ( !empty($_POST['postTitle']) 	? wp_strip_all_tags( $_POST['postTitle'] ) 	 : '' );
				$post_embedded 	= ( !empty($_POST['postEmbedded']) 	? sanitize_text_field($_POST['postEmbedded']): '' );
				$post_settings 	= ( !empty($_POST['postSettings']) 	? json_decode(stripcslashes($_POST['postSettings']),true): '' );
				
				$post_name 	= $post_title;			
				
				if( $_POST['postAction'] == 'update' ){
					
					//update layer
					
					if( $this->user->is_editor ){
					
						if( $this->layer->type == $this->layer->layerStorage ){
							
							$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
						}
						else{
							
							$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
						}
						
						if(!empty($layer)){
						
							$layerId	= intval( $layer->ID );

							if( is_int($layerId) && $layerId !== -1 ){
							
								global $wpdb;
							
								//$wpdb->update( $wpdb->posts, array( 'post_content' => $post_content), array( "ID" => $layerId));
							
								update_post_meta($layerId, 'layerContent', $post_content);
							
								update_post_meta($layerId, 'layerCss', $post_css);
								
								update_post_meta($layerId, 'layerJs', $post_js);
								
								echo 'Template successfully updated!';
								exit;
							}
						}
						else{
						
							http_response_code(404);
							
							echo 'Error getting default layer ID...';
							exit;
						}
					}
					else{
						
						http_response_code(404);
						
						echo 'Update permission denided...';
						exit;
					}
				}
				elseif( $_POST['postAction'] == 'duplicate' ){
					
					//duplicate layer

					$layer = '';
					
					if( $this->layer->type == $this->layer->layerStorage ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
					}
					elseif( $this->user->is_admin ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
					}

					if( !empty($layer) ){
					
						$layerId = intval( $layer->ID );

						if( is_int($layerId) && $layerId !== -1 ){
						
							$post_id = wp_insert_post( array(
								
								'post_author' 	=> $this->user->ID,
								'post_title' 	=> $post_title,
								'post_name' 	=> $post_name,
								'post_type' 	=> $layer->post_type,
								'post_status' 	=> 'publish'
							) );

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

								$layer_url = $this->urls->editor . '?uri=' . $post_id . '&edit';
								
								//var_dump( $layer_url );exit;
								
								wp_redirect($layer_url);
								echo 'Redirecting editor...';
								exit;
							}							
						}
					}
				}
				elseif( $_POST['postAction'] == 'import' ){
					
					if( $this->user->is_admin ){
												
						// import layer
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
						
						if( !empty($layer) ){
						
							$layerId = intval( $layer->ID );

							if( is_int($layerId) && $layerId !== -1 ){
							
								$post_id = wp_insert_post(array(
									
									'post_author' 	=> $this->user->ID,
									'post_title' 	=> $post_title,
									'post_name' 	=> $post_name,
									'post_type' 	=> $layer->post_type,
									'post_status' 	=> 'publish'
								));

								if( is_numeric($post_id) ){							
									
									// duplicate all taxonomies
									
									$taxonomies = get_object_taxonomies($layer->post_type);
									
									foreach ($taxonomies as $taxonomy) {
										
										$layerTerms = wp_get_object_terms($layerId, $taxonomy, array('fields' => 'slugs'));
										
										wp_set_object_terms($post_id, $layerTerms, $taxonomy, false);
									}
									
									update_post_meta( $post_id, 'layerMargin', '0px' );

									update_post_meta($post_id, 'layerContent', $post_content);
									
									update_post_meta($post_id, 'layerCss', $post_css);
									
									update_post_meta($post_id, 'layerJs', $post_js);									
									
									if(!empty($_POST['postSources'])){
										
										$postSources = $_POST['postSources'];
										
										$upload_dir = wp_upload_dir();
										
										$valid_hosts = array(
										
											'fonts.googleapis.com',
										);										
										
										foreach($postSources as $tagname => $sources){
											
											foreach($sources as $i => $source){
												
												// search source
												
												if( !in_array(parse_url($source,PHP_URL_HOST),$valid_hosts) ){

													$source_name = strtolower(basename($source));
													
													$source_name = preg_replace('/[^\da-z]/i', '-', $source_name);
													
													if( !empty($source_name) ){
														
														$source_name = $source_name . ( $tagname == 'link' ? '.css' : '.js');
							
														if( file_exists( $upload_dir['path'] . '/' . $source_name ) ){
															
															unlink( $upload_dir['path'] . '/' . $source_name );
														}
							
														if( file_exists( $upload_dir['path'] . '/' . $source_name ) ){
															
															// upload file
															
															$postSources[$tagname][$i] = $upload_dir['url'] . '/' . $source_name;	
														}
														else{
							
															// get file contents
															
															$ch = curl_init($source);
															curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
															curl_setopt($ch, CURLOPT_TIMEOUT,20);
															curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
															curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
															$file = curl_exec($ch);
															$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
															curl_close($ch);
															
															//if( $file = file_get_contents($source) ){
															if( $httpcode >= 300 ){
																
																// remove comments in file
									
																$regex = array(
																"`^([\t\s]+)`ism"=>'',
																"`^\/\*(.+?)\*\/`ism"=>"",
																"`([\n\A;]+)\/\*(.+?)\*\/`ism"=>"$1",
																"`([\n\A;\s]+)//(.+?)[\n\r]`ism"=>"$1\n",
																"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism"=>"\n"
																);
																
																$file = preg_replace(array_keys($regex),$regex,$file);		
																
																$css_urls = $this->extract_css_urls($file);
																
																if( !empty($css_urls) ){
																	
																	foreach($css_urls as $type => $urls){
																		
																		if( !empty($urls) ){
																			
																			foreach($urls as $url){
																				
																				$abs_url = $this->get_absolute_url( $url, $source );
																				
																				$filename = strtolower(basename($abs_url));
																				
																				if( !empty($filename) ){
																					
																					$filename = md5($source) . '_' . $filename;
																					
																					if( !file_exists( $upload_dir['path'] . '/' . $filename ) ){
																						
																						$ch = curl_init($abs_url);
																						curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
																						curl_setopt($ch, CURLOPT_TIMEOUT,20);
																						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
																						curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
																						$content = curl_exec($ch);
																						$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
																						curl_close($ch);
																						
																						if( $httpcode >= 300 ){																						
																							
																							$upload  = wp_upload_bits( $filename, null, $content );
																							
																							if( !empty($upload['url']) ){
																								
																								$abs_url = $upload['url'];
																							}
																							else{
																								
																								//var_dump($content);
																							}
																						}
																					}
																					else{
																						
																						$abs_url = $upload_dir['url'] . '/' . $filename;
																					}
														
																					$file = str_replace( $url, $abs_url, $file );
																				}
																			}
																		}
																	}
																}
																
																// upload file
																
																$upload = wp_upload_bits($source_name, null, $file);
																
																// set new url
									
																$postSources[$tagname][$i] = $upload['url'];
															}
														}
													}
													else{
														
														unset($postSources[$tagname][$i]);
													}
												}
											}
										}
										
										// update meta

										update_post_meta($post_id, 'layerMeta', json_encode($postSources,JSON_PRETTY_PRINT));
									}									

									// get layer url
										
									$layer_url = $this->urls->editor . '?uri=' . $post_id;
									
									//redirect to user layer

									wp_redirect($layer_url);
									echo 'Redirecting editor...';
									exit;
								}							
							}
						}
					}
					else{
						
						http_response_code(404);
						
						$this->message ='<div class="alert alert-danger">';
								
							$this->message .= 'You don\'t have enough right to perform this action...';

						$this->message .='</div>';
						
						include( $this->views . '/message.php' );							
					}
				}
				elseif( $_POST['postAction'] == 'save' ){				

					//save layer
					
					$post_id = '';
					$defaultLayerId = -1;
					
					if( $this->layer->type != 'cb-default-layer' ){
						
						$post_id		= $this->user->layer->ID;
						$post_author	= $this->user->layer->post_author;
						$post_title		= $this->user->layer->post_title;
						$post_name		= $this->user->layer->post_name;
						$post_status 	= $this->user->layer->post_status;
						$post_type		= $this->layer->type; // user-layer, post, page...
						
						$defaultLayerId	= intval(get_post_meta( $post_id, 'defaultLayerId', true));
					}
					else{
						
						$post_type		= $this->layer->layerStorage;
						
						if( $post_type == 'user-menu' ){
							
							$post_status = 'publish';
						}
						else{
							
							$post_status = ( $this->layer->layerOutput == 'hosted-page' ? 'draft' : 'publish' );
						}
						
						$defaultLayer 	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
						
						if( !empty($defaultLayer) ){
						
							if( empty($post_title) ){
							
								$post_title = $defaultLayer->post_title;
							}
							
							if( empty($post_content) ){
							
								$post_content = $defaultLayer->post_content;
							}
							
							$post_author = $this->user->ID;
							
							if( !$this->plan->remaining_storage_amount($defaultLayer) > 0 ){
								
								$layer_type = $this->layer->get_layer_type($defaultLayer);

								$this->message ='<div class="alert alert-danger">';
								
									$this->message .= 'You can\'t save more <b>' . $layer_type->name . '</b> projects with the current plan...';

								$this->message .='</div>';
								
								include( $this->views . '/message.php' );
							}

							$defaultLayerId	= intval( $defaultLayer->ID );
						}
						else{
							
							http_response_code(404);
							
							echo 'This default layer doesn\'t exists...';
							exit;							
						}
					}
					
					if( $post_title!='' && is_int($defaultLayerId) && $defaultLayerId > 0 ){
						
						$time = current_time('mysql');
						
						$post_id = wp_update_post(array(
							
							'ID' 			=> $post_id,
							'post_author' 	=> $post_author,
							'post_title' 	=> $post_title,
							'post_name' 	=> $post_name,
							'post_type' 	=> $post_type,
							'post_status' 	=> $post_status,
							'post_date' 	=> $time,
							'post_date_gmt' => get_gmt_from_date($time),
						));
						
						if( is_numeric($post_id) ){
							
							update_post_meta($post_id, 'defaultLayerId', $defaultLayerId);
							
							update_post_meta($post_id, 'layerContent', $post_content);
							
							update_post_meta($post_id, 'layerCss', $post_css);
							
							update_post_meta($post_id, 'layerJs', $post_js);
							
							update_post_meta($post_id, 'layerEmbedded', $post_embedded);
							
							update_post_meta($post_id, 'layerSettings', $post_settings);
							
							if( $this->layer->type == 'cb-default-layer' ){
								
								// update layer type
								
								$terms = wp_get_post_terms($defaultLayerId,'layer-type');
								
								if( !empty($terms[0]) ){

									wp_set_object_terms( $post_id, $terms[0]->term_id, 'layer-type', false ); 
								}
								
								// copy static contents
								
								$this->layer->copy_static_contents($defaultLayerId,$post_id);
							
								//redirect to user layer

								if( !empty($post_embedded) ){
									
									$user_layer_url = $this->layer->embedded['scheme'].'://'.$this->layer->embedded['host'].$this->layer->embedded['path'].'wp-admin/post.php?post='.$this->layer->embedded['t'].'&action=edit&ult='.urlencode($post_title).'&uli='.$post_id.'&ulk='.md5('userLayerId'.$post_id.$post_title);
								}
								elseif( $this->layer->layerOutput == 'hosted-page' ){
									
									$user_layer_url = $this->urls->editor . '?action=edit&uri=' . $post_id;
								}
								else{
									
									$user_layer_url = $this->urls->editor . '?uri=' . $post_id;
								}
								
								wp_redirect($user_layer_url);
								
								echo 'Redirecting editor...';
								exit;							
							}
							else{

								echo 'Template successfully saved!';
								exit;
							}
						}
					}				
					else{
					
						http_response_code(404);
						
						echo 'Error saving user layer...';
						exit;
					}
				}
				else{
					
					http_response_code(404);
					
					$this->message ='<div class="alert alert-danger">';
							
						$this->message .= 'This action doesn\'t exists...';

					$this->message .='</div>';
					
					include( $this->views . '/message.php' );					
				}
			}
			elseif( $_SERVER['REQUEST_METHOD'] === 'POST' ){
				
				if( !empty($this->layer->layerOutput) && $this->layer->layerOutput == 'image' ){
				
					if( $this->layer->upload_image_template() ){
						
						echo 'Template successfully saved!';
						exit;					
					}
					else{
						
						echo 'Error Saving Template....';
						exit;					
					}
				}
			}	
		}
	}
	
	public function extract_css_urls( $text ){
		
		$urls = array( );
	 
		$url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
		$urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
		$pattern         = '/(' .
			 '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
			'|(@import\s*'      . $urlfunc_pattern . ')'      .
			'|('                . $urlfunc_pattern . ')'      .  ')/iu';
		if ( !preg_match_all( $pattern, $text, $matches ) )
			return $urls;
	 
		// @import '...'
		// @import "..."
		foreach ( $matches[3] as $match )
			if ( !empty($match) )
				$urls['import'][] = 
					preg_replace( '/\\\\(.)/u', '\\1', $match );
	 
		// @import url(...)
		// @import url('...')
		// @import url("...")
		foreach ( $matches[7] as $match )
			if ( !empty($match) )
				$urls['import'][] = 
					preg_replace( '/\\\\(.)/u', '\\1', $match );
	 
		// url(...)
		// url('...')
		// url("...")
		foreach ( $matches[11] as $match )
			if ( !empty($match) )
				$urls['property'][] = 
					preg_replace( '/\\\\(.)/u', '\\1', $match );
	 
		return $urls;
	}
	
	public static function get_absolute_url($u, $source){
		
		$parse = parse_url($source);
		
		if( !empty($u) && $u[0] != '#' && parse_url($u, PHP_URL_SCHEME) == ''){
		
			if( !empty($u[1]) && $u[0].$u[1] == '//'){

				$u =  $parse['scheme'].'://'.substr($u, 2);
			}
			elseif( $u[0] == '/' ){
				
				$u = $parse['scheme'].'://'.$parse['host']. $u;
			}
			elseif( !empty($u[1]) && $u[0].$u[1] == './'){
				
				$u = dirname($source) . substr($u, 2);
			}
			elseif( !empty($u[1]) && !empty($u[2]) && $u[0].$u[1].$u[2] == '../'){
				
				$u = dirname(dirname($source)) . substr($u, 2);
			}
			elseif( substr($source, -1) == '/' ){
				
				$u = $source . $u;
			}
			else{
				
				$u = dirname($source) . '/' . $u;
			}
		}
		
		if( strpos($u,'#') ){
		
			$u = strstr($u, '#', true);
		}

		return $u;		
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
	
	public function register_theme() {
		
		$this->theme = new LTPLE_Client_Theme($this);
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
	public function enqueue_styles() {
		
		wp_register_style( $this->_token . '-jquery-ui', esc_url( $this->assets_url ) . 'css/jquery-ui.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-jquery-ui' );		
	
		wp_register_style( $this->_token . '-bootstrap-css', esc_url( $this->assets_url ) . 'css/bootstrap.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap-css' );		

		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );		
		
		wp_register_style( $this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'css/bootstrap-table.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap-table' );

		
		wp_register_style( $this->_token . '-toggle-switch', esc_url( $this->assets_url ) . 'css/toggle-switch.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-toggle-switch' );	
		
		wp_register_style( $this->_token . '-client', false, array());
		wp_enqueue_style( $this->_token . '-client' );
		wp_add_inline_style( $this->_token . '-client', $this->get_inline_style() );

	} // End enqueue_styles ()
	
	public function get_inline_style(){
		
		$style = '';
		
		$style .='#ltple-wrapper *::-webkit-scrollbar-track{';
			
			//$style .='-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);';
			$style .='border-radius: 0px;';
			$style .='background-color: transparent;';
		$style .='}';

		$style .='#ltple-wrapper *::-webkit-scrollbar{';
			
			$style .='width:6px;';
			$style .='background-color: transparent;';
		$style .='}';

		$style .='#ltple-wrapper *::-webkit-scrollbar-thumb{';
			
			$style .='border-radius: 3px;';
			//$style .='-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);';
			$style .='background-color: rgb(229, 229, 229);';
	
		$style .='}';
		
		$style .='#header_logo {';
			
			$style .='max-width:90px;';
			$style .='width:100%;';
			$style .='height: 50px;';
			$style .='z-index: 9999;';
			$style .='position: absolute;';
			$style .='overflow: hidden;';
			$style .='display: inline-block;';
			$style .='background-position: center left;';				
			$style .='background-image:url(' . $this->assets_url . 'images/header_small.png);';
		
		$style .='}';
		
		$style .='#header_logo a {';
		
			$style .='padding:8px 4px;';
			$style .='height:50px;';
			$style .='width:100%;';
			$style .='border:none;';
			$style .='display:inline-block;';
			$style .='text-align:center;';
			
		$style .='}';
		
		$style .='#header_logo a img {';
			
			$style .='width: auto;';
			$style .='height: 35px;';
			$style .='margin-left: -10px;';
			
		$style .='}';
		
		$style .='#main-menu {';
		
			$style .='padding-left:72px;';
		
		$style .='}';	
		
		$style .=' .tabs-left, .tabs-right {';
		
			$style .='padding-top:0 !important;';
		
		$style .='}';
		
		$style .=' .tabs-left {';
		
			$style .='padding: 0 6px !important;';
		
		$style .='}';

		$style .=' .tabs-left>li, .tabs-right>li {';
		
			$style .='margin-bottom:0 !important;';
		
		$style .='}';

		$style .=' .tabs-left>li {';
			$style .='margin: 0 -12px 0 -6px !important;';
			$style .='border-top: 1px solid #fff;';
			$style .='border-bottom: 1px solid #eee;';
		$style .='}';		

		$style .= ' .tabs-left>li.active>a, .tabs-left>li.active>a:focus, .tabs-left>li.active>a:hover{';
			
			$style .= 'border-radius:0;';
			$style .= 'box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);';
			
		$style .='}';

		if( !empty($this->settings->navbarColor) ){
			
			$style .=' .navbar{';
				
				$style .='background:'.$this->settings->navbarColor.' !important;';
				
			$style .='}';
		}
		
		if( !empty($this->settings->mainColor) ){
		
			$style .=' .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover{';	
			
				$style .='background-color:'.$this->settings->mainColor.' !important;';
				
			$style .='}';
	
			$style .=' .navbar-collapse .nav>li>a:hover, .navbar-nav>.active, #search a, .nav-next a:link, .nav-next a:visited, .nav-previous a:link, .nav-previous a:visited {';

				$style .='background-color:'.$this->settings->mainColor.' !important;';
			
			$style .='}';
			
			$style .='.nav-next a:hover, .nav-next a:hover, .nav-previous a:hover, .nav-previous a:hover{';
				
				$style .='color:#fff !important;';
				
			$style .='}';

			$style .='.single .entry-content {';
			
				$style .='font-size: 16px;';
				$style .='line-height: 40px;';
				
			$style .='}';			
			
			$style .='.single .entry-content h1, .single .entry-content h2, .single .entry-content h3, .single .entry-content h4{';
				
				$style .='color:' . $this->settings->mainColor . ' !important;';
				$style .='font-weight:bold !important;';
			
			$style .='}';
			
			$style .='.panel-header h1{';

				$style .='font-size: 24px;';
			
				if( $this->settings->titleBkg ){
						
					$style .='font-weight: normal !important;';
					$style .='text-transform: uppercase !important;';
					$style .='padding: 45px 30px !important;';						
						
					$style .='color:#fff !important;';
					
					$style .='background-image: url(' . $this->settings->titleBkg . ') !important;';
					$style .='background-size: cover !important;';
					$style .='background-position: center center !important;';
					$style .='background-repeat: no-repeat !important;';
					$style .='background-repeat: no-repeat !important;';
				}
				else{
					
					$style .='color:' . $this->settings->mainColor . ' !important;';
				}
				
			$style .='}';
				
			$style .=' span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {';
				$style .='background-color: '.$this->settings->mainColor.' !important;';
				
				if( !empty($this->settings->borderColor) ){
					
					$style .='border: 0px solid '.$this->settings->borderColor.' !important;';
				}
				
			$style .='}';
			
			$style .= ' .bs-callout {';
				
				$style .= 'background-color:#fff !important;';
				
			$style .='}';
			
			$style .= ' .bs-callout-primary{';
			
				$style .='border-left: 5px solid '.$this->settings->mainColor . ' !important;';
				
			$style .='}';				
			
			$style .= ' .tabs-left>li.active>a, .tabs-left>li.active>a:focus, .tabs-left>li.active>a:hover{';
			
				$style .='border-left: 5px solid '.$this->settings->mainColor . ' !important;';
				$style .='background-color: #fbfbfb !important;';
				$style .='margin-top: -1px;';
				$style .='padding:15px 20px;';
				
			$style .='}';
			
			$style .= '#content .nav{';
				
				$style .='padding-right:0px !important;';
				
			$style .='}';

			$style .= '@media (min-width: 768px) {';
				
				$style .= '#content .nav{';
					
					$style .='padding-right:250px !important;';
					
				$style .='}';
				
			$style .= '}';
			
			$style .= '@media (min-width: 992px) {';
				
				
			$style .= '}';
			
			$style .= '@media (min-width: 1200px) {';
				
								
			$style .= '}';			
			
			$style .= ' .nav>li>a{';
				
				$style .='padding:15px 24px;';
				
			$style .='}';
			
			$style .= ' .bs-callout-primary h4{';
			
				$style .='color:'.$this->settings->linkColor . ';';
			
			$style .='}';
			
			$style .='footer#colophon h1, footer#colophon h2, footer#colophon h3{';
			
				$style .='border-bottom: 1px solid '.$this->settings->mainColor . ' !important;';
			
			$style .='}';
			
			$style .=' .gallery_type_title {';
			
				$style .='color: #4276a0;';
				$style .='border: none !important;';
				$style .='background-color: #fdfdfd !important;';
				$style .='font-size:13px;';
				$style .='box-shadow: 0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);';
				$style .='height:41px;';
				$style .='padding:7px 10px;';
				$style .='text-transform: uppercase;';
					
			$style .='}';
			
			$style .='.gallery_head {';
				
				$style .='background-color:#4276a0 !important;';
				$style .='color:#fff !important;';
				$style .='margin-bottom:1px!important';
			
			$style .='}';
			
			$style .='#plan_table table {';
				
				$style .='width: 100%;';
				
			$style .='}';
			
			$style .='#plan_table table th {';
			
				//$style .='background-color: '.$this->settings->mainColor . ';';
				$style .='background-color: #4276a0;';
				$style .='color: #fff;';
				$style .='font-weight: bold;';
				
			$style .='}';
		
			$style .='#plan_table table .badge{';
			
				$style .='font-size: 17px;';
				$style .='padding: 2px 8px;';
				$style .='border-radius: 5px;';
				$style .='line-height: 15px;';
				$style .='margin-top: -2px;';
				
			$style .='}';
		
			$style .='#plan_table table th .badge{';
			
				$style .='background-color: #fff;';
				//$style .='color: '.$this->settings->mainColor . ';';
				$style .='color: #4276a0;';
				
			$style .='}';
			
			$style .='#plan_table table td .badge{';
			
				$style .='background-color: #4276a0;';
				$style .='color: #fff;';
				
			$style .='}';
			
			$style .='#plan_table table td {';
			
				$style .='font-size: 19px;';
				$style .='color: #4276a0;';
				
			$style .='}';
			
			$style .='#plan_table .plan_section {';
				
				$style .='color: '.$this->settings->mainColor . ';';
				$style .='font-size: 22px;';
				$style .='font-weight: normal;';
				$style .='display: block;';
				$style .='cursor: pointer;';
				$style .='width: 100%;';
				$style .='text-align: left;';
				$style .='border: none;';
				$style .='background: #fff;';
				$style .='padding: 20px;';
				$style .='margin: 15px 0 15px 0;';
				$style .='box-shadow: 0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);';
			
			$style .='}';
			
			if( is_plugin_active('wpforo/wpforo.php') ){
				
				$style .=' #wpforo-wrap .wpfl-1 .wpforo-category, #wpforo-wrap .wpfl-2 .wpforo-category, #wpforo-wrap .wpfl-3 .wpforo-category {';
				
					$style .='background-color: '.$this->settings->mainColor . ';';
					 
				$style .='}';
			} 
		}
		
		if( !empty($this->settings->linkColor) ){
			
			$style .=' a, .colortext, code, .infoareaicon, .fontawesome-icon.circle-white, .wowmetaposts span a:hover, h1.widget-title, .testimonial-name, .mainthemetextcolor, .primarycolor, footer#colophon a:hover, .icon-box-top h1:hover, .icon-box-top.active a h1{';
				
				$style .='color:'.$this->settings->linkColor . ';';
				
			$style .='}';				
		}	

		return $style;
	}
	
	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_enqueue_script('jquery-ui-dialog');
		
		wp_register_script($this->_token . '-bootstrap-js', esc_url( $this->assets_url ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-js' );			
		
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), time());
		wp_enqueue_script( $this->_token . '-frontend' );
		
		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );	

		//wp_register_script($this->_token . '-sprintf', esc_url( $this->assets_url ) . 'js/sprintf.js', array( 'jquery' ), $this->_version);
		//wp_enqueue_script( $this->_token . '-sprintf' );	
		
		wp_register_script($this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'js/bootstrap-table.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table' );

		//wp_register_script($this->_token . '-bootstrap-table-export', esc_url( $this->assets_url ) . 'js/bootstrap-table-export.js', array( 'jquery', $this->_token . 'sprintf' ), $this->_version);
		//wp_enqueue_script( $this->_token . '-bootstrap-table-export' );
		
		//wp_register_script($this->_token . '-table-export', esc_url( $this->assets_url ) . 'js/tableExport.js', array( 'jquery' ), $this->_version);
		//wp_enqueue_script( $this->_token . '-table-export' ); 
		
		wp_register_script($this->_token . '-bootstrap-table-mobile', esc_url( $this->assets_url ) . 'js/bootstrap-table-mobile.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table-mobile' ); 		

		wp_register_script($this->_token . '-bootstrap-table-filter-control', esc_url( $this->assets_url ) . 'js/bootstrap-table-filter-control.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table-filter-control' ); 		
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
	
		wp_register_style( $this->_token . '-server-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-server-admin' );
		
		wp_register_style( $this->_token . '-bootstrap', esc_url( $this->assets_url ) . 'css/bootstrap.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap' );	
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_enqueue_script('jquery-ui-sortable');
		
		wp_register_script( $this->_token . '-client-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-client-admin' );

		wp_register_script($this->_token . '-bootstrap', esc_url( $this->assets_url ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap' );		
		
		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );
		
		wp_register_style( $this->_token . '-toggle-switch', esc_url( $this->assets_url ) . 'css/toggle-switch.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-toggle-switch' );

	} // End admin_enqueue_scripts ()
	
	public function get_login_logo(){
		
		echo'<style type="text/css">';
		
			echo'#login h1 a, .login h1 a {';
			
				if( !empty($this->settings->options->logo_url) ){
					
					echo'background-image:url('.$this->settings->options->logo_url.');';
					echo'background-repeat:no-repeat;';
				}
				else{
					
					echo'display:none;';
				}
				
			echo'}';
			
		echo'</style>';		
	}
	
	public function get_login_logo_url() {
		
		return home_url();
	}
	
	public function get_login_logo_url_title() {
		
		return get_bloginfo('name');
	}
		
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
	public function load_plugin_textdomain() {
	    $domain = 'live-template-editor-client';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

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
	public static function instance( $file = '', $version = '1.0.0' ) {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public static function install() {
		
		// store version number
		
		//$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		
		update_option( $this->_token . '_version', $this->_version );
	}
}
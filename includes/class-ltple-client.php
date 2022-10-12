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
	
	public $filesystem = null;
	
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

	public $server;
	public $theme;
	public $user;
	public $layer;
	public $message;
	public $dialog;
	public $canonical_url;
	public $triggers;
	public $inWidget;
	public $modalId;
	
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
		$this->assets_url 	= home_url( trailingslashit( str_replace( ABSPATH, '', $this->dir ))  . 'assets/' );
		
		$this->inWidget = ( ( isset($_GET['output']) && $_GET['output'] == 'widget' ) ? true : false );
		
		$this->modalId  = ( ( $this->inWidget && !empty($_GET['modal']) && is_string($_GET['modal']) ) ? $_GET['modal'] : false );
		
		add_filter('shutdown',array( $this , 'handle_error' ));
		
		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Handle localisation
		
		$this->load_plugin_textdomain();
	
		add_action('init', array( $this, 'load_localisation' ), 0 );		
		
		$this->client 		= new LTPLE_Client_Client( $this );
		$this->request 		= new LTPLE_Client_Request( $this );
		$this->email 		= new LTPLE_Client_Email( $this );
		$this->session 		= new LTPLE_Client_Session( $this );
		$this->triggers		= new LTPLE_Client_Triggers( $this );
								
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

		$this->editor 	= new LTPLE_Client_Editor( $this );
		
		$this->websocket = new LTPLE_Client_Websocket( $this );
	
		$this->media 	= new LTPLE_Client_Media( $this );
		 
		$this->apps 	= new LTPLE_Client_Apps( $this );
		
		$this->gallery 	= new LTPLE_Client_Gallery( $this );			
		
		$this->element 	= new LTPLE_Client_Element( $this );
		
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
		$this->account 	= new LTPLE_Client_Account( $this );
		$this->profile 	= new LTPLE_Client_Profile( $this );
		
		$this->extension = new LTPLE_Client_Extension( $this );
					
		$this->update = new LTPLE_Client_Update( $this );
		
		if( is_admin() ) {		
		
			add_action( 'init', array( $this, 'init_backend' ));
		}
		else{
			
			add_action( 'init', array( $this, 'init_frontend' ));
		}
		
		add_action( 'ltple_editor_action', array( $this, 'do_editor_action'),99999999 );
	
	} // End __construct ()
	
	public function handle_error(){
	
		if( $error = error_get_last() ) {
			
			$skip_message = array(
				
				'Unknown: file created in the system',
				'ftp_chmod(): SITE CHMOD command failed.',
				'ftp_chdir(): Failed to change directory.',
				'Undefined property: WP_Post::$filter',
				'Incorrect APP1 Exif Identifier Code',
			);
			
			$skip_file = array(
			
				WP_PLUGIN_DIR . '/live-template-editor-app-twitter/vendor/abraham/twitteroauth/src/SignatureMethod.php',
			); 
			
			if( !in_array($error['message'],$skip_message) && !in_array($error['file'],$skip_file) ){

				$error['url'] 	= ( is_ssl() ? home_url('','https') : home_url() ) . $_SERVER['REQUEST_URI'];
				
				$error['user'] 	= get_current_user_id();

				wp_mail( get_option('admin_email'),'debugging LTPLE Client error',print_r($error,true));				
			}
		}
	}
	
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
	
	public function output_message(){
	
		if(!empty($this->message)){ 

			//output message

			echo $this->message;
		}
		
		echo $this->session->get_user_data('message');
	}
	
	public function exit_alert($message,$code=200){
		
		$class = $code < 400 ? 'success' : 'alert';
		
		http_response_code($code);
		
		$this->message = '<div class="alert alert-' . $class . '">';
			
			$this->message .= $message;
		
		$this->message .= '</div>';
		
		include( $this->views . '/message.php' );
		
		exit;
	}
	
	public function exit_message($message,$code=200,$data=array()){
		
		if( is_string($message) ){
			
			$data['message'] = $message;
		}
		else{
			
			$data = array_merge($data,$message);
		}
		
		http_response_code($code);

		echo json_encode($data);
		
		exit;
	}
	
	public function init_frontend(){	

		// Load frontend JS & CSS

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 99999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		
		add_action( 'login_enqueue_scripts', array( $this, 'get_login_logo' ) );
		add_filter( 'login_headerurl', array( $this, 'get_login_logo_url' ) );
		
		add_filter( 'login_headertext', array( $this, 'get_login_header_text' ) );

		// add editor shortcodes
		
		add_shortcode('ltple-client-editor', array( $this , 'get_gallery_shortcode' ) );
		
		// add apps shortcodes
		
		add_shortcode('ltple-client-apps', array( $this , 'get_apps_shortcode' ) );		
		
		add_action('ltple_message', array( $this, 'output_message') );	
		
		// add footer
		
		add_action('wp_footer', array( $this, 'get_footer') );	
		
		// Custom default layer template
		
		add_filter('ltple_layer_template', array( $this, 'filter_template_path'),1,2 );
	
		// get current user

		$this->set_current_user();
		
		// loaded hook
		
		do_action( 'ltple_loaded');
	}
	
	public function in_ui(){
		
		if( $this->profile->id > 0 && LTPLE_Editor::get_framework('css') == 'bootstrap-4' )
			
			return false;

		global $post;
		
		if( !empty($post->post_type) && $post->post_type == 'default-element' )
			
			return false;

		
		return apply_filters('ltple_in_ui',true);
	}
	
	public function get_current_user(){
		
		return  $this->set_current_user();
	}

	public function set_current_user($user_id=null){
		
		if( !isset($this->user->plan) ){
			
			if( !empty($user_id) ){
				
				$this->user = get_user_by( 'id',$user_id);
				
				if( !empty($this->user->ID) ){
					
					wp_set_current_user($this->user->ID);
				}
			}
			elseif( $this->request->is_remote ){

				$this->user = get_user_by( 'id', $this->ltple_decrypt_str($_SERVER['HTTP_X_FORWARDED_USER']));
			
				if( !empty($this->user->ID) ){
					
					wp_set_current_user($this->user->ID);
				}
				else{
					
					$this->exit_message('Wrong remote request...',404);				
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
				
					$this->user->can_edit = true;	
				}
				else{
				
					$this->user->can_edit = current_user_can( 'editor', $this->user->ID );			
				}
				
				// get user last seen
				
				$this->user->last_seen = intval( get_user_meta( $this->user->ID, $this->_base . '_last_seen',true) );
								
				
				// get user notification settings
				
				$this->user->notify 		= $this->users->get_user_notification_settings( $this->user->ID );
				$this->user->can_spam 		= $this->user->notify['series'];
				$this->user->can_spam_set 	= ( !empty(get_user_meta($this->user->ID, $this->_base . '_can_spam',true)) ? true : false );
				
				// get user last user agent
				
				$this->user->last_uagent = get_user_meta( $this->user->ID, $this->_base . '_last_uagent',true);
							
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
					
					$this->user->period_end = $this->plan->get_license_period_end( $this->user->ID );
					
					// get remaining days
					
					$this->user->remaining_days = $this->plan->get_license_remaining_days( $this->user->period_end );
				}			
			
				do_action('ltple_user_loaded');
			}
			else{

				add_action('after_password_reset', array($this,'redirect_password_reset'));
			}
		}
		
		return apply_filters('ltple_current_user',$this->user);
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
		elseif( !$url = $this->session->get_user_data('redirect_to',$user->ID) ){
			
			$url = $this->urls->home;
		}
		
        wp_redirect( $url );
        exit;		
	}	

	public function init_backend(){	
	
		// Load admin JS & CSS
		
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		add_action('ltple_editor_admin_enqueue_scripts', array( $this, 'editor_enqueue_styles' ), 10, 1 );
		
		add_action('admin_head', array($this, 'custom_admin_dashboard_css'));
		
		add_filter('page_row_actions', array($this, 'filter_post_type_row_actions'), 10, 2 );
		add_filter('post_row_actions', array($this, 'filter_post_type_row_actions'), 10, 2 );
		add_filter('tag_row_actions', array($this, 'filter_taxonomy_row_actions'), 10, 2 );
		
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
			
			$this->user->can_edit = true;	
		}
		else{
			
			$this->user->can_edit = current_user_can( 'editor', $this->user->ID );
		}
		
		if( !$this->user->is_admin || !$this->user->can_edit ){
			
			if(!WP_DEBUG){
			
				$url = $this->urls->profile;
				
				wp_redirect($url);
				exit;
			}
		}
		
		// get user rights
		
		$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );
		
		// set user role
		
		if( $this->user->can_edit && !current_user_can( 'list_users', $this->user->ID ) ){
		
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
		
		do_action( 'ltple_user_loaded' );
		
		do_action( 'ltple_admin_loaded' );
		
		do_action( 'ltple_loaded' );
	}
	
	public function custom_admin_dashboard_css() {
		
		echo '<style>';
					
			echo '#adminmenu a {color:' . $this->settings->linkColor . ' !important;}';
			echo '#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head, #adminmenu .wp-menu-arrow, #adminmenu .wp-menu-arrow div, #adminmenu li.current a.menu-top, #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu, .folded #adminmenu li.current.menu-top, .folded #adminmenu li.wp-has-current-submenu { border-left: 5px solid ' . $this->settings->mainColor . '; }';
					
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
			
		echo '</style>';
	}	
	
	public function filter_post_type_row_actions( $actions, $post ){
	
		if( $post->post_type != 'page' && $post->post_type != 'post' ){
		
			// remove quick edit
		
			//unset( $actions['edit'] );
			//unset( $actions['view'] );
			unset( $actions['trash'] );
			unset( $actions['inline hide-if-no-js'] );
			
			if( !isset($actions['duplicate']) ){
			
				// duplicate action
			
				$actions['duplicate'] = '<a href="#duplicateItem" data-toggle="dialog" data-type="post_type:' . $post->post_type . '" data-target="#duplicateItem" class="duplicate-button" data-id="' . $post->ID . '">Duplicate</a>';
			}
		}
		
		// add editor actions
		
		if( $editor_actions = apply_filters('ltple_admin_editor_actions',array(), $post) ){
			
			$layer = LTPLE_Editor::instance()->get_layer($post);
					
			foreach( $editor_actions as $slug => $name ){
				
				if( $this->layer->is_html_output($layer->output) ){
					
					if( $slug == 'edit-with-ltple' ){
						 
						$actions[$slug] = '<a href="' . $layer->urls['edit'] . '">'.$name.'</a>';
					}
					elseif( $slug == 'refresh-preview' ){
						
						$source = get_preview_post_link($post->ID);
						
						// TODO differentiate actions with slug 
						
						$action = '<div id="action-buttons-' . $post->ID . '">';

							$action .= '<a href="#refreshPreview" data-id="' . $post->ID . '" data-title="preview for ' . $post->post_title . '" data-source="' . $source . '" data-toggle="dialog" data-target="#actionConsole" class="action-button">';
								
								$action .= $name;
								 
							$action .= '</a>';

						$action .= '</div>';

						$actions[$slug] = $action;
					}
				}
			}
		}

		return $actions;
	}
	
	public function filter_taxonomy_row_actions( $actions, $term ){
	
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
		
		return apply_filters('ltple_app_types',array(
		
			'networks'  => [],
			'images'	=> [],
			'videos' 	=> [],
			'blogs' 	=> [],
			'payment' 	=> [],
			'streaming' => [],
		));
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
			
			echo '<img loading="lazy" src="' . $post->post_content . '" style="width:100px;" />';
		}
		
		return $column_name;
	}
	
	public function filter_template_path( $path, $layer ){
		
		if( $this->layer->is_default($layer) && empty($_GET['action']) && strpos($this->urls->current,$this->urls->home . '/' . $this->product->slug . '/') === false ){
			
			if( strpos($this->urls->current,$this->urls->home . '/preview/') !== 0 || $this->layer->has_preview($layer->output) ){
				
				if( $this->layer->is_html_output($layer->output) && $layer->output != 'web-app' ){
				
					$show_layer = false;
				
					$visibility = $this->layer->get_layer_visibility($layer);
					
					if( $visibility == 'anyone' ){
						
						$show_layer = true;
					}
					elseif( $visibility == 'registered' && $this->user->loggedin ){
						
						$show_layer = true;
					}
					elseif( $this->plan->user_has_layer( $layer ) === true ){
						
						$show_layer = true;
					}
					elseif( $this->user->can_edit ){
						
						$show_layer = true;
					}
					
					if( $show_layer ){
						
						if( $tab = apply_filters('ltple_preview_profile_tab',false,$this->layer->get_layer_type($layer)) ){
							
							if( $this->user->loggedin ){
								
								$user_id = $this->user->ID;
							}
							else{
								
								$user_id = $layer->post_author;
							}
							
							$this->profile->set_profile($user_id,$tab,$layer->post_name,false);
							
							include($this->views . '/profile.php');					
						}
						elseif( $layer->post_type == 'default-element' ) {
							
							add_filter('ltple_css_framework',function($framework){
								
								return 'bootstrap-4';
								
							},99999999,1);	
							
							return get_stylesheet_directory() . '/templates/landing-page.php';
						}
						else{
							
							return $this->views . '/layer.php';
						}
					}
				}
			}
			
			return $this->views . '/preview.php';
		}
		elseif( $default_id = $this->layer->get_default_id($layer->ID) ){
			
			if( $this->layer->is_local($layer) ){
				
				//theme template
				
				return $path; 
			}
			elseif( $this->user->loggedin ){
				
				if( $this->user->is_admin || intval($layer->post_author ) == $this->user->ID ){
				
					return $this->views . '/layer.php';
				}
				else{
					
					$this->exit_message('You don\'t have access to this template...',404);	
				}
			}
			else{
				
				$this->exit_message('Sign in to access this template...',404);
			}				
		}
		elseif( file_exists($this->views . '/'.$layer->post_type . '.php') ){
			
			return $this->views .  '/' . $layer->post_type . '.php';
		}
		
		return $path;
	}
	
	public function disable_theme() {
		
		return false;
	}

	public function get_header(){
		
		if( $this->profile->id > 0 ){
		
			$post = $this->profile->get_profile_post();
		}
		else{
			
			$post = get_post();
		}
		
		$service_name = get_bloginfo('name');
	
		$site_name = apply_filters('ltple_site_name',$service_name);

		if( !empty($post->post_title) ){
			
			$title = ucfirst($post->post_title);
		}
		else{
			
			$title = $site_name;
		}
		
		$title = apply_filters('ltple_header_title',$title);
		
		if( !empty($title) ){

			// output default meta tags

			echo '<title>' . $title .  ' • ' . $site_name . '</title>'.PHP_EOL;
			
			echo '<meta property="og:site_name" content="' . $site_name . '" />'.PHP_EOL;
			
			echo '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
			echo '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
			echo '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;
		}
		
		if( !empty($post->post_author) ){

			$author_name = get_the_author_meta('display_name', $post->post_author );
			$author_mail = get_the_author_meta('user_email', $post->post_author );
			
			echo '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			echo '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
			echo '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
			echo '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;
		}
		
		if( $locale = get_locale() ){
			
			echo '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
		}
		
		$robots = 'index,follow';
			
		echo '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
			
		if( !empty($post->post_date) ){
			
			$revised = $post->post_date;
			
			echo '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
		}
		
		//get description
		
		if( !empty($post->post_excerpt) ){
			
			$content = ucfirst($post->post_excerpt);
		}
		elseif( !empty($post->post_content) ){
			
			$content = ucfirst($post->post_content);
		}
		elseif( !empty($post->post_title) ){
			
			$content = ucfirst($post->post_title);
		}
		else{
			
			$content = $title;
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
		
		echo '<meta name="copyright" content="'.$site_name.'" />'.PHP_EOL;
		echo '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
		
		if( !empty($post->ID) ){
		
			$canonical_url = get_permalink( $post->ID );

			echo '<meta name="url" content="' . $canonical_url . '" />' . PHP_EOL;
			echo '<meta name="canonical" content="'.$canonical_url.'" />' . PHP_EOL;
			echo '<meta name="original-source" content="'.$canonical_url.'" />' . PHP_EOL;
			echo '<link rel="original-source" href="'.$canonical_url.'" />' . PHP_EOL;
			echo '<meta property="og:url" content="'.$canonical_url.'" />' . PHP_EOL;
			echo '<meta name="twitter:url" content="'.$canonical_url.'" />' . PHP_EOL;
		}
		
		echo '<meta name="rating" content="General" />' . PHP_EOL;
		echo '<meta name="directory" content="submission" />' . PHP_EOL;
		echo '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
		echo '<meta name="distribution" content="Global" />' . PHP_EOL;
		echo '<meta name="target" content="all" />' . PHP_EOL;
		
		if( $og_type = apply_filters('ltple_meta_og_type','article',$post) ){
		
			if( $og_type == 'article' ){
				
				echo '<meta name="medium" content="blog" />' . PHP_EOL;
			}
			
			echo '<meta property="og:type" content="'.$og_type.'" />' . PHP_EOL;
		}
		
		$twitter_card = 'summary';
		
		if( $thumb_id = get_post_thumbnail_id($post) ){
			
			$twitter_card = 'summary_large_image';
			
			if( $image = wp_get_attachment_image_src( $thumb_id, 'full', false ) ){
			
				echo '<meta property="og:image" content="'.$image[0].'" />' . PHP_EOL;
				echo '<meta property="og:image:width" content="'.$image[1].'" />' . PHP_EOL;
				echo '<meta property="og:image:height" content="'.$image[2].'" />' . PHP_EOL;
		
				echo '<meta property="twitter:image" content="'.$image[0].'" />' . PHP_EOL;
				echo '<meta property="twitter:image:width" content="'.$image[1].'" />' . PHP_EOL;
				echo '<meta property="twitter:image:height" content="'.$image[2].'" />' . PHP_EOL;			
			}
		}
		
		echo '<meta name="twitter:card" content="'.$twitter_card.'" />' . PHP_EOL;
	
		// TODO application/ld+json
	
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
	
	public function get_collapse_button(){
		
		$class = $this->inWidget ? 'hidden-sm hidden-lg' : '';
		
		$button = '<button class="'.$class.'" type="button" id="sidebarCollapse">';
				
			$button .='<i class="glyphicon glyphicon-align-left"></i>';
			
		$button .='</button>';
		
		return $button;
	}
	
	public function get_footer(){

		// collect information
		
		if( $this->user->loggedin && !is_admin() && !$this->inWidget ){	
			
			// collect usr information

			if( empty( $this->user->can_spam_set ) && !isset($_POST['can_spam']) ){
				
				include($this->views . '/modals/newsletter.php');
			}
			
			do_action('ltple_collect_user_information');
		}
		
		do_action('ltple_footer');
	}
	
	public function get_apps_shortcode(){
		
		ob_start();
		
		include($this->views . '/navbar.php');
		
		if($this->user->loggedin){

			include($this->views . '/apps.php');
		}
		else{
			
			echo $this->login->get_form();
		}
		
		return ob_get_clean();
	}
	
	public function get_gallery_shortcode(){
		
		ob_start();
		
		include($this->views . '/navbar.php');
			
		include($this->views . '/gallery.php');
		
		return ob_get_clean();
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
	
	public function do_editor_action(){	
		
		if( $this->user->loggedin && !empty($this->layer->id) && $this->layer->id > 0 ){
			
			if( isset($_POST['postAction'])&& $_POST['postAction'] == 'edit' ){
						
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
								
								$fields = $this->layer->get_user_layer_fields(array(),$post);
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
							
							do_action('ltple_edit_layer',$post_id,$post);
						
							$this->exit_message('Settings successfully updated!', 200, apply_filters('ltple_edit_layer_callback',[],$post));
						}
						
						$this->exit_message('Access to project denied...',404);
					}
				}
				
				$this->exit_message('Error retrieving project...',404);
			}
			elseif( isset($_GET['postAction'])&& $_GET['postAction']=='delete' ){
				
				if( $this->layer->author == $this->user->ID ){
					
					// get local images
				
					$image_dir = $this->image->dir . $this->user->ID . '/';
					$image_url = $this->image->url . $this->user->ID . '/';	
				
					$images = glob( $image_dir . $this->user->layer->ID . '_*.png');				
					
					if( !isset($_GET['confirmed']) ){
					
						// confirm deletion

						$message = '<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
							
							$message .= '<h2>Are you sure you want to delete this  ' . $this->layer->get_storage_name($this->layer->layerStorage) . '?</h2>';
						
							if( !empty($images) ){
								
								$message .= '<hr></hr>';

								$message .= '<div style="margin-top:20px;" class="alert alert-warning">The following images will be removed</div>';
								
								$message .= '<div style="margin-top:20px;">';

									foreach ($images as $image) {
										
										$message .= '<div class="row">';
										
											$message .='<div class="col-xs-3 col-sm-3 col-lg-2">';

												$message .='<img class="lazy" data-original="' . $image_url . basename($image) .'" />';
													
											$message .='</div>';

											$message .='<div class="col-xs-9 col-sm-9 col-lg-10">';

												$message .='<b style="overflow:hidden;width:90%;display:block;">' . basename($image) . '</b>';
												$message .='<br>';
												$message .='<input style="width:100%;padding: 2px;" type="text" value="'. $image_url . basename($image) .'" />';

											$message .='</div>';										
										
										$message .= '</div>';
									}
									
								$message .= '</div>';
							}
								
							$message .= '<hr></hr>';	
								
							$message .= '<div style="margin-top:10px;text-align:right;">';						
								
								$message .= '<a style="margin:10px;" class="btn btn-lg btn-success" href="' . $this->urls->current . '&confirmed">Yes</a>';
								
								$message .= '<a style="margin:10px;" class="btn btn-lg btn-danger" href="' . $this->urls->edit . '?uri=' . $this->user->layer->ID . '">No</a>';

							$message .= '</div>';
						
						$message .= '</div>';
						
						$this->session->update_user_data('message',$message);
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
						
						if( $_GET['confirmed'] == 'self' ){
						
							$message ='<div class="alert alert-success">';
									
								$message .= 'Template successfully deleted.';

							$message .='</div>';
							
							$this->session->update_user_data('message',$message);
						
							wp_redirect($this->urls->dashboard . '?list=' . $layer_type->storage);
							exit;
						}
						else{
							
							$this->exit_message('Template successfully deleted!',200);
							
						}
					}
				}
			}
			elseif( isset($_POST['postAction']) && $_POST['postAction'] == 'download' ){
				
				$this->layer->download_static_contents($this->layer->id);
			}
			elseif( isset($_POST['postContent']) && !empty($this->layer->type) ){
				
				// get post content
				
				$post_content 	= LTPLE_Editor::sanitize_content( $_POST['postContent'] );
				
				$post_json 		= ( !empty($_POST['postJson']) ? LTPLE_Editor::sanitize_json( $_POST['postJson'] ) : '' );
				
				$post_css 		= ( !empty($_POST['postCss']) ? sanitize_meta('layerCss',$_POST['postCss'],'post') : '' ); // unslash breaks unicode char
				$post_js 		= ( !empty($_POST['postJs'])  ? sanitize_meta('layerJs',stripcslashes( $_POST['postJs'] ),'post') : '' );

				$post_title 	= ( !empty($_POST['postTitle']) ? wp_strip_all_tags( $_POST['postTitle'] ) 	 : '' );
				$post_name 		= $post_title;			
				
				if( $_POST['postAction'] == 'update' ){
					
					//update layer
					
					if( $this->user->can_edit ){
					
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
								
								$this->exit_message('Template successfully updated!',200);
							}
						}
						else{
						
							$this->exit_message('Error getting default layer ID...',404);
						}
					}
					else{
						
						$this->exit_message('Update permission denided...',404);
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
								
								if( $layerMeta = get_post_meta($layerId) ){
						
									foreach($layerMeta as $name => $value){
										
										if( isset($value[0]) ){
											
											update_post_meta( $post_id, $name, maybe_unserialize($value[0]) );
										}
									}
								}
								
								// duplicate all taxonomies
								
								if( $taxonomies = get_object_taxonomies($layer->post_type) ){
								
									foreach ($taxonomies as $taxonomy) {
										
										if( !apply_filters('ltple_duplicate_' . $layer->post_type . '_bail_tax_' . $taxonomy, false) ){
										
											$layerTerms = wp_get_object_terms($layerId, $taxonomy, array('fields' => 'slugs'));
										
											wp_set_object_terms($post_id, $layerTerms, $taxonomy, false);
										}
									}
								}
								
								//redirect to user layer

								$layer_url = $this->urls->edit . '?uri=' . $post_id . '&action=edit';

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
										
									$layer_url = $this->urls->edit . '?uri=' . $post_id;
									
									//redirect to user layer

									wp_redirect($layer_url);
									echo 'Redirecting editor...';
									exit;
								}							
							}
						}
					}
					else{
						
						$this->exit_alert('You don\'t have enough right to perform this action...',404);
					}
				}
				elseif( isset($_POST['postAction']) && $_POST['postAction'] == 'save' ){				

					//save layer
					
					$post_id = '';
					$defaultLayerId = -1;
					
					if( empty($this->layer->layerStorage) ){
						
						$this->exit_alert('Wrong template storage...',404);
					}
					elseif( $this->layer->type != 'cb-default-layer' ){
						
						$post_id		= $this->user->layer->ID;
						$post_author	= $this->user->layer->post_author;
						$post_title		= $this->user->layer->post_title;
						$post_name		= $this->user->layer->post_name;
						$post_status 	= $this->user->layer->post_status;
						$post_type		= $this->layer->type; // user-layer, post, page...
						
						$defaultLayerId	= intval(get_post_meta( $post_id, 'defaultLayerId', true));
					}
					else{
						
						$post_type = $this->layer->layerStorage;
						
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
							
							$post_author = $this->user->ID;
							
							if( !$this->plan->remaining_storage_amount($defaultLayer) > 0 ){
								
								$layer_type = $this->layer->get_layer_type($defaultLayer);
								
								$this->exit_alert('You can\'t save more projects from the <b>' . $layer_type->name . '</b> gallery with the current plan...',404);
							}

							$defaultLayerId	= intval( $defaultLayer->ID );
						}
						else{
							
							$this->exit_alert('This default layer doesn\'t exists...',404);						
						}
					}
					
					if( $post_title!='' && is_int($defaultLayerId) && $defaultLayerId > 0 ){
						
						$time 	= current_time('mysql');
						$gmt 	= get_gmt_from_date($time);
						
						if( $post_id > 0 ){
						
							$post_id = wp_update_post(array(
								
								'ID' 			=> $post_id,
								'post_author' 	=> $post_author,
								'post_title' 	=> $post_title,
								'post_name' 	=> $post_name,
								'post_type' 	=> $post_type,
								'post_status' 	=> $post_status,
								'post_date' 	=> $time,
								'post_date_gmt' => $gmt,
							));
						}
						else{
							
							$post_id = wp_insert_post(array(
								
								'post_author' 	=> $post_author,
								'post_title' 	=> $post_title,
								'post_name' 	=> $post_name,
								'post_type' 	=> $post_type,
								'post_status' 	=> $post_status,
								'post_date' 	=> $time,
								'post_date_gmt' => $gmt,
							));							
						}
						
						if( is_numeric($post_id) ){
							
							update_post_meta($post_id, 'defaultLayerId', $defaultLayerId);
							
							update_post_meta($post_id, 'layerContent', $post_content);
							
							update_post_meta($post_id, 'layerJson', $post_json);
							
							update_post_meta($post_id, 'layerCss', $post_css);
							
							update_post_meta($post_id, 'layerJs', $post_js);
							
							if( $this->layer->type == 'cb-default-layer' ){
								
								// update layer type
								
								$terms = wp_get_post_terms($defaultLayerId,'layer-type');
								
								if( !empty($terms[0]) ){

									wp_set_object_terms( $post_id, $terms[0]->term_id, 'layer-type', false ); 
								}
								
								//redirect to user layer

								$user_layer_url = $this->urls->edit . '?action=edit&uri=' . $post_id;
								
								wp_redirect($user_layer_url);
								echo 'Redirecting editor...';
								exit;					
							}
							else{
								
								$this->exit_message('Template successfully saved!',200);
							}
						}
					}

					$this->exit_message('Error saving user layer...',404);
				}
				else{
					
					$this->exit_alert('This action doesn\'t exists...',404);					
				}
			}
			elseif( $_SERVER['REQUEST_METHOD'] === 'POST' ){
				
				if( !empty($this->layer->layerOutput) && $this->layer->layerOutput == 'image' ){
				
					if( $this->layer->upload_image_template() ){
						
						$this->exit_message('Template successfully saved!',200);						
					}
					else{
						
						$this->exit_message('Error Saving Template...',404);
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
	
		$this->editor_enqueue_styles();

		wp_register_style( $this->_token . '-toggle-switch', esc_url( $this->assets_url ) . 'css/toggle-switch.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-toggle-switch' );
	
		wp_register_style( 'fontawesome-5', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css', array(), $this->_version );
		wp_enqueue_style( 'fontawesome-5' );			
		
		global $post;
		
		if( empty($post->post_type) || $post->post_type != 'default-element' ){
		
			wp_register_style( $this->_token . '-client', false,array($this->_token . '-bootstrap-css','theme-style',$this->_token . '-client-ui'));
			wp_enqueue_style( $this->_token . '-client' );
		
			wp_add_inline_style( $this->_token . '-client', $this->get_client_style() );
		}
	}
	
	public function get_client_style(){
		
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

		if( !empty($this->settings->navbarColor) ){
			
			$style .=' .navbar, .tinynav, .dropdown-menu {';
				
				$style .='background:'.$this->settings->navbarColor.' !important;';
				
			$style .='}';
		}
		
		if( !empty($this->settings->mainColor) ){
			
			$style .=' .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover{';	
			
				$style .='background-color:'.$this->settings->mainColor.' !important;';
				
			$style .='}';
	
			$style .=' .navbar-collapse .nav>li>a:hover, .navbar-nav>.active, #search a, .nav-next a:link, .nav-next a:visited, .nav-previous a:link, .nav-previous a:visited, a.totop, a.totop:hover {';

				$style .='background-color:' . $this->settings->mainColor . ' !important;';
			
			$style .='}';
			
			$style .='.nav-next a:hover, .nav-next a:hover, .nav-previous a:hover, .nav-previous a:hover{';
				
				$style .='color:#fff !important;';
				
			$style .='}';		
			
			$style .='.single .entry-content h1, .single .entry-content h2, .single .entry-content h3, .single .entry-content h4{';
				
				$style .='color:' . $this->settings->mainColor . ' !important;';
				$style .='font-weight:bold !important;';
			
			$style .='}';
			
			$style .='.panel-header .page-title, .entry-content .page-title {';

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
			
			// bs modals

			$style .='.fade.in {';
				$style .='opacity: 1;';
			$style .='}';

			$style .='.modal-header, .ui-widget-header {';
				$style .='padding: 4px 10px !important;';
				$style .='background: '. $this->settings->navbarColor .' !important;';
				$style .='color: #fff;';
				$style .='border-bottom: none;';
				$style .='font-size: 16px;';
				$style .='display: block;';
			$style .='}';

			$style .='.modal-header .close span {';
				$style .='color: #fff;';
				$style .='opacity: 1;';
				$style .='font-size: 35px;';
				$style .='filter: none;';
				$style .='box-shadow: none;';
				$style .='float: right;';
			$style .='}';

			$style .='.modal-title {';
				$style .='margin: 0;';
				$style .='line-height: 40px;';
				$style .='font-size: 20px;';
				$style .='color: #fff;';
				$style .='display:inline-block;';
			$style .='}';

			$style .='.modal-content{';
				
				$style .='border-radius: 0 !important;';	
			$style .='}';

			$style .='.modal-full{';
				   
				$style .='display: contents;';
				$style .='width:100vw !important;';
				$style .='height: 100vh !important;';
				$style .='margin:0;';
				$style .='top:0;';
				$style .='bottom:0;';
				$style .='left:0;';
				$style .='right:0;';
				$style .='position:absolute;';
			$style .='}';

			$style .='.modal-full .modal-content {';

				$style .='width:100vw !important;';
				$style .='height: 100vh !important;';
				$style .='border: none;';
				$style .='overflow: hidden;';
			$style .='}';

			$style .='@media (min-width: 992px){';
				
				$style .='.modal-lg {';
					
					$style .='width: 1050px !important;';
				$style .='}';
			$style .='}';

			// wedocs
			
			$style .='.wedocs-sidebar .widget-title {';
			
				$style .='color:#fff !important;';
				$style .='background-color: ' . $this->settings->navbarColor . 'b8 !important;';
				$style .='font-size: 15px !important;';
				$style .='text-transform:uppercase;';
				$style .='box-shadow: 0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);';
				$style .='border: none !important;';
			
			$style .='}';

			$style .='.wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list > li.current_page_parent > a, .wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list > li.current_page_item > a, .wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list > li.current_page_ancestor > a {';
				
				$style .='background: #dbe1e6;';
				$style .='color: #506988;';
				$style .='border-radius: 0px 5px 5px 0px;';
			
			$style .='}';
			
			$style .='.wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list li.wd-state-open > a > .wedocs-caret, .wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list li.wd-state-closed > a > .wedocs-caret, .wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list li a, .wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list li ul.children a {';

				$style .='color: #506988;';
				
			$style .='}';

			$style .='.wedocs-single-wrap .wedocs-single-content article .entry-content img{';			
							
				$style .='max-width: 100%;';
				$style .='border: none;';
				$style .='margin: 0;';
				$style .='padding: 0;';
				$style .='height: auto;';
				$style .='background: transparent;';
				$style .='display: block;';
				
			$style .='}';
			
			$style .='span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {';
				
				$style .='background-color: '.$this->settings->mainColor . ';';
				
				if( !empty($this->settings->mainColor) ){
					
					$style .='border-color: '.$this->settings->mainColor . ';';
				}
				
			$style .='}';
			
			$style .= ' .bs-callout {';
				
				$style .= 'background-color:#fff !important;';
				
			$style .='}';
			
			$style .= ' .bs-callout-primary{';
			
				$style .='border-left: 5px solid '.$this->settings->mainColor . ' !important;';
				
			$style .='}';				
			
			$style .= ' .tabs-left>li.active>a, .tabs-left>li.active>a:focus, .tabs-left>li.active>a:hover{';
			
				$style .='border-radius:0;';
				$style .='box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);';
				$style .='border-left: 5px solid '.$this->settings->mainColor . ' !important;';
				$style .='background-color: #fbfbfb !important;';
				$style .='margin-top: -1px !important;';
				$style .='padding:15px 13px !important;';
				
			$style .='}';
			
			$style .= '#content.library-content a:hover{';
				
				$style .='text-decoration:none !important;';
			
			$style .='}';

			$style .= '.nav>li>a{';
				
				$style .='padding:13px 17px;';
				
				if( !empty($this->settings->linkColor) ){

					$style .='color:'.$this->settings->linkColor . ';';			
				}
				
			$style .='}';
			
			if( !empty($this->settings->navbarColor) ){
				
				$style .= '#ltple-wrapper #sidebar .glyphicon, #ltple-wrapper #sidebar .fa, #ltple-wrapper #sidebar .fab, #ltple-wrapper #sidebar .fas, #ltple-wrapper #sidebar .far{';
					
					$style .='color:'.$this->settings->navbarColor . 'b8;';			
				
				$style .='}';
				
				$style .= '.badge, .btn, .btn:hover, .btn:focus, .btn:active, .btn.active, .btn:active:focus, .btn:active:hover, .btn.active:focus, .btn.active:hover, .open > .btn.dropdown-toggle, .open > .btn.dropdown-toggle:focus, .open > .btn.dropdown-toggle:hover, .btn.btn-default, .btn.btn-default:hover, .btn.btn-default:focus, .btn.btn-default:active, .btn.btn-default.active, .btn.btn-default:active:focus, .btn.btn-default:active:hover, .btn.btn-default.active:focus, .btn.btn-default.active:hover, .open > .btn.btn-default.dropdown-toggle, .open > .btn.btn-default.dropdown-toggle:focus, .open > .btn.btn-default.dropdown-toggle:hover, .navbar .navbar-nav > li > a.btn, .navbar .navbar-nav > li > a.btn:hover, .navbar .navbar-nav > li > a.btn:focus, .navbar .navbar-nav > li > a.btn:active, .navbar .navbar-nav > li > a.btn.active, .navbar .navbar-nav > li > a.btn:active:focus, .navbar .navbar-nav > li > a.btn:active:hover, .navbar .navbar-nav > li > a.btn.active:focus, .navbar .navbar-nav > li > a.btn.active:hover, .open > .navbar .navbar-nav > li > a.btn.dropdown-toggle, .open > .navbar .navbar-nav > li > a.btn.dropdown-toggle:focus, .open > .navbar .navbar-nav > li > a.btn.dropdown-toggle:hover, .navbar .navbar-nav > li > a.btn.btn-default, .navbar .navbar-nav > li > a.btn.btn-default:hover, .navbar .navbar-nav > li > a.btn.btn-default:focus, .navbar .navbar-nav > li > a.btn.btn-default:active, .navbar .navbar-nav > li > a.btn.btn-default.active, .navbar .navbar-nav > li > a.btn.btn-default:active:focus, .navbar .navbar-nav > li > a.btn.btn-default:active:hover, .navbar .navbar-nav > li > a.btn.btn-default.active:focus, .navbar .navbar-nav > li > a.btn.btn-default.active:hover, .open > .navbar .navbar-nav > li > a.btn.btn-default.dropdown-toggle, .open > .navbar .navbar-nav > li > a.btn.btn-default.dropdown-toggle:focus, .open > .navbar .navbar-nav > li > a.btn.btn-default.dropdown-toggle:hover{';
				
					$style .='background-color:' . $this->settings->navbarColor . 'b8;';
					$style .='border-color:' . $this->settings->navbarColor . ';';
			
				$style .='}';
			}
			
			$style .= ' .bs-callout-primary h4{';
			
				$style .='color:'.$this->settings->linkColor . ' !important;';
			
			$style .='}';
			
			$style .='footer#colophon h1, footer#colophon h2, footer#colophon h3{';
			
				$style .='border-bottom: 1px solid '.$this->settings->mainColor . ' !important;';
			
			$style .='}';
			
			$style .=' .gallery_type_title {';
			
				$style .='color: ' . $this->settings->linkColor . ' !important;';
				$style .='border: none !important;';
				$style .='background-color: #fdfdfd !important;';
				$style .='font-size:13px !important;';
				$style .='box-shadow: 0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);';
				$style .='height:41px !important;';
				$style .='padding:7px 10px !important;';
				$style .='text-transform: uppercase;';
					
			$style .='}';
			
			$style .='.gallery_head {';
				
				$style .='background-color:' . $this->settings->navbarColor . 'b8 !important;';
				$style .='color:#fff !important;';
				$style .='margin-bottom:1px!important';
			
			$style .='}';
			
			$style .='#plan_table table {';
				
				$style .='width: 100%;';
				
			$style .='}';
			
			$style .='#plan_table table th {';
			
				$style .='background-color: ' . $this->settings->navbarColor . 'b8;';
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
			
				$style .='background-color: ' . $this->settings->navbarColor . ';';
				$style .='color:#fff;';
				
			$style .='}';
			
			$style .='#plan_table table td .badge{';
			
				$style .='background-color: ' . $this->settings->navbarColor . 'b8;';
				$style .='color: #fff;';
				
			$style .='}';
			
			$style .='#plan_table table td {';
			
				$style .='font-size: 19px;';
				$style .='color: ' . $this->settings->linkColor . ';';
				
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
				
					$style .='background-color: '.$this->settings->mainColor . ' !important;';
					 
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

		if( $this->in_ui() ){

			wp_register_script($this->_token . '-notify', esc_url( $this->assets_url ) . 'js/notify.js', array( 'jquery' ), $this->_version);
			wp_enqueue_script( $this->_token . '-notify' );
		}
		
		wp_register_script($this->_token . '-client-ui', esc_url( $this->assets_url ) . 'js/client-ui.js', array( 'jquery', 'jquery-touch-punch', 'jquery-ui-dialog' ), $this->_version);
		wp_enqueue_script( $this->_token . '-client-ui' );		
		
		wp_register_script($this->_token . '-bootstrap-js', esc_url( $this->assets_url ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-js' );

		if( $this->inWidget ){

			wp_register_script($this->_token . '-widget', '', array( $this->_token . '-client-ui',$this->_token . '-bootstrap-js') );
			wp_enqueue_script($this->_token . '-widget' );
			wp_add_inline_script($this->_token . '-widget', $this->get_widget_script() );
		}

		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );	

	} // End enqueue_scripts ()

	public function get_widget_script(){
		
		$script = '';
		
		if( $this->modalId ){
		
			$script .= ';(function($){' . PHP_EOL;
				
				$script .= 'if ( window.self !== window.top ) {' . PHP_EOL;
				
					//TODO append close bottom from here
				
					$script .= '$(".close_widget").on("click",function(){' . PHP_EOL;

						// hide parent iframe
						
						$script .= '$("#'.$this->modalId.'", window.parent.document).hide();' . PHP_EOL;

					$script .= '});' . PHP_EOL;
					
				$script .= '}' . PHP_EOL;
				
			$script .= '})(jQuery);' . PHP_EOL;
		}

		return $script;
	}

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
	 
		wp_register_style( $this->_token . '-server-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-server-admin' );
	}
	
	
	public function editor_enqueue_styles(){
		
		if( $this->in_ui() ){
			
			wp_enqueue_style( 'jquery-ui-dialog' );
	
			wp_register_style( $this->_token . '-jquery-ui', esc_url( $this->assets_url ) . 'css/jquery-ui.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-jquery-ui' );
		}
		
		wp_register_style( $this->_token . '-client-ui', esc_url( $this->assets_url ) . 'css/client-ui.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-client-ui' );
	}
	

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

		//wp_register_script($this->_token . '-bootstrap', esc_url( $this->assets_url ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->_version);
		//wp_enqueue_script( $this->_token . '-bootstrap' );		
		
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
	
	public function get_login_header_text() {
		
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
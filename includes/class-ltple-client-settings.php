<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Settings {

	/**
	 * The single instance of LTPLE_Client_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */

	public $plugin;

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();
	
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		$this->plugin 			= new stdClass();
		$this->plugin->slug  	= 'live-template-editor-client';
		$this->plugin->title 	= 'Live Template Editor Client';
		$this->plugin->short 	= 'Live Editor';
		
		// get options
		$this->options 				 = new stdClass();
		$this->options->analyticsId  = get_option( $this->parent->_base . 'analytics_id');
		$this->options->emailSupport = get_option( $this->parent->_base . 'email_support');	
		
		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_items' ) );	
		
		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

		// Custom default layer post
		
		add_action('template_redirect', function() {
			
			$post_type = get_post_type();
			
			if( $post_type == 'cb-default-layer' || $post_type == 'user-layer'){

				remove_filter( 'the_content', 'wpautop' );
			}
		});
		
		// Custom default layer editor
		
		add_action('edit_form_after_title', function() {
			
			if($screen=get_current_screen()){
				
				$custom_post_types=[];
				$custom_post_types['cb-default-layer'] 	= '';
				$custom_post_types['user-layer'] 		= '';
				$custom_post_types['default-image'] 	= '';
				$custom_post_types['user-image'] 		= '';
				$custom_post_types['user-url'] 			= '';
				$custom_post_types['email-model'] 		= '';
				$custom_post_types['email-campaign'] 	= '';
			
				if( isset( $custom_post_types[$screen->id] ) ){
					
					add_filter( 'wp_default_editor', array( $this, 'set_default_editor') );
					add_filter( 'admin_footer', array( $this, 'set_admin_edit_page_js'), 99);
					add_filter( 'tiny_mce_before_init', array( $this, 'schema_TinyMCE_init') );	
				}
			}
		});
	}
	
	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings(){
		
		$this->settings = $this->settings_fields();		
		
		$this->schedule_actions();
	}
	
	public function schedule_actions(){
		
		foreach($this->settings as $settings){

			foreach($settings['fields'] as $fields){
			
				if( $fields['type'] == 'action_schedule'){
					
					$key = $this->parent->_base . $fields['id'];
					
					if( !empty($_POST[$key]['every']) ){
						
						// schedule cron event
						
						$every = intval($_POST[$key]['every']);

						if( $every > 14 && $every < 60){
							
							// get recurrence
							
							$event 		= $this->parent->_base . $fields['id'];
							$recurrence = $every.'min';	
							
							// get arguments
							
							$args = [];

							if( !empty($_POST[$key]['args']) ){
								
								foreach($_POST[$key]['args'] as $arg){
									
									if(is_numeric($arg)){
										
										$args[] = floatval($arg);
									}
								}
							}
							
							//remove existing event
							
							$this->parent->cron->remove_event($event);

							//set new event
							
							wp_schedule_event( time(), $recurrence, $event, $args);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
		//add menu in wordpress settings
		
		//$page = add_options_page( __( $this->plugin->title, $this->plugin->slug ) , __( $this->plugin->short, $this->plugin->slug ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		//add_action( 'admin_print_styles' . $page, array( $this, 'settings_assets' ) );
		
		//add menu in wordpress dashboard
		
		add_menu_page($this->plugin->short, $this->plugin->short, 'manage_options', $this->plugin->slug, array($this, 'settings_page'),'dashicons-layout');
		
		add_users_page( 
			'All Subscribers', 
			'All Subscribers', 
			'edit_pages',
			'users.php?'.$this->parent->_base .'view=subscribers'
		);

		add_submenu_page(
			$this->plugin->slug,
			__( 'All Subscribers', $this->plugin->slug ),
			__( 'All Subscribers', $this->plugin->slug ),
			'edit_pages',
			'users.php?'.$this->parent->_base .'view=subscribers'
		);

		add_submenu_page(
			$this->plugin->slug,
			__( 'All Leads', $this->plugin->slug ),
			__( 'All Leads', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=lead'
		);

		add_submenu_page(
			$this->plugin->slug,
			__( 'Affiliate Commissions', $this->plugin->slug ),
			__( 'Affiliate Commissions', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=affiliate-commission'
		);			
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Default Layers', $this->plugin->slug ),
			__( 'Default Layers', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=cb-default-layer'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Default Images', $this->plugin->slug ),
			__( 'Default Images', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=default-image'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Layers', $this->plugin->slug ),
			__( 'User Layers', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-layer'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Apps', $this->plugin->slug ),
			__( 'User Apps', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-app'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Images', $this->plugin->slug ),
			__( 'User Images', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-image'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Bookmarks', $this->plugin->slug ),
			__( 'User Bookmarks', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-bookmark'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Domains', $this->plugin->slug ),
			__( 'User Domains', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-domain'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Layer Types', $this->plugin->slug ),
			__( 'Layer Types', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=cb-default-layer&taxonomy=layer-type'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Layer Ranges', $this->plugin->slug ),
			__( 'Layer Ranges', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=cb-default-layer&taxonomy=layer-range'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'CSS Libraries', $this->plugin->slug ),
			__( 'CSS Libraries', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=cb-default-layer&taxonomy=css-library'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'JS Libraries', $this->plugin->slug ),
			__( 'JS Libraries', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=cb-default-layer&taxonomy=js-library'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Image Types', $this->plugin->slug ),
			__( 'Image Types', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=default-image&taxonomy=image-type'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Account Options', $this->plugin->slug ),
			__( 'Account Options', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=user-plan&taxonomy=account-option'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Subscription Plans', $this->plugin->slug ),
			__( 'Subscription Plans', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=subscription-plan'
		);

		add_submenu_page(
			$this->plugin->slug,
			__( 'Email Models', $this->plugin->slug ),
			__( 'Email Models', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=email-model'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Email Campaigns', $this->plugin->slug ),
			__( 'Email Campaigns', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=email-campaign'
		);
		
		/*
		add_submenu_page(
			$this->plugin->slug,
			__( 'Campaign Triggers', $this->plugin->slug ),
			__( 'Campaign Triggers', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=email-campaign&taxonomy=campaign-trigger'
		);
		*/
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Marketing Channels', $this->plugin->slug ),
			__( 'Marketing Channels', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=user&taxonomy=marketing-channel'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Connected Apps', $this->plugin->slug ),
			__( 'Connected Apps', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?post_type=user-app&taxonomy=app-type'
		);
	}
	
	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets ( $version = '1.0.1' ) {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the cbp-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();
		
    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), $version );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', $this->plugin->slug ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['settings'] = array(
			'title'					=> __( 'General settings', $this->plugin->slug ),
			'description'			=> '',
			'fields'				=> array(

				array(
					'id' 			=> 'host_url',
					'label'			=> __( 'Host Url' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'http://', $this->plugin->slug )
				),			
				array(
					'id' 			=> 'server_url',
					'label'			=> __( 'Server Url' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'http://', $this->plugin->slug )
				),
				array(
					'id' 			=> 'client_key',
					'label'			=> __( 'Client key' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( '', $this->plugin->slug )
				),
				array(
					'id' 			=> 'email_support',
					'label'			=> __( 'Support email' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'support@example.com', $this->plugin->slug )
				),
				array(
					'id' 			=> 'analytics_id',
					'label'			=> __( 'Analytics ID' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'UA-XXXXXXXX-1', $this->plugin->slug )
				),
			)
		);
	
		$settings['urls'] = array(
			'title'					=> __( 'URLS', $this->plugin->slug ),
			'description'			=> __( '', $this->plugin->slug ),
			'fields'				=> array(

				array(
					'id' 			=> 'editorSlug',
					'label'			=> __( 'Editor' , $this->plugin->slug ),
					'description'	=> '[ltple-client-editor]',
					'type'			=> 'slug',
					'callback'		=> 'test',
					'placeholder'	=> __( 'editor', $this->plugin->slug )
				),
				array(
					'id' 			=> 'loginSlug',
					'label'			=> __( 'Login' , $this->plugin->slug ),
					'description'	=> '[ltple-client-login]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'login', $this->plugin->slug )
				),
				array(
					'id' 			=> 'plansSlug',
					'label'			=> __( 'Plans' , $this->plugin->slug ),
					'description'	=> 'no shortcode',
					'type'			=> 'slug',
					'placeholder'	=> __( 'plans', $this->plugin->slug )
				),
				array(
					'id' 			=> 'productSlug',
					'label'			=> __( 'Product' , $this->plugin->slug ),
					'description'	=> '[ltple-client-product]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'product', $this->plugin->slug )
				)
			)
		);
		
		$settings['niche'] = array(
			'title'					=> __( 'Niche', $this->plugin->slug ),
			'description'			=> 'Some information about the targeted market',
			'fields'				=> array(
				array(
					'id' 			=> 'niche_business',
					'label'			=> __( 'Niche business' , $this->plugin->slug ),
					'description'	=> 'Singular term representing the targeted industry',
					'type'			=> 'text',
					'placeholder'	=> __( 'modeling', $this->plugin->slug )
				),	
				array(
					'id' 			=> 'niche_single',
					'label'			=> __( 'Niche name (singular)' , $this->plugin->slug ),
					'description'	=> 'Singular term representing the targeted group of people',
					'type'			=> 'text',
					'default'		=> 'user',
					'placeholder'	=> __( 'user', $this->plugin->slug )
				),					
				array(
					'id' 			=> 'niche_plural',
					'label'			=> __( 'Niche name (plural)' , $this->plugin->slug ),
					'description'	=> 'Plural term representing the targeted group of people',
					'type'			=> 'text',
					'default'		=> 'users',
					'placeholder'	=> __( 'users', $this->plugin->slug )
				),
				array(
					'id' 			=> 'niche_terms',
					'label'			=> __( 'Niche terms' , $this->plugin->slug ),
					'description'	=> 'List of key words separated by line break to describe the niche. This list is used to fetch leads, prospects and contacts across the connected apps.',
					'type'			=> 'textarea',
					'style'			=> 'height:100px;width:250px;',
					'default'		=> '',
					'placeholder'	=> __( 'user...', $this->plugin->slug )
				),
				array(
					'id' 			=> 'niche_hashtags',
					'label'			=> __( 'Niche hashtags' , $this->plugin->slug ),
					'description'	=> 'List of hashtags to be used for automated actions such as auto retweet.',
					'type'			=> 'textarea',
					'style'			=> 'height:100px;width:250px;',
					'default'		=> '',
					'placeholder'	=> __( '#nicheTag...', $this->plugin->slug )
				),
			)
		);	

		$settings['stars'] = array(
			'title'					=> __( 'Stars', $this->plugin->slug ),
			'description'			=> __( 'Amount of stars rewarded', $this->plugin->slug )
		);
		
		foreach( $this->parent->stars->triggers as $group => $trigger ){
			
			foreach($trigger as $key => $data){
				
				$settings['stars']['fields'][] = array(
				
					'id' 			=> $key.'_stars',
					'label'			=> $data['description'],
					'description'	=> '['.$key.']',
					'type'			=> 'number',
					'placeholder'	=> __( 'stars', $this->plugin->slug )
				);				
			}
			 

		}
		
		$settings['marketing'] = array(
			'title'					=> __( 'Marketing', $this->plugin->slug ),
			'description'			=> __( 'Marketing Material & Affiliate settings', $this->plugin->slug ),
			'fields'				=> array(
				array(
					'id' 			=> 'main_video',
					'name' 			=> 'main_video',
					'label'			=> __( 'Main video' , $this->plugin->slug ),
					'description'	=> 'Main youtube video',
					'type'			=> 'text',
					'placeholder'	=> 'http://',
				),			
				array(
					'id' 			=> 'affiliate_banners',
					'name' 			=> 'affiliate_banners',
					'label'			=> __( 'Affiliate banners' , $this->plugin->slug ),
					'description'	=> '',
					'inputs'		=> 'string',
					'type'			=> 'key_value',
					'placeholder'	=> ['key'=>'image title', 'value'=>'url'],
				),
				/*
				array(
					'id' 			=> 'affiliate_text',
					'name' 			=> 'affiliate_text',
					'label'			=> __( 'Affiliate text' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'textarea',
					'placeholder'	=> __( '', $this->plugin->slug )
				)
				*/
			)
		);

		$settings['twitter'] = array(
			'title'					=> __( 'Twitter', $this->plugin->slug ),
			'description'			=> __( 'Twitter API settings', $this->plugin->slug ),
			'fields'				=> array(
				array(
					'id' 			=> 'twt_main_account',
					'label'			=> __( 'Twitter Main Account' , $this->plugin->slug ),
					'description'	=> 'Main connected account',
					'type'			=> 'dropdown_main_apps',
					'app'			=> 'twitter'
				),
				array(
					'id' 			=> 'twt_auto_retweet',
					'label'			=> __( 'Auto Retweet' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'action_schedule',
					'action' 		=> 'retweet',
					'unit' 			=> 'tweets',
					'appId' 		=> ( isset($_POST[$this->parent->_base .'twt_main_account']) ? intval($_POST[$this->parent->_base .'twt_main_account']) : intval(get_option( $this->parent->_base .'twt_main_account' )) ),
					'last' 			=> true,
				),
				/*
				array(
					'id' 			=> 'twt_auto_follow',
					'label'			=> __( 'Auto Follow' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'action_schedule',
					'action' 		=> 'follow',
					'unit' 			=> 'leads',
					'appId' 		=> ( isset($_POST[$this->parent->_base .'twt_main_account']) ? intval($_POST[$this->parent->_base .'twt_main_account']) : intval(get_option( $this->parent->_base .'twt_main_account' )) ),
					'last' 			=> true,
				),
				array(
					'id' 			=> 'twt_auto_unfollow',
					'label'			=> __( 'Auto Unfollow' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'action_schedule',
					'action' 		=> 'unfollow',
					'unit' 			=> 'leads',
					'appId' 		=> ( isset($_POST[$this->parent->_base .'twt_main_account']) ? intval($_POST[$this->parent->_base .'twt_main_account']) : intval(get_option( $this->parent->_base .'twt_main_account' )) ),
					'last' 			=> true,
				),
				*/
				array(
					'id' 			=> 'twt_import_leads',
					'label'			=> __( 'Import Leads' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'action_schedule',
					'action' 		=> 'import leads',
					'last' 			=> false,
				),
				array(
					'id' 			=> 'twt_welcome_tweet',
					'label'			=> __( 'Welcome Tweet' , $this->plugin->slug ),
					'description'	=> 'Message to be tweeted when a new Twitter account is connected',
					'type'			=> 'textarea',
					'placeholder'	=> __( 'Welcome tweet (140 char)', $this->plugin->slug )
				),
				array(
					'id' 			=> 'twt_welcome_dm',
					'label'			=> __( 'Welcome DM' , $this->plugin->slug ),
					'description'	=> 'Direct Message to be sent when a new Twitter account is connected',
					'type'			=> 'textarea',
					'placeholder'	=> __( 'Welcome DM', $this->plugin->slug )
				),
				array(
					'id' 			=> 'twt_thanks_followback_dm',
					'label'			=> __( 'Thanks Followback DM' , $this->plugin->slug ),
					'description'	=> 'Direct Message to be sent when a Twitter account followback',
					'type'			=> 'textarea',
					'placeholder'	=> __( 'Thanks Follow DM', $this->plugin->slug )
				)					
			)
		);
		
		$settings['wordpress'] = array(
			'title'					=> __( 'Wordpress', $this->plugin->slug ),
			'description'			=> __( 'Wordpress API settings', $this->plugin->slug ),
			'fields'				=> array(
				array(
					'id' 			=> 'wpcom_main_account',
					'label'			=> __( 'Wordpress Main Account' , $this->plugin->slug ),
					'description'	=> 'Main connected account',
					'type'			=> 'dropdown_main_apps',
					'app'			=> 'wordpress'
				)				
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->parent->_base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->parent->_base ) );

				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			
			$html .= '<h1>' . __( $this->plugin->title , $this->plugin->slug ) . '</h1>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}
			
			$html .= '<div class="col-xs-9">' . "\n";

				$html .= '<form style="margin:15px;" method="post" action="options.php" enctype="multipart/form-data">' . "\n";

					// Get settings fields
					
					ob_start();
					
					settings_fields( $this->parent->_token . '_settings' );
					
					do_settings_sections( $this->parent->_token . '_settings' );
					
					$html .= ob_get_clean();

					$html .= '<p class="submit">' . "\n";
						$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
						$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , $this->plugin->slug ) ) . '" />' . "\n";
					$html .= '</p>' . "\n";
				$html .= '</form>' . "\n";
				
			$html .= '</div>' . "\n";
			
			$html .= '<div class="col-xs-3">' . "\n";
			
				
			
			$html .= '</div>' . "\n";
			
		$html .= '</div>' . "\n";

		echo $html;
	}
	
	public function set_default_editor() {
		
		$r = 'html';
		return $r;
	}
	
	public function set_admin_edit_page_js(){
		
		echo '  <style type="text/css">
		
					#content-tmce, #content-tmce:hover, #qt_content_fullscreen{
						display:none;
					}
					
				</style>';
				
		echo '	<script type="text/javascript">
		
				jQuery(document).ready(function(){
					jQuery("#content-tmce").attr("onclick", null);
				});
				
				</script>';
	}

	public function schema_TinyMCE_init($in){
		
		/**
		 *   Edit extended_valid_elements as needed. For syntax, see
		 *   http://www.tinymce.com/wiki.php/Configuration:valid_elements
		 *
		 *   NOTE: Adding an element to extended_valid_elements will cause TinyMCE to ignore
		 *   default attributes for that element.
		 *   Eg. a[title] would remove href unless included in new rule: a[title|href]
		 */
		
		if(!isset($in['extended_valid_elements']))
			$in['extended_valid_elements']= '';
		
		if(!empty($in['extended_valid_elements']))
			$in['extended_valid_elements'] .= ',';

		$in['extended_valid_elements'] .= '@[id|class|style|title|itemscope|itemtype|itemprop|datetime|rel],div,dl,ul,ol,dt,dd,li,span,a|rev|charset|href|lang|tabindex|accesskey|type|name|href|target|title|class|onfocus|onblur]';

		return $in;
	}

	
	/**
	 * Main LTPLE_Client_Settings Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Settings instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}

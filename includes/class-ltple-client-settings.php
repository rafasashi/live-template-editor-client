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
	public $options;
	public $addons;
	
	var $tabs = array();
	
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		$this->plugin 			= new stdClass();
		$this->plugin->slug  	= 'live-template-editor-client';
		$this->plugin->title 	= 'Live Template Editor';
		$this->plugin->short 	= 'Live Editor';
		
		// get options
		
		$this->options 				 	= new stdClass();
		$this->options->emailSupport 	= str_replace('@gmail.com','+'.time().'@gmail.com',get_option( $this->parent->_base . 'email_support'));	
		
		$this->options->logo_url = $this->get_default_logo_url();
		
		$this->options->profile_header = $this->get_default_profile_header();

		$this->options->social_icon = $this->get_default_social_icon();
		
		$this->titleBkg = get_option( $this->parent->_base . 'titleBkg', '' );
		
		$this->options->enable_ranking 	= get_option( $this->parent->_base . 'enable_ranking', 'off' );
		
		// get custom style
		
		$this->navbarColor 	= get_option( $this->parent->_base . 'navbarColor', '#182f42' );
		$this->mainColor 	= get_option( $this->parent->_base . 'mainColor', '#506988' );
		$this->linkColor 	= get_option( $this->parent->_base . 'linkColor', '#506988' );	

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_items' ) );	
		
		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
		
		//Add Custom API Endpoints
		
		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-email/v1', '/info', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_email_info'),
			) );			
			
		} );
	}
	
	public function get_default_logo_url() {
		
		if( empty($this->options->logo_url) ){
		
			if( !$this->options->logo_url = get_option( $this->parent->_base . 'homeLogo' )){
				
				$this->options->logo_url = $this->parent->assets_url . 'images/home.png';
			}
		}
		
		return $this->options->logo_url;
	}
	
	public function get_default_profile_header() {
		
		if( empty($this->options->profile_header) ){
		
			if( !$this->options->profile_header = get_option( $this->parent->_base . 'profileHeader' )){
				
				$this->options->profile_header = plugins_url() . '/' . $this->plugin->slug . '/assets/images/profile_header.jpg';
			}
		}
		
		return $this->options->profile_header;
	}
	
	public function get_default_social_icon() {
		
		if( empty($this->options->social_icon) ){
		
			if( !$this->options->social_icon = get_option( $this->parent->_base . 'socialIcon' )){
				
				$this->options->social_icon = $this->parent->assets_url . 'images/social_icon.png';
			}
		}
		
		return $this->options->social_icon;
	}
	
	public function get_email_info( $rest = NULL ) {
		
		$email_info 	= array();
		
		$email_info['name']                 = get_bloginfo("name");
		$email_info['description']          = get_bloginfo("description");
		$email_info['url']                  = $this->parent->urls->profile . '?tab=billing-info';
		$email_info['email_sender']         = get_bloginfo("admin_email");
		$email_info['charset']              = get_bloginfo("charset");
		$email_info['version']              = get_bloginfo("version");
		$email_info['language']             = get_bloginfo("language");
		//$email_info['html_type']            = get_bloginfo("html_type");
		//$email_info['text_direction']       = get_bloginfo("text_direction");
		//$email_info['stylesheet_url']       = get_bloginfo("stylesheet_url");
		//$email_info['stylesheet_directory'] = get_bloginfo("stylesheet_directory");
		//$email_info['template_url']         = get_bloginfo("template_url");
		//$email_info['template_directory']   = get_bloginfo("template_url");
		//$email_info['pingback_url']         = get_bloginfo("pingback_url");
		//$email_info['atom_url']             = get_bloginfo("atom_url");
		//$email_info['rdf_url']              = get_bloginfo("rdf_url");
		//$email_info['rss_url']              = get_bloginfo("rss_url");
		//$email_info['rss2_url']             = get_bloginfo("rss2_url");
		//$email_info['comments_atom_url']    = get_bloginfo("comments_atom_url");
		//$email_info['comments_rss2_url']    = get_bloginfo("comments_rss2_url");
		//$email_info['wpurl']                = get_bloginfo("wpurl");
		//$email_info['siteurl']              = home_url();
		//$email_info['home']                 = home_url();
		
		return $email_info;
	}
	
	public function do_settings_tabs($current){

		$tabs = array();
		
		foreach( $this->tabs[$this->tabIndex] as $slug => $data ){
			
			$tab = !empty($data['tab']) ? $data['tab'] : $data['name'];
						
			if( !empty($data['type']) && $data['type'] == 'taxonomy' ){
				
				$data['url'] = 'edit-tags.php?taxonomy='.$slug . ( !empty($data['post-type']) ? '&post_type='.$data['post-type'] : '' );
			}
			else{
				
				$data['url'] = 'edit.php?post_type='.$slug;
			}
				
			$tabs[$tab][$slug] = $data;
		}
		
		echo '<h2 class="nav-tab-wrapper" style="margin-bottom:10px;">';
			
			$active = '';
			
			foreach( $tabs as $tab => $items ){
				
				$class 	= '';
				$url 	= ''; 
				
				foreach( $items as $slug => $data ){
					
					if( $slug == $current ){
						
						$active = $tab;
						$class 	= 'nav-tab-active';
					}
					
					if( empty($url) || $slug == $current ){
						
						$url = $data['url'];						
					}
				}
				
				echo '<a class="nav-tab '.$class.'" href="'.$url.'">'.$tab.'</a>';
			}
			
		echo '</h2>';
		
		echo'<ul class="subnav-tabs">';
		
		if( $active == 'Templates' ){
			
			// TODO list all template types
			
		}
		elseif( !empty($tabs[$active]) && count($tabs[$active]) > 1 ){
		
			foreach( $tabs[$active] as $slug => $data ){
				
				if( $slug == $current ){
				
					echo'<li class="subnav-li subnav-li-active">';
						
						echo '<a href="' . $data['url'] . '">' . $data['name'] . '</a>';
						
					echo'</li>';
				}
				else{
					
					echo'<li class="subnav-li subnav-li-inactive">';
						
						echo '<a href="' . $data['url'] . '">' . $data['name'] . '</a>';
						
					echo'</li>';
				}
			}
		}
		
		echo'</ul>';
	}
	
	public function post_type_tabs($views) {
		
		$post_type = '';
		
		if( !empty($_GET['post_type']) ){
				
			$post_type = $_GET['post_type'];
		}
		elseif( !empty($views->post_type) ){
			
			$post_type = $views->post_type;
		}
	
		if( !empty($post_type) ){
			
			$this->do_settings_tabs($post_type);
		}
		
		return $views;
	}
	
	public function taxonomy_tabs($tax) {
		
		if( !empty($tax->taxonomy) ){
			
			$taxonomy = $tax->taxonomy;
		}
		else{
			
			$taxonomy = $tax;
		}
		
		if( !empty($taxonomy) ){
			
			$this->do_settings_tabs($taxonomy);
		}
		
		do_action('ltple_taxonomy_action');
	}	
	
	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings(){
		
		$this->settings = $this->settings_fields();
		
		do_action('ltple_plugin_settings');

		$this->schedule_actions();
		
		if( is_admin() ){
			
			add_action( 'load-edit.php', function() {
				
				if( !empty($_GET['post_type']) ){			
					
					foreach($this->tabs as $t => $tabs){
					
						if(isset($tabs[$_GET['post_type']])){
							
							$this->tabIndex = $t;
							
							add_filter( 'views_edit-' . $_GET['post_type'], array( $this, 'post_type_tabs') );						
						}
					}
				}

			});

			add_action( 'load-post.php', function() {
				
				if( !empty($_GET['post']) ){
				
					if( $post = get_post($_GET['post']) ){
					
						foreach($this->tabs as $t => $tabs){
						
							if( isset($tabs[$post->post_type]) ){
								
								$this->tabIndex = $t;
								
								add_filter( 'edit_form_top', array( $this, 'post_type_tabs') );						
								
								break;
							}
						}
					}
				}
			});

			add_action( 'load-edit-tags.php', function() {
				
				if( !empty($_GET['taxonomy']) ){

					foreach($this->tabs as $t => $tabs){

						if(isset($tabs[$_GET['taxonomy']])){
							
							$this->tabIndex = $t;
							
							add_filter( $_GET['taxonomy'].'_pre_add_form', array( $this, 'taxonomy_tabs') );						
						}
					}
				}
			});	
			
			add_action( 'load-term.php', function() {
				
				if( !empty($_GET['taxonomy']) ){

					foreach($this->tabs as $t => $tabs){

						if(isset($tabs[$_GET['taxonomy']])){
							
							$this->tabIndex = $t;
							
							add_filter( $_GET['taxonomy'].'_term_edit_form_top', array( $this, 'taxonomy_tabs') );						
						}
					}
				}
			});	
		}
	}
	
	public function schedule_actions(){
		
		foreach($this->settings as $settings){
			
			if( empty($settings['fields']) ) continue;
			
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
		
		add_menu_page(
		
			$this->plugin->short, 
			$this->plugin->short, 
			'edit_pages', 
			$this->plugin->slug, 
			array($this, 'settings_page'),
			'dashicons-layout',
			2
		);
		
		add_users_page( 
			'All Customers', 
			'All Customers', 
			'edit_pages',
			'users.php?' . $this->parent->_base .'view=customers'
		);
		
		add_users_page( 
			'Newsletter', 
			'Newsletter', 
			'edit_pages',
			'users.php?' . $this->parent->_base .'view=newsletter'
		);
		
		/*
		add_users_page( 
			'All Guests', 
			'All Guests', 
			'edit_pages',
			'users.php?' . $this->parent->_base .'view=guests'
		);		

		add_users_page( 
			'All Leads', 
			'All Leads', 
			'edit_pages',
			'users.php?' . $this->parent->_base .'view=leads'
		);
		*/
		
		if( $this->parent->user->is_admin ){
		
			add_plugins_page( 
				'Live Editor Addons', 
				'Live Editor Addons', 
				'edit_pages',
				'admin.php?page=' . $this->plugin->slug . '&tab=addons'
			);
		}

		add_submenu_page(
			$this->plugin->slug,
			__( 'Default Resources', $this->plugin->slug ),
			__( 'Default Resources', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=cb-default-layer'
		);
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Contents', $this->plugin->slug ),
			__( 'User Contents', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=user-layer'
		);
		
		/*
		add_submenu_page(
			$this->plugin->slug,
			__( 'User Network', $this->plugin->slug ),
			__( 'User Network', $this->plugin->slug ),
			'edit_pages',
			'edit-tags.php?taxonomy=user-contact'
		);
		*/
		
		add_submenu_page(
			$this->plugin->slug,
			__( 'Plan Settings', $this->plugin->slug ),
			__( 'Plan Settings', $this->plugin->slug ),
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
			'live-template-editor-client',
			__( 'Tutorials', $this->plugin->slug ),
			__( 'Tutorials', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=tutorial'
		);		
		
		do_action('ltple_admin_menu');
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
		
		$fields = array();
		
		if( !is_plugin_active( 'live-template-editor-server/live-template-editor-server.php' ) ){

			$fields[] = array(
			
				'id' 			=> 'server_url',
				'label'			=> __( 'Server Url' , $this->plugin->slug ),
				'description'	=> '',
				'type'			=> 'text',
				'default'		=> '',
				'placeholder'	=> __( 'http://', $this->plugin->slug )
			);
			
			$fields[] = array(
			
				'id' 			=> 'client_key',
				'label'			=> __( 'Client key' , $this->plugin->slug ),
				'description'	=> '',
				'type'			=> 'password',
				'default'		=> '',
				'show'			=> true,
				'placeholder'	=> __( '', $this->plugin->slug )
			);		
		}
		
		$fields[] = array(
		
			'id' 			=> 'email_support',
			'label'			=> __( 'Support email' , $this->plugin->slug ),
			'description'	=> '',
			'type'			=> 'text',
			'default'		=> '',
			'placeholder'	=> __( 'support@example.com', $this->plugin->slug )
		);
		
		/*
		$fields[] = array(
		
			'id' 			=> 'post_types',
			'label'			=> __( 'Post Types' , $this->plugin->slug ),
			'description'	=> '',
			'type'			=> 'checkbox_multi',
			'options'		=> array(
			
				'post' 			=> 'Post',
				'page' 			=> 'Page',
			),
		);
		*/
		
		$settings['settings'] = array(
			'title'					=> __( 'General settings', $this->plugin->slug ),
			'description'			=> '',
			'fields'				=> apply_filters('ltple_general_settings',$fields)
		);
	
		$settings['urls'] = array(
			'title'					=> __( 'URLs', $this->plugin->slug ),
			'description'			=> __( '', $this->plugin->slug ),
			'fields'				=> apply_filters('ltple_urls_settings',array(

				array(
					'id' 			=> 'editorSlug',
					'label'			=> __( 'Editor' , $this->plugin->slug ),
					'description'	=> '[ltple-client-editor]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'editor', $this->plugin->slug )
				),
				array(
					'id' 			=> 'mediaSlug',
					'label'			=> __( 'Media' , $this->plugin->slug ),
					'description'	=> '[ltple-client-media]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'editor', $this->plugin->slug )
				),
				array(
					'id' 			=> 'accountSlug',
					'label'			=> __( 'Account' , $this->plugin->slug ),
					'description'	=> '[ltple-client-account]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'editor', $this->plugin->slug )
				),
				array(
					'id' 			=> 'appsSlug',
					'label'			=> __( 'Apps' , $this->plugin->slug ),
					'description'	=> '[ltple-client-apps]',
					'type'			=> 'slug',
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
			))
		);

		$settings['style'] = array(
			'title'					=> __( 'Style', $this->plugin->slug ),
			'description'			=> '',
			'fields'				=> apply_filters('ltple_style_settings',array(
				array(
					'id' 			=> 'homeLogo',
					'label'			=> __( 'Home Logo' , $this->plugin->slug ),
					'description'	=> 'Logo url 100 x 50 recommended',
					'type'			=> 'text',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->logo_url
				),
				array(
					'id' 			=> 'profileHeader',
					'label'			=> __( 'Profile Header' , $this->plugin->slug ),
					'description'	=> 'Header url 1920 x 1080 recommended',
					'type'			=> 'text',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->profile_header
				),
				array(
					'id' 			=> 'socialIcon',
					'label'			=> __( 'Social Icon' , $this->plugin->slug ),
					'description'	=> 'Icon url 120 x 120 recommended',
					'type'			=> 'text',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->social_icon
				),
				array(
					'id' 			=> 'titleBkg',
					'label'			=> __( 'Title Background' , $this->plugin->slug ),
					'description'	=> 'Header url 2560 x 470 recommended',
					'type'			=> 'text',
					'placeholder'	=> 'https://',
					'default'		=> '',
				),
				array(
					'id' 			=> 'navbarColor',
					'label'			=> __( 'Navbar Color' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'placeholder'	=> '#182f42',
					'default'		=> '#182f42',
				),
				array(
					'id' 			=> 'mainColor',
					'label'			=> __( 'Main Color' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'placeholder'	=> '#F86D18',
					'default'		=> '#F86D18',
				),
				array(
					'id' 			=> 'linkColor',
					'label'			=> __( 'Link Color' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'placeholder'	=> '#F86D18',
					'default'		=> '#F86D18',
				),
			))
		);
		
		$settings['plans'] = array(
			'title'					=> __( 'Plans', $this->plugin->slug ),
			'description'			=> 'Default settings for products & plans',
			'fields'				=> apply_filters('ltple_plan_settings',array(				
				array(
					'id' 			=> 'main_image',
					'name' 			=> 'main_image',
					'label'			=> __( 'Cover image' , $this->plugin->slug ),
					'description'	=> 'Main cover image for plans',
					'type'			=> 'text',
					'placeholder'	=> 'https://',
				),
				array(
					'id' 			=> 'main_video',
					'name' 			=> 'main_video',
					'label'			=> __( 'HTML editor video' , $this->plugin->slug ),
					'description'	=> 'HTML editor video',
					'type'			=> 'text',
					'placeholder'	=> 'http://',
				),
			))
		);
		
		$settings['website'] = array(
			'title'					=> __( 'Profile', $this->plugin->slug ),
			'description'			=> 'User profile & website settings ',
			'fields'				=> apply_filters('ltple_profile_settings',array(
				array(
					'id' 			=> 'enable_profile_home_page',
					'label'			=> __( 'Enable Home Page' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'switch',
				),
			))
		);

		$settings['stars'] = array(
			'title'					=> __( 'Ranking', $this->plugin->slug ),
			'description'			=> __( 'Setting up the stars and ranking system', $this->plugin->slug ),
			'fields'				=> apply_filters('ltple_stars_settings',array(
				array(
					'id' 			=> 'enable_ranking',
					'label'			=> __( 'Enable Ranking' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'switch',
				),
			))
		);
		
		// TODO add triggers via ltple_stars_settings filter 
		
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
		
		$settings['tax'] = array(
			'title'					=> __( 'Tax', $this->plugin->slug ),
			'description'			=> __( 'Setting up the tax system', $this->plugin->slug ),
			'fields'				=> apply_filters('ltple_tax_settings',array(
				array(
					'id' 			=> 'enable_taxes',
					'label'			=> __( 'Enable Taxes' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'switch',
				),
				array(
					'id' 			=> 'vat_rate',
					'label'			=> __( 'VAT %' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'number',
					'style'			=> 'width:50px;'
				),
			)),
		);
		
		$settings['importer'] = array(
			'title'					=> __( 'Importer', $this->plugin->slug ),
			'description'			=> __( 'Importer & update remote data', $this->plugin->slug ),
			'fields'				=> apply_filters('ltple_importer_settings',array()),
		);

		$settings['addons'] = array(
			'title'					=> __( 'Addons', $this->plugin->slug ),
			'description'			=> '',
			'class'					=> 'pull-right',
			'fields'				=> apply_filters('ltple_addon_settings',array(
				array(
					'id' 			=> 'addon_plugins',
					'type'			=> 'addon_plugins'
				)				
			)),
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		
		// get tabs
		
		$this->tabs = array (
		
			'default-contents' => array(
			
				'cb-default-layer' 	=> array( 'tab'  => 'Templates','name' => 'Templates'),
				'default-element' 	=> array( 'tab'  => 'HTML',		'name' => 'Elements'),
				'element-library' 	=> array( 'tab'  => 'HTML',		'name' => 'Libraries',	'type' => 'taxonomy', 'post-type' => 'default-element' ),
				'css-library' 		=> array( 'tab'  => 'CSS',		'name' => 'CSS', 		'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'js-library' 		=> array( 'tab'  => 'JS',		'name' => 'JS', 		'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'font-library' 		=> array( 'tab'  => 'Fonts',	'name' => 'Fonts', 		'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'default-image' 	=> array( 'tab'  => 'Images',	'name' => 'Images'),
				'image-type' 		=> array( 'tab'  => 'Images',	'name' => 'Sections', 	'type' => 'taxonomy', 'post-type' => 'default-image' ),	
				'app-type' 			=> array( 'tab'  => 'Apps',		'name' => 'Apps', 		'type' => 'taxonomy', 'post-type' => 'user-app' ),	
			),
			'user-contents' => array(
			  
				'user-layer' 	=> array( 'tab'  => 'HTML', 		'name' => 'HTML'),
				'user-page' 	=> array( 'tab'  => 'Pages', 		'name' => 'Pages'),
				'user-menu' 	=> array( 'tab'  => 'Pages',		'name' => 'Menus'),
				'user-image' 	=> array( 'tab'  => 'Images',		'name' => 'Images'),				
				'user-psd' 		=> array( 'tab'  => 'Images',		'name' => 'PSDs'),
				'user-bookmark' => array( 'tab'  => 'Bookmarks',	'name' => 'Bookmarks'),
				'user-app' 		=> array( 'tab'  => 'Apps',			'name' => 'Applications'),
			),
			'user-network' => array(
			
				'user-contact' 	=> array( 'name' => 'Emails', 'type' => 'taxonomy', 'post-type' => '' ),
			),
			'plan-settings' => array(
				
				'subscription-plan' => array( 'tab'  => 'Plans', 		'name' => 'Plans' ),
				'gallery-section' 	=> array( 'tab'  => 'Galleries',	'name' => 'Sections', 	'type' => 'taxonomy' ),
				'layer-type' 		=> array( 'tab'  => 'Galleries', 	'name' => 'Categories',	'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'layer-range' 		=> array( 'tab'  => 'Galleries', 	'name' => 'Ranges',   	'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'account-option' 	=> array( 'tab'  => 'Options', 		'name' => 'Options',  	'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'addon-service' 	=> array( 'tab'  => 'Options', 		'name' => 'Services', 	'type' => 'taxonomy', 'post-type' => 'subscription-plan' ),	
			),
			'marketing-settings' => array(
			
				'marketing-channel' => array( 'name' => 'Channels', 'type' => 'taxonomy', 'post-type' => 'post' ),
			),
			'email-campaigns' => array(
			
				'email-model' 		=> array( 'name' => 'Models'),
				'email-campaign' 	=> array( 'name' => 'Campaigns'),
				'email-invitation' 	=> array( 'name' => 'Invitations'),
			),
		);
		
		do_action('ltple_admin_tabs'); 
		
		//get addons
	
		$this->addons = array(
			
			/*
			'addon-plugin' 		=> array(
			
				'title' 		=> 'Addon Plugin',
				'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-addon',
				'addon_name' 	=> 'live-template-editor-addon',
				'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-addon/archive/master.zip',
				'description'	=> 'This is a first test of addon plugin for live template editor.',
				'author' 		=> 'Rafasashi',
				'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
			),
			*/
			'affiliate-program' => array(
			
				'title' 		=> 'Affiliate Program',
				'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-affiliate',
				'addon_name' 	=> 'live-template-editor-affiliate',
				'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-affiliate/archive/master.zip',
				'description'	=> 'Affiliate program including click tracking and commissions.',
				'author' 		=> 'Rafasashi',
				'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
			),
			'sponsorship-program' => array(
			
				'title' 		=> 'Sponsorship Program',
				'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-sponsorship',
				'addon_name' 	=> 'live-template-editor-sponsorship',
				'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-sponsorship/archive/master.zip',
				'description'	=> 'Sponsorship program including management and purchase of licenses in bulk.',
				'author' 		=> 'Rafasashi',
				'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
			),
			'directory-plugin' => array(
				
				'title' 		=> 'Directory Plugin',
				'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-directory',
				'addon_name' 	=> 'live-template-editor-directory',
				'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-directory/archive/master.zip',
				'description'	=> 'This is a directory plugin for live template editor.',
				'author' 		=> 'Rafasashi',
				'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
			),
		);
		
		do_action('ltple_admin_addons');
		
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			
			$current_section = '';
			
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				
				$current_section = $_POST['tab'];
			} 
			else {
				
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {
				
				if( empty($data['fields']) ) continue;
				
				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					if(!isset($field['label'])){
						
						$field['label'] = '';
					}
				
					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					// this will save the option in the wp_options table
					
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

					if( empty($data['fields']) ) continue;

					// Set tab class
					
					$class = 'nav-tab';
					
					if( !empty($data['class']) ){
						
						$class .= ' '.$data['class'];
					}
					
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
			
			$html .= '<div class="col-xs-12 col-md-9">' . "\n";

				$html .= '<form style="margin:15px;" method="post" action="options.php" enctype="multipart/form-data">' . "\n";

					// Get settings fields
					
					ob_start();
					
					settings_fields( $this->parent->_token . '_settings' );
					
					//do_settings_sections( $this->parent->_token . '_settings' );

					$this->do_settings_sections( $this->parent->_token . '_settings' );
					
					$html .= ob_get_clean();

					if( !isset($_GET['tab']) || !in_array($_GET['tab'],array('addons','importer')) ){
					
						$html .= '<p class="submit">' . "\n";
							$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
							$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , $this->plugin->slug ) ) . '" />' . "\n";
						$html .= '</p>' . "\n";
					}
					
				$html .= '</form>' . "\n";
				
			$html .= '</div>' . "\n";
			
			$html .= '<div class="col-xs-12 col-md-3">' . "\n";
			
				
			
			$html .= '</div>' . "\n";
			
		$html .= '</div>' . "\n";

		echo $html;
	}
	
	public function do_settings_sections($page) {
		
		global $wp_settings_sections, $wp_settings_fields;

		if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
			return;

		foreach( (array) $wp_settings_sections[$page] as $section ) {
			
			echo '<h3 style="margin-bottom:25px;">' . $section['title'] . '</h3>'.PHP_EOL;
			
			call_user_func($section['callback'], $section);
			
			if ( !isset($wp_settings_fields) ||
				 !isset($wp_settings_fields[$page]) ||
				 !isset($wp_settings_fields[$page][$section['id']]) )
					continue;
					
			echo '<div class="settings-form-wrapper" style="margin-top:25px;">';

				$this->do_settings_fields($page, $section['id']);
			
			echo '</div>';
		}
	}

	public function do_settings_fields($page, $section) {
		
		global $wp_settings_fields;

		if ( !isset($wp_settings_fields) ||
			 !isset($wp_settings_fields[$page]) ||
			 !isset($wp_settings_fields[$page][$section]) )
			return;

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			
			echo '<div class="settings-form-row row">';

				if ( !empty($field['title']) ){
			
					echo '<div class="col-xs-3" style="margin-bottom:15px;">';
					
						if ( !empty($field['args']['label_for']) ){
							
							echo '<label style="font-weight:bold;" for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
						}
						else{
							
							echo '<b>' . $field['title'] . '</b>';		
						}
					
					echo '</div>';
					echo '<div class="col-xs-9" style="margin-bottom:15px;">';
						
						call_user_func($field['callback'], $field['args']);
							
					echo '</div>';
				}
				else{
					
					echo '<div class="col-xs-12" style="margin-bottom:15px;">';
						
						call_user_func($field['callback'], $field['args']);
							
					echo '</div>';					
				}
					
			echo '</div>';
		}
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

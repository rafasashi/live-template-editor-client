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
	var $enabled = array();
		
	public function __construct ( $parent ) {

		$this->parent = $parent;

		// get options
		
		$this->options 				 	= new stdClass();
		$this->options->emailSupport 	= str_replace('@gmail.com','+'.time().'@gmail.com',get_option( $this->parent->_base . 'email_support'));	
		
		$this->options->logo_url = $this->get_default_logo_url();
		
		$this->options->profile_header = $this->get_default_profile_header();

		$this->options->social_icon = $this->get_default_social_icon();
		
		// get custom style
		
		$this->navbarColor 	= get_option( $this->parent->_base . 'navbarColor', '#182f42' );
		$this->mainColor 	= get_option( $this->parent->_base . 'mainColor', '#506988' );
		$this->linkColor 	= get_option( $this->parent->_base . 'linkColor', '#506988' );	
		$this->titleBkg 	= get_option( $this->parent->_base . 'titleBkg', '' );
		
		// Register plugin settings
		
		add_action('init' , array( $this, 'init_tabs' ) );
		
		// Add settings page to menu
		
		add_action('admin_menu' , array( $this, 'add_menu_items' ) );	
        
        add_filter('ltple_admin_tabs_default-contents', function($tabs) {
            
            // prepend tabs
            
            $tabs = array_merge(array(
                
                'cb-default-layer' => array(
                
                    'tab'  => 'Templates', 
                    'name' => 'Templates', 
                    'in_menu' => true 
                ),
            ),$tabs);
            
            // append tabs

            $tabs = array_merge($tabs,array(
            
                'default-image' => array( 
                    'tab'  => 'Images', 
                    'name' => 'Images', 
                    'in_menu' => true 
                ),
                'image-type' => array( 
                    'tab'  => 'Images', 
                    'name' => 'Sections', 
                    'in_menu' => false, 
                    'type' => 'taxonomy', 
                    'post-type' => 'default-image' 
                ),  
                'app-type' => array( 
                    'tab'  => 'APIs', 
                    'name' => 'APIs', 
                    'in_menu' => true, 
                    'type' => 'taxonomy', 
                    'post-type' => 'user-app' 
                ),  
            ));

            return $tabs;

        }, 0, 1);
		
		// Add settings link to plugins page
		
		add_filter('plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
		
		add_filter('ltple_general_settings', array( $this, 'register_general_settings' ),10,1 );

		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		add_filter('init',function(){
			
			$this->settings = $this->get_fields();
		});
	}
	
	public function is_enabled($service){
		
		if( !isset($this->enabled[$service]) ){
		
			$enable = get_option( $this->parent->_base . 'enable_' . $service,false);
	
			$this->enabled[$service] = $enable == 'on' ? true : false;
		}
		
		return $this->enabled[$service];
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
				
				$this->options->profile_header = plugins_url() . '/' . 'live-template-editor-client' . '/assets/images/profile_header.jpg';
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

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {

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
			add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), 'ltple_settings' );

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
				
				$option_name = 'ltple_' . $field['id'];
				register_setting( 'ltple_settings', $option_name, $validation );

				// Add field to page
				
				add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), 'ltple_settings', $section, array( 'field' => $field, 'prefix' => 'ltple_' ) );

			}

			if ( ! $current_section ) break;
		}
	}

	public function settings_section ( $section ) {
		
		if( !empty($this->settings[ $section['id'] ]['description'] ) )
		
			echo '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
	}
	
	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		
		$html = '<div class="wrap" id="' . 'ltple_settings">' . "\n";
			
			$html .= '<h1>' . __( 'SaaS' , 'live-template-editor' ) . '</h1>' . "\n";

			$tab = '';
			
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				
				$tab .= sanitize_title($_GET['tab']);
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
				
				$html .= '<form style="margin:15px;" method="post" action="' . ( $tab == 'data' ? 'admin-post.php' : 'options.php' ) . '" enctype="multipart/form-data">' . "\n";

					// Get settings fields
					
					ob_start();
					
					settings_fields( 'ltple_settings' );
					
					//do_settings_sections( 'ltple_settings' );

					$this->do_settings_sections( 'ltple_settings' );
					
					$html .= ob_get_clean();

					if( empty($tab) || !in_array($tab,array('editors')) ){
					
						$html .= '<p class="submit">' . "\n";
							
							$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
							
							$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'live-template-editor' ) ) . '" />' . "\n";
						
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
    
	public function do_settings_tabs($current){

		$tabs = array();
		
        if( !empty($this->tabIndex) && !empty($this->tabs[$this->tabIndex]) ){
            
            foreach( $this->tabs[$this->tabIndex] as $slug => $data ){
                
                $tab = !empty($data['tab']) ? $data['tab'] : $data['name'];
                            
                if( !empty($data['type']) && $data['type'] == 'taxonomy' ){
                    
                    $data['url'] = 'edit-tags.php?taxonomy='.$slug . ( !empty($data['post-type']) ? '&post_type='.$data['post-type'] : '' );
                }
                else{
                    
                    if( !empty($_GET['author']) ){
                        
                        $data['url'] = 'edit.php?post_type='.$slug.'&author='.intval($_GET['author']);
                    }
                    else{
                    
                        $data['url'] = 'edit.php?post_type='.$slug;
                    }
                }
                    
                $tabs[$tab][$slug] = $data;
            }
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
		elseif( !empty($tabs[$active]) ){
            
            $count = count($tabs[$active]);
            
			foreach( $tabs[$active] as $slug => $data ){
				
                if( $count > 1 || $data['tab'] != $data['name'] ){
                    
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
		}
		
		echo'</ul>';
		
		do_action('ltple_after_settings_tab');
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
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {

		add_menu_page( 
		
			'ltple-settings', 
			'SaaS', 
			'edit_pages', 
			'ltple-settings', 
			NULL, 
			'dashicons-cloud', 
			2 
		);	
		
		add_submenu_page( 
			'ltple-settings', 
			'Settings', 
			'Settings', 
			'edit_pages', 
			'ltple-settings', 
			array($this, 'settings_page')
		);

		// settings

		add_submenu_page(
			'ltple-settings',
			__( 'Apps & Services', 'live-template-editor-client' ),
			__( 'Apps & Services', 'live-template-editor-client' ),
			'edit_pages',
			'edit-tags.php?taxonomy=gallery-section&post_type=cb-default-layer'
		);
		
		add_submenu_page(
			'ltple-settings',
			__( 'Plan & Pricing', 'live-template-editor-client' ),
			__( 'Plan & Pricing', 'live-template-editor-client' ),
			'edit_pages',
			'edit.php?post_type=subscription-plan'
		);

		add_submenu_page(
			'ltple-settings',
			__( 'Tutorials', 'live-template-editor-client' ),
			__( 'Tutorials', 'live-template-editor-client' ),
			'edit_pages',
			'edit.php?post_type=tutorial'
		);

		do_action('ltple_admin_menu');
		
		// storage

		if( !empty($this->tabs['user-contents']) ){

			add_menu_page( 
		
				'ltple-storage', 
				'Storage', 
				'edit_pages', 
				'ltple-storage', 
				array($this, 'storage_page'), 
				'dashicons-database', 
				4
			);
			
			$added = array();
			
			foreach( $this->tabs['user-contents'] as $slug => $tab ){
				
				if( !empty($tab['tab']) && !empty($tab['in_menu']) && !in_array($tab['tab'],$added) ){
					
					if( !empty($tab['type']) && $tab['type'] == 'taxonomy' ){
						
						add_submenu_page(
							'ltple-storage',
							$tab['tab'],
							$tab['tab'],
							'edit_pages',
							'edit-tags.php?taxonomy='.$slug.( !empty($tab['post-type']) ? '&post_type=' . $tab['post-type'] : '' ),
						);						
					}
					else{
						
						add_submenu_page(
							'ltple-storage',
							$tab['tab'],
							$tab['tab'],
							'edit_pages',
							'edit.php?post_type='.$slug
						);
					}
					
					$added[] = $tab['tab'];
				}
			}
		}
		
		// users
		
		add_users_page( 
			'All Customers', 
			'All Customers', 
			'edit_pages',
			'users.php?' . $this->parent->_base .'view=customers'
		);
	}
	
	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'live-template-editor-client' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	public function register_general_settings ($fields) {
		
		$fields[] = array(
		
			'id' 			=> 'email_support',
			'label'			=> __( 'Support email' , 'live-template-editor-client' ),
			'type'			=> 'text',
			'placeholder'	=> __( 'support@example.com', 'live-template-editor' )
		);
		
		$fields[] = array(
		
			'id' 			=> 'phone_support',
			'label'			=> __( 'Support phone' , 'live-template-editor' ),
			'type'			=> 'text',
			'placeholder'	=> __( '+1 23456789', 'live-template-editor-client' ),
			'description'	=> 'including country code',
		);
		
		return $fields;
	}
		

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */

	public function get_fields(){

		$settings = array(
			
			'settings' => array(
			
				'title'			=> __( 'Settings', 'live-template-editor' ),
				'fields'		=> apply_filters('ltple_general_settings',array( array(
				
					'id' 			=> 'client_key',
					'label'			=> __( 'Client key' , 'live-template-editor' ),
					'type'			=> 'password',
					'show'			=> true
				)))
			)
		);

		
		$settings['urls'] = array(
			'title'					=> __( 'URLs', 'live-template-editor-client' ),
			'description'			=> __( '', 'live-template-editor-client' ),
			'fields'				=> apply_filters('ltple_urls_settings',array(
				array(
					'id' 			=> 'accountSlug',
					'label'			=> __( 'Account Settings' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-account]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'account', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'profileSlug',
					'label'			=> __( 'Profile Settings' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-profile]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'profile', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'loginSlug',
					'label'			=> __( 'Login Page' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-login]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'login', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'editorSlug',
					'label'			=> __( 'Template Gallery' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-editor]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'editor', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'mediaSlug',
					'label'			=> __( 'Media Library' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-media]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'media', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'appsSlug',
					'label'			=> __( 'Connected Apps' , 'live-template-editor-client' ),
					'description'	=> '[ltple-client-apps]',
					'type'			=> 'slug',
					'placeholder'	=> __( 'apps', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'plansSlug',
					'label'			=> __( 'Plans' , 'live-template-editor-client' ),
					'description'	=> 'no shortcode',
					'type'			=> 'slug',
					'placeholder'	=> __( 'plans', 'live-template-editor-client' )
				),
				array(
					'id' 			=> 'productSlug',
					'label'			=> __( 'Product' , 'live-template-editor-client' ),
					'description'	=> '',
					'type'			=> 'slug',
					'placeholder'	=> __( 'product', 'live-template-editor-client' )
				)
			))
		);
		
		$settings['media'] = array(
			'title'					=> __( 'Media', 'live-template-editor-client' ),
			'description'			=> 'User media library settings',
			'fields'				=> apply_filters('ltple_media_settings',array(				
				array(
					'id' 			=> 'enable_image_editor',
					'label'			=> __( 'Image editor' , 'live-template-editor-client' ),
					'description'	=> 'Enable image editor in media library',
					'type'			=> 'switch',
				),
			))
		);
        
		$settings['style'] = array(
			'title'					=> __( 'Style', 'live-template-editor-client' ),
			'description'			=> '',
			'fields'				=> apply_filters('ltple_style_settings',array(
				array(
					'id' 			=> 'homeLogo',
					'label'			=> __( 'Home Logo' , 'live-template-editor-client' ),
					'description'	=> 'Logo url 100 x 50 recommended',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->logo_url
				),
				array(
					'id' 			=> 'profileHeader',
					'label'			=> __( 'Profile Header' , 'live-template-editor-client' ),
					'description'	=> 'Header url 1920 x 1080 recommended',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->profile_header
				),
				array(
					'id' 			=> 'socialIcon',
					'label'			=> __( 'Social Icon' , 'live-template-editor-client' ),
					'description'	=> 'Icon url 120 x 120 recommended',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
					'default'		=> $this->options->social_icon
				),
				array(
					'id' 			=> 'titleBkg',
					'label'			=> __( 'Title Background' , 'live-template-editor-client' ),
					'description'	=> 'Header url 2560 x 470 recommended',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
					'default'		=> '',
				),
				array(
					'id' 			=> 'navbarColor',
					'label'			=> __( 'Navbar Color' , 'live-template-editor-client' ),
					'description'	=> '',
					'type'			=> 'color',
					'placeholder'	=> '#182f42',
					'default'		=> '#182f42',
				),
				array(
					'id' 			=> 'mainColor',
					'label'			=> __( 'Main Color' , 'live-template-editor-client' ),
					'description'	=> '',
					'type'			=> 'color',
					'placeholder'	=> '#F86D18',
					'default'		=> '#F86D18',
				),
				array(
					'id' 			=> 'linkColor',
					'label'			=> __( 'Link Color' , 'live-template-editor-client' ),
					'description'	=> '',
					'type'			=> 'color',
					'placeholder'	=> '#F86D18',
					'default'		=> '#F86D18',
				),
			))
		);
		
		$settings['plans'] = array(
			'title'					=> __( 'Plans', 'live-template-editor-client' ),
			'description'			=> 'Default settings for plans',
			'fields'				=> apply_filters('ltple_plan_settings',array(				
				array(
					'id' 			=> 'main_image',
					'name' 			=> 'main_image',
					'label'			=> __( 'Cover image' , 'live-template-editor-client' ),
					'description'	=> 'Main cover image for plans',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
				),
			))
		);
		
		$settings['metrics'] = array(
			'title'					=> __( 'Metrics', 'live-template-editor-client' ),
			'description'			=> 'Default settings for metrics',
			'fields'				=> apply_filters('ltple_plan_settings',array(				
				array(
					'id' 			=> 'enable_bandwidth',
					'label'			=> __( 'Bandwidth' , 'live-template-editor-client' ),
					'description'	=> 'Enable bandwidth tracking',
					'type'			=> 'switch',
				),
			))
		);
		
		$settings['templates'] = array(
		
			'title'					=> __( 'Templates', 'live-template-editor-client' ),
			'description'			=> 'Default template settings',
			'fields'				=> apply_filters('ltple_templates_settings',array(
				array(
					'id' 			=> 'default_range_id',
					'label'			=> __( 'Default gallery range' , 'live-template-editor-client' ),
					'description'	=> 'Default range of the template gallery',
					'type'			=> 'dropdown_categories',
					'taxonomy'		=> 'layer-range',
				),
			))
		);
		
		$settings['videos'] = array(
		
			'title'					=> __( 'Videos', 'live-template-editor-client' ),
			'description'			=> 'Default explainer videos',
			'fields'				=> apply_filters('ltple_videos_settings',array(				
				array(
					'id' 			=> 'main_video',
					'name' 			=> 'main_video',
					'label'			=> __( 'HTML editor' , 'live-template-editor-client' ),
					'description'	=> 'HTML editor video',
					'type'			=> 'url',
					'placeholder'	=> 'https://',
				),
				
			))
		);

		$settings['stars'] = array(
			'title'					=> __( 'Ranking', 'live-template-editor-client' ),
			'description'			=> __( 'Setting up the stars and ranking system', 'live-template-editor-client' ),
			'fields'				=> apply_filters('ltple_stars_settings',array(
				array(
					'id' 			=> 'enable_ranking',
					'label'			=> __( 'Enable Ranking' , 'live-template-editor-client' ),
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
					'placeholder'	=> __( 'stars', 'live-template-editor-client' )
				);				
			}
		}

		if( !empty($this->parent->user->user_email) && $this->parent->user->user_email == MASTER_ADMIN_EMAIL ){
			
			$settings['data'] = array(
				'title'					=> __( 'Data', 'live-template-editor-client' ),
				'description'			=> __( 'Import, export & update remote data', 'live-template-editor-client' ),
				'fields'				=> apply_filters('ltple_data_settings',array(
				
					array(
						'id' 			=> 'data[import]',
						'label'			=> __( 'Import' , 'live-template-editor-client' ),
						'placeholder'	=> 'https://',
						'type'			=> 'url',
						'description'	=> '.../api/ltple-export/v1/post_type/{post_type}/{key} <br> (generate resource local urls after importing)',
					),
					array(
						'id' 			=> 'data[key]',
						'placeholder'	=> 'decryption key',
						'type'			=> 'text',
						'description'	=> 'key used in the export url',
					),
					array(
						'id'	=> 'import',
						'type'	=> 'submit',
						'data'	=> 'Import',
					),
				)),
			);
		}
		
		return apply_filters('ltple_settings_fields',$settings);
	}

	public function addons_fields () {
		
		return apply_filters('ltple_addons_fields',array(
			
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
		));
	}
	
	public function register_addons($addons){
	
		$addons['affiliate-program'] = array(
			
			'title' 		=> 'Affiliate Program',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-affiliate',
			'addon_name' 	=> 'live-template-editor-affiliate',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-affiliate/archive/master.zip',
			'description'	=> 'Affiliate program including click tracking and commissions.',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);
		
		$addons['sponsorship-program'] = array(
			
			'title' 		=> 'Sponsorship Program',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-sponsorship',
			'addon_name' 	=> 'live-template-editor-sponsorship',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-sponsorship/archive/master.zip',
			'description'	=> 'Sponsorship program including management and purchase of licenses in bulk.',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);
		
		$addons['directory-plugin'] = array(
				
			'title' 		=> 'Directory Plugin',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-directory',
			'addon_name' 	=> 'live-template-editor-directory',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-directory/archive/master.zip',
			'description'	=> 'This is a directory plugin for live template editor.',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);	
		
		return $addons;
	}
	
	/**
	 * Register plugin settings
	 * @return void
	 */
	public function init_tabs () {
		
		// get tabs
		
		$this->tabs = array (

			'user-contents' => apply_filters('ltple_admin_tabs_user-contents',array(
					  
				'user-layer' 	=> array( 'tab'  => 'HTML', 	'name' => 'HTML', 	'in_menu' => true),
				'user-psd' 		=> array( 'tab'  => 'Images',	'name' => 'Bitmaps','in_menu' => true),
				'user-image' 	=> array( 'tab'  => 'Images',	'name' => 'External','in_menu' => false), // TODO migrate to media lib
				'user-bookmark' => array( 'tab'  => 'Links',	'name' => 'Links', 	'in_menu' => true),
				'user-app' 		=> array( 'tab'  => 'Apps',		'name' => 'Apps', 	'in_menu' => true),
			)),
			'user-network' => apply_filters('ltple_admin_tabs_user-network',array(
			
				'user-contact' 	=> array( 'name' => 'Emails', 'type' => 'taxonomy', 'post-type' => '' ),
			)),
			'gallery-settings' => apply_filters('ltple_admin_tabs_gallery-settings',array(
				
				'gallery-section' 	=> array( 'tab'  => 'Sections', 	'name' => 'Sections', 	'type' => 'taxonomy', 	'post-type' => 'cb-default-layer' ),
				'layer-type' 		=> array( 'tab'  => 'Services', 	'name' => 'Services',	'type' => 'taxonomy', 	'post-type' => 'cb-default-layer' ),
				'layer-range' 		=> array( 'tab'  => 'Ranges', 		'name' => 'Ranges',   	'type' => 'taxonomy', 	'post-type' => 'cb-default-layer' ),	
			)),
			'plan-settings' => apply_filters('ltple_admin_tabs_plan-settings',array(
				
				'subscription-plan' => array( 'tab'  => 'Plans', 	'name' => 'Plans' ),	
				'account-option' 	=> array( 'tab'  => 'Options', 	'name' => 'Options',  	'type' => 'taxonomy', 'post-type' => 'cb-default-layer' ),
				'addon-service' 	=> array( 'tab'  => 'Options', 	'name' => 'Services', 	'type' => 'taxonomy', 'post-type' => 'subscription-plan' ),
			)),
			'marketing-settings' => apply_filters('ltple_admin_tabs_marketing-settings',array(
			
				'marketing-channel' => array( 'name' => 'Channels', 'type' => 'taxonomy', 'post-type' => 'post' ),
			)),
		);
		
		do_action('ltple_admin_tabs');
		
		add_action( 'load-edit.php', function() {
			
			if( !empty($_GET['post_type']) ){			
				
				$post_type = sanitize_title($_GET['post_type']);
				
				foreach($this->tabs as $t => $tabs){
				
					if( isset($tabs[$post_type]) ){
						
						$this->tabIndex = $t;
						
						add_filter( 'views_edit-' . $post_type, array( $this, 'post_type_tabs') );						
					}
				}
			}

		});

		add_action( 'load-post.php', function() {
			
			if( !empty($_GET['post']) && is_numeric($_GET['post']) ){
				
				$post_id = intval($_GET['post']);
				
				if( $post = get_post($post_id) ){
					
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
				
				$post_type = !empty($_GET['post_type']) ? sanitize_title($_GET['post_type']) : '';
				
				$taxonomy = !empty($_GET['taxonomy']) ? sanitize_title($_GET['taxonomy']) : '';

				foreach($this->tabs as $t => $tabs){
	
					if( isset($tabs[$taxonomy]) ){
						
						if( !empty($taxonomy) && in_array($taxonomy,array(
							
							'gallery-section',
							'layer-type',
							'layer-range',
						
						))){
							
							$this->tabIndex = 'gallery-settings';
						}
						elseif( $post_type == 'cb-default-layer' ){
						
							$this->tabIndex =  'default-contents';
						}
						else{
							
							$this->tabIndex =  $t;
						}
						
						add_filter( $taxonomy.'_pre_add_form', array( $this, 'taxonomy_tabs') );
					}
				}
			}
		});	
		
		add_action( 'load-term.php', function() {
			
			if( !empty($_GET['taxonomy']) ){
                
                $taxonomy = sanitize_title($_GET['taxonomy']);
                
				foreach($this->tabs as $t => $tabs){

					if(isset($tabs[$taxonomy])){
						
						$this->tabIndex = $t;
						
						add_filter( $_GET['taxonomy'].'_term_edit_form_top', array( $this, 'taxonomy_tabs') );						
					}
				}
			}
		});
	}
	
	public function storage_page () {

		// Build page HTML
		
		echo '<div class="wrap" id="' . $this->parent->_token . '_storage">' . "\n";
			
			echo '<h1>' . __( 'Storage' , 'live-template-editor-client' ) . '</h1>' . "\n";
		
			echo '<div id="dashboard-widgets-wrap">';
				
				echo '<div id="dashboard-widgets" class="metabox-holder">';
					
					echo '<div class="postbox-container">';
					echo '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
						
						echo '<div id="dashboard_right_now" class="postbox ">';
							
							echo '<div class="inside">';
							
								echo '<div class="main">';
								
									echo '<ul>';
										
										$added = array();
										
										foreach( $this->tabs['user-contents'] as $slug => $tab ){
											
											if( !empty($tab['tab']) && !empty($tab['in_menu']) && !in_array($tab['tab'],$added) ){
												
												if( !empty($tab['type']) && $tab['type'] == 'taxonomy' ){
													
													$path = 'edit-tags.php?taxonomy='.$slug.( !empty($tab['post-type']) ? '&post_type=' . $tab['post-type'] : '' );						
												}
												else{
													
													$path = 'edit.php?post_type='.$slug;
												}
												
												echo '<li><a href="'.get_admin_url(null,$path).'">' . $tab['tab'] . '</a></li>';
											
												$added[] = $tab['tab'];
											}
										}
										
									echo '</ul>';

								echo '</div>';
							
							echo '</div>';
						
						echo '</div>';
						
					echo '</div>';
					echo '</div>';
					
				echo '</div>';

			echo '</div>' . "\n";
			
		echo '</div>' . "\n";
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

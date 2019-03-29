<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Profile {

	private $parent;
	
	var $id = 0;
	var $slug;
	var $tabs = null;
	var $user = null;
	var $privacySettings = null;
	var $socialAccounts = null;
	var $notificationSettings = null;
	var $pictures;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		add_filter('ltple_loaded', array( $this, 'init_profile' ));
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('pr',$query_vars)){
				
				$query_vars[] = 'pr';
			}
			
			if(!in_array('tab',$query_vars)){
			
				$query_vars[] = 'tab';
			}
			
			return $query_vars;
		}, 1);
		
		add_filter('template_redirect', array( $this, 'get_profile_parameters' ));
		
		add_shortcode('ltple-client-profile', array( $this , 'get_profile_shortcode' ) );
	}
	
	public function get_profile_parameters(){
		
		// get displayed user id
		
		$this->id = intval(get_query_var('pr'));
		
		// get displayed user data
		
		if( $this->id > 0 ){
		
			$this->user = get_user_by( 'ID', $this->id );

			if( !$this->tab = get_query_var('tab') ){
				
				$this->tab = 'about-me';
			}	
			
			add_filter('ltple_header_title', array($this,'get_profile_title'),10,1);
			
			add_filter('the_seo_framework_title_from_custom_field', array($this,'get_profile_title'),10);
			
			add_filter('ltple_header_canonical_url', array($this,'get_profile_url'),10);
			
			add_filter('the_seo_framework_rel_canonical_output', '__return_empty_string');
			
			add_filter('get_canonical_url', array($this,'get_profile_url'),10);
		}
		elseif( !is_admin() && $this->parent->user->loggedin ){
				
			$this->pictures	= $this->get_profile_picture_fields();
		}
	}
	
	public function get_profile_title(){
		
		$this->parent->title = ucfirst($this->user->nickname) . "'s " . $this->parent->title;
		
		if( $this->tab != 'about-me' ){
			
			$tabs = $this->get_profile_tabs();
			
			foreach( $tabs as $tab ){
				
				if( $tab['slug'] == $this->tab){
					
					$this->parent->title .= ' - ' . $tab['name'];
					break;
				}
			}
		}
		
		return $this->parent->title;
	}
	
	public function get_profile_url(){
		
		$this->parent->canonical_url = $this->parent->urls->profile . $this->id . '/';
	
		return $this->parent->canonical_url;
	}
	
	public function get_profile_shortcode(){
		
		include($this->parent->views . '/navbar.php');

		if( $this->id > 0 ){

			include($this->parent->views . '/profile.php');
		}
		elseif( $this->parent->user->loggedin ){
			
			include($this->parent->views . '/settings.php');
		}
	}
	
	public function is_public(){
		
		$is_public = false;
		
		if( $this->user ){
			
			$last_seen = intval( get_user_meta( $this->user->ID, $this->parent->_base . '_last_seen',true) );
			
			if( $last_seen > 0 ){
				
				$aboutMe = get_user_meta( $this->user->ID, $this->parent->_base . 'policy_about-me',true );

				if( $aboutMe != 'off' ){
					
					$is_public = true;
				}
			}
		}
		
		return $is_public;
	}
	
	public function handle_update_profile(){
			
		if(!empty($_POST['settings'])){
			
			if( $_POST['settings'] == 'general-info' ){
				
				// save general information
				
				foreach( $this->fields as $field ){
					
					$id = $field['id'];
					
					if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
						
						$content = wp_kses_post($_POST[$id]);
						
						wp_update_user( array( 'ID' => $this->parent->user->ID, $id => $content ) );
							
						$this->parent->user->{$id} = $content;
					}
				}
				
				// save pictures
				
				foreach( $this->pictures as $field){
					
					$id = $field['id'];
					
					if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
						
						$content = wp_kses_post($_POST[$id]);

						update_user_meta( $this->parent->user->ID, $id, $content );
					
						if( $id == $this->parent->_base . 'profile_picture' ){
							
							// refresh image
							
							$this->parent->image->parse_avatar_url($content,$this->parent->user->ID,true);
						}
					}
				}
			}
			elseif( $_POST['settings'] == 'privacy-settings' ){
				
				// save privacy settings

				foreach( $this->privacySettings as $field){
					
					$id = $field['id'];
					
					$content = ( !empty($_POST[$id]) ? wp_kses_post($_POST[$id]) : 'off' );

					update_user_meta( $this->parent->user->ID, $id, $content );
				}
			}
			elseif( $_POST['settings'] == 'social-accounts' ){
				
				// save privacy settings

				foreach( $this->socialAccounts as $label => $fields){
					
					foreach( $fields as $field ){
					
						$id = $field['id'];
					
						$content = ( !empty($_POST[$id]) ? wp_kses_post($_POST[$id]) : 'off' );

						update_user_meta( $this->parent->user->ID, $id, $content );
					}
				}
			}
			elseif( $_POST['settings'] == 'email-notifications' && !empty($this->parent->user->notify) ){
				
				// save notification settings			
				
				$notify = $this->parent->user->notify;
				
				foreach( $notify as $key => $value ){
				
					if( !empty($_POST[$this->parent->_base . 'notify'][$key]) && $_POST[$this->parent->_base . 'notify'][$key] == 'on' ){
						
						$notify[$key] = 'true';
						
						$this->notificationSettings[$key]['data'] = 'on';
					}
					else{
						
						$notify[$key] = 'false';
						
						$this->notificationSettings[$key]['data'] = 'off';
					}
				}
				
				update_user_meta($this->parent->user->ID, $this->parent->_base . '_can_spam', $notify['series']);
					
				update_user_meta($this->parent->user->ID, $this->parent->_base . 'notify', $notify);					
			
				$this->parent->user->notify = $notify;
			}
			
			do_action('ltple_update_profile');
		}
	}
	
	public function get_profile_picture_fields( $user_id = 0, $userApps = array() ){
	 
		if( $user_id == 0) {
			
			$user_id = $this->parent->user->ID;
			
			$userApps = $this->parent->user->apps;
		}
		
		$pictures 	= array();
		
		//get gravatar picture
		
		$image 			= get_avatar_url( $user_id );
		$pictures[] 	= add_query_arg('_',time(),$image);
		
		// get connected twitter pictures
		
		if( !empty($userApps) ){
		
			foreach( $userApps as $i => $userApp ){
				
				$key = 'twitter-';
				
				if( strpos( $userApp->post_name, $key ) === 0 ){
					
					$name 		= str_replace($key,'',$userApp->post_name);
					$pictures[] = 'https://twitter.com/'.$name.'/profile_image?size=original&_'.time();
				}
			}
		}

		// get local picture

		$pictures[] = $this->parent->image->get_local_avatar_url( $user_id );
		
		$fields['profile_picture'] = array(

			'id' 			=> $this->parent->_base . 'profile_picture',
			'label'			=> 'Avatar',
			'description'	=> 'Upload or select an avatar from <a class="label label-default" target="_blank" href="https://en.gravatar.com/">Gravatar</a> <a class="label label-info" href="'.$this->parent->apps->getAppUrl('twitter','connect').'">Twitter</a>',
			'type'			=> 'avatar',
			'options'		=> $pictures,
		);
		
		$fields['profile_banner'] = array(

			'id' 			=> $this->parent->_base . 'profile_banner',
			'label'			=> 'Header',
			'description'	=> 'Upload a header picture 1920 x 1080 pixels recommended',
			'type'			=> 'banner',
			'default'		=> $this->parent->image->get_banner_url( $user_id ) . '?' . time(),
		);
		
		return $fields;
	}
	
	public function get_profile_tabs(){

		if( is_null($this->tabs) ){
		
			$this->tabs = [];
			
			// about me
			
			$this->tabs['about-me']['position'] = 1;
		
			$this->tabs['about-me']['name'] = 'About Me';
			
			$this->tabs['about-me']['content'] = '<table class="form-table">';
				
				$this->fields = $this->get_general_fields();
				
				foreach( $this->fields as $field ){
					
					$this->tabs['about-me']['content'] .= '<tr>';
					
						$this->tabs['about-me']['content'] .= '<th style="width:200px;><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
						
						$this->tabs['about-me']['content'] .= '<td>';
						
							if( isset($this->user->{$field['id']}) ){
								
								$meta = $this->user->{$field['id']};
							}
							else{
								
								$meta = get_user_meta( $this->user->ID , $field['id'] );
							}
							
							if(!empty($meta)){
							
								if(	$field['id'] == 'user_url'){
										
									$this->tabs['about-me']['content'] .=  '<a target="_blank" href="'.$meta.'">'.$meta.' <span style="font-size:11px;" class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>';
								}
								else{
									
									$this->tabs['about-me']['content'] .=  '<p>';
									
										$this->tabs['about-me']['content'] .=  str_replace(PHP_EOL,'</p><p>',strip_tags($meta));
										
									$this->tabs['about-me']['content'] .=  '</p>';
								}
							}
							else{
								
								$this->tabs['about-me']['content'] .=  '';
							}
						
						$this->tabs['about-me']['content'] .= '</td>';
						
					$this->tabs['about-me']['content'] .= '</tr>';
				}
				
			$this->tabs['about-me']['content'] .= '</table>';
			
			// add addon tabs
			
			do_action('ltple_profile_tabs');
			
			// sort tabs
			
			usort($this->tabs, function($a, $b) {
				
				return $a['position'] - $b['position'];
			});
			
			// parse tabs
			
			foreach( $this->tabs as $i => $tab ){
				
				$this->tabs[$i]['slug'] = sanitize_title($tab['name']);
			}
		}
		
		return $this->tabs;
	}		
	
	public function init_profile(){
		
		$this->slug = get_option( $this->parent->_base . 'profileSlug' );

		// add rewrite rules

		add_rewrite_rule(
		
			$this->slug . '/([0-9]+)/?$',
			'index.php?pagename=' . $this->slug . '&pr=$matches[1]',
			'top'
		);
		
		add_rewrite_rule(
		
			$this->slug . '/([0-9]+)/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&pr=$matches[1]&tab=$matches[2]',
			'top'
		);
		
		if( !is_admin() ){
			
			if( $this->parent->user->loggedin ){
			
				// get profile fields
				
				if( !empty($_GET['tab']) && $_GET['tab'] == 'privacy-settings' ){
					
					$this->set_privacy_fields();
				}
				elseif( !empty($_GET['tab']) && $_GET['tab'] == 'social-accounts' ){
					
					$this->set_social_fields();
				}
				elseif( !empty($_GET['tab']) && $_GET['tab'] == 'email-notifications' ){
					
					$this->set_notification_fields();
				}
				else{
					
					$this->pictures	= $this->get_profile_picture_fields();
					
					$this->fields = $this->get_general_fields();
				}
				
				// update profile fields
			
				$this->handle_update_profile();
			}
		}
	}
	
	public function get_general_fields( $fields=[] ){
		
		/*
		$fields['user_login'] = array(

			'id' 			=> 'user_login',
			'label'			=> 'Username',
			'description'	=> '',
			'placeholder'	=> 'Username',
			'type'			=> 'text',
			'disabled'		=> true
		);
		*/
		
		$fields['nickname'] = array(

			'id' 			=> 'nickname',
			'label'			=> 'Nickname',
			'description'	=> '',
			'placeholder'	=> 'Nickname',
			'type'			=> 'text',
			'required'		=> true
		);
		
		$fields['description'] = array(

			'id' 			=> 'description',
			'label'			=> 'About me',
			'description'	=> '',
			'placeholder'	=> '',
			'type'			=> 'textarea'
		);
		
		$fields['user_url'] = array(
		
			'id' 			=> 'user_url',
			'label'			=> 'Web Site',
			'description'	=> '',
			'placeholder'	=> 'http://',
			'type'			=> 'url'			
		);
		
		return $fields;
	}
	
	public function set_social_fields(){
		
		if( is_null($this->socialAccounts) ){
			
			if ( $apps = $this->parent->apps->getUserApps($this->parent->user->ID) ){
				
				foreach( $apps as $app ){
					
					if( !empty( $app->user_profile ) ){
						
						$this->socialAccounts[$app->app_name][$app->ID] = array(

							'id' 			=> $this->parent->_base . 'app_profile_' . $app->ID,
							'label'			=> ucfirst($app->user_name),
							'description'	=> 'Add <a target="_blank" href="' . $app->user_profile . '">' . ucfirst($app->user_name) . ' <span class="fa fa-external-link" style="font-weight:bold;font-size:10px;"></span></a> social icon in My Profile',
							'type'			=> 'switch',
							'default'		=> 'on',
						);						
					}
				}
			}
			
			do_action('ltple_social_accounts');
		}
		
		return $this->privacySettings;
	}	
	
	public function set_privacy_fields(){
		
		if( is_null($this->privacySettings) ){
			
			$this->privacySettings['about-me'] = array(

				'id' 			=> $this->parent->_base . 'policy_about-me',
				'label'			=> 'My Profile',
				'description'	=> 'Anyone can see My Profile page',
				'type'			=> 'switch',
				'default'		=> 'on',
			);
			
			do_action('ltple_privacy_settings');
		}
		
		return $this->privacySettings;
	}
	
	public function set_notification_fields(){
		
		if( is_null($this->notificationSettings) ){
			
			if( !empty($this->parent->user->notify) ){
			
				$descriptions = $this->parent->email->get_notification_settings('description');
				
				foreach( $this->parent->user->notify as $key => $value ){
					
					$this->notificationSettings[$key] = array(

						'id' 			=> $this->parent->_base . 'notify['.$key.']',
						'label'			=> ucfirst($key),
						'description'	=> $descriptions[$key],
						'type'			=> 'switch',
						'data'			=> ( $value == 'true' ? 'on' : 'off' ),
					);				
				}
			}
		}
		
		return $this->notificationSettings;
	}
	
	/**
	 * Main LTPLE_Client_Profile Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Profile is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Profile instance
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

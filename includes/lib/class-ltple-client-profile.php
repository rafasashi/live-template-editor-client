<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Profile {

	private $parent;
	
	var $id = 0;
	var $tab 		= null;
	var $slug 		= null;
	var $tabSlug 	= null;
	var $url 		= null;
	var $tabs 		= null;
	var $user 		= null;
	
	var $privacySettings = null;
	var $socialAccounts = null;
	var $notificationSettings = null;
	var $pictures;
	
	var $profile_css 		= null;
	var $background_image 	= '';
	var $is_public			= false;
	var $self_profile		= false;
	var $is_pro				= false;
	var $is_editable 		= false;
	
	var $completeness = array();
	
	var $post = null;
	
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
			
			if(!in_array('slug',$query_vars)){
			
				$query_vars[] = 'slug';
			}
			
			return $query_vars;
		}, 1);
		
		add_filter('rew_cache_bail_page', array( $this , 'bail_profile_cache' ) );
				
		add_filter('template_redirect', array( $this, 'get_profile_parameters' ),1);
		
		add_shortcode('ltple-client-profile', array( $this , 'get_profile_shortcode' ) );

		add_action( 'ltple_view_my_profile_settings', function(){
			
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->dashboard .'"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> Dashboard</a>';

			echo'</li>';					
					
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->profile .'"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Profile Settings</a>';

			echo'</li>';			
		},1);
		
		add_action( 'ltple_view_my_profile', function(){
			
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->profile .'?tab=billing-info"><span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span> Billing Info</a>';

			echo'</li>';
			
			if( !empty( $this->parent->apps->list ) ){
			
				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. $this->parent->urls->dashboard .'?list=user-app"><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Connected Apps</a>';

				echo'</li>';
			}
			
		},1);
	}
	
	public function get_profile_parameters(){
		
		// get displayed user id
		
		$this->id = apply_filters('ltple_profile_id',intval(get_query_var('pr')));

		// displayed user data
		
		if( $this->id > 0 ){
			
			// profile user
			
			$this->user = get_user_by( 'ID', $this->id );
			
			$this->user->period_end = $this->parent->plan->get_license_period_end( $this->user->ID);
			
			$this->user->remaining_days = $this->parent->plan->get_license_remaining_days( $this->user->period_end );
			
			// profile tab
			
			$this->tab 		= apply_filters('ltple_profile_tab',get_query_var('tab','about-me'));
			
			$this->tabSlug 	= apply_filters('ltple_profile_slug',get_query_var('slug',''));
			
			// profile url
			
			$this->url = $this->get_profile_url();
			
			do_action('ltple_profile_redirect');
			
			add_filter('ltple_header_canonical_url', array($this,'get_profile_url'),10);

			add_filter('get_canonical_url', array($this,'get_profile_url'),10);			
			
			// profile title
			
			add_filter('ltple_header_title', array($this,'get_profile_title'),10,1);

			// disable the_seo_framework
			
			if ( function_exists( 'the_seo_framework' ) ) {
			
				remove_action( 'wp_head', array(the_seo_framework(),'html_output'), 1 );
			}
			
			$this->is_public = $this->is_public();

			$this->self_profile = ( $this->parent->user->loggedin && !empty($this->user) && $this->user->ID  == $this->parent->user->ID ? true : false );
			
			if( $this->is_public || $this->self_profile ){			
				
				// set response code
				
				global $wp_query;
				
				status_header(200);
					
				$wp_query->is_404 = false;
				
				// get background
			
				$this->background_image = $this->parent->image->get_banner_url($this->user->ID) . '?' . time();
				
				// is user pro 
			
				$this->is_pro = $this->parent->users->is_pro_user($this->user->ID);
				
				// is profile editable
				
				$this->is_editable = ( $this->parent->user->loggedin && $this->parent->user->ID == $this->user->ID ? true : false );
				
				// get tabs
				
				$this->tabs = $this->get_profile_tabs();				
				
				// get apps
				
				$this->apps = $this->parent->apps->getUserApps($this->user->ID);

				// get profile picture
				
				$this->picture = $this->parent->image->get_avatar_url( $this->user->ID );
				
				// enqueue inline style
				
				add_action( 'wp_enqueue_scripts',function(){

					wp_register_style( $this->parent->_token . '-profile', false, array());
					wp_enqueue_style( $this->parent->_token . '-profile' );

					wp_add_inline_style( $this->parent->_token . '-profile', '
			
						@import url("https://fonts.googleapis.com/css?family=Pacifico");
					
						.profile-heading {
							
							height:350px;
							background-color: #333;
							background-image: url("' . $this->background_image . '");
							background-position: center center;
							background-size: cover;
							background-attachment: '. ( $this->tab == 'about-me' ? 'scroll' : 'scroll' ).';
							background-repeat: no-repeat;
							border-bottom:5px solid ' . $this->parent->settings->mainColor . ';
							position:relative;
							overflow:hidden;
						}		
					
						.profile-overlay {
							
							width:100%;
							height:350px;
							position:absolute;
							background-image: linear-gradient(to bottom right,#284d6b,' . $this->parent->settings->mainColor . ');
							opacity:'. ( $this->tab == 'about-me' ? '.5' : '.7' ).';
						}
						
						.profile-heading h1, .profile-heading h2 {
							
							padding-top:'.( $this->is_editable ? '80px' : '125px').';
							color: #fff !important;
							font-weight: normal;
							font-size: 53px;
							font-family: "Pacifico", cursive;
							position: relative;
							text-shadow: 0px 0px 8px rgba(0, 0, 0, .4);
							box-shadow: none !important;
							background: none !important;
						}
							
						.profile-avatar img {

							border: solid 7px #f9f9f9;
							border-radius: 100px;
							margin:0px;
							position: relative;
							background:#fff;
							box-shadow:6px -10px 6px -7px rgba(0, 0, 0, 0.27), -7px -10px 6px -7px rgba(0, 0, 0, 0.27);
						}
								
						.profile-menu {
							
							padding: 90px 15px 0 15px;
						}

						.profile-menu ul {
							
							font-size: 16px;
						}	
						
						#social_icons img {
						
							background:#fff;
							border:1px solid #eee;
							padding:1px;
							height:30px;
							width:30px;
							border-radius:250px;
						}
						
					');
					
					if( !empty($this->profile_css) ){
						
						wp_register_style( $this->parent->_token . '-profile-css', false, array());
						wp_enqueue_style( $this->parent->_token . '-profile-css' );
					
						wp_add_inline_style( $this->parent->_token . '-profile-css', $this->profile_css );							
					}
					
				},10 );	
			}
		}
		elseif( !is_admin() && $this->parent->user->loggedin ){
				
			$this->pictures	= $this->get_profile_picture_fields();
		}
	}
	
	public function bail_profile_cache($bail){
		
		if( !$bail && $this->id > 0 ){
			
			return array('ltple','profile');
		}
		
		return $bail;
	}
	
	public function get_profile_post(){
		
		if( is_null($this->post) ){
			
			global $post;
			
			if( $this->id > 0 ){
				
				// get post
				
				$post = get_page_by_path( 'profile', OBJECT, 'page' );

				$this->post = apply_filters('ltple_post',$post);
				
				// get post content
				
				if( $this->post->post_type == 'page' ){
					
					$post->post_content = apply_filters('ltple_profile_page_description',$this->get_profile_description());
				}
				elseif( $this->parent->layer->is_hosted($this->post->post_type) ){
				
					$this->post->post_content = $this->parent->layer->get_layer_description($this->post->ID);
				}
				
				// get post excerpt
				
				$this->post->post_excerpt = $this->post->post_content;
			}
		}
		
		return $this->post;
	}
	
	public function get_profile_description($description = ''){
		
		$meta = get_user_meta($this->id);
		
		if( !empty($meta['description'][0]) ){
			
			$description = strip_tags($meta['description'][0]);
		}

		return $description;
	}
	
	public function get_profile_title($title=''){
		
		if( !empty($this->user) ){
		
			$title = ucfirst($this->user->nickname) . "'s profile";
		}
		
		if( $this->tab != 'about-me' ){
			
			$tabs = $this->get_profile_tabs();
			
			foreach( $tabs as $tab ){
				
				if( $tab['slug'] == $this->tab){
					
					$title .= ' - ' . $tab['name'];
					break;
				}
			}
		}
		
		return $title;
	}
	
	public function get_profile_url(){
		
		$this->parent->canonical_url = apply_filters( 'ltple_profile_url', $this->parent->urls->profile . $this->id );
	
		return $this->parent->canonical_url;
	}
	
	public function get_profile_shortcode(){
		
		if( $this->id > 0 ){
						
			include($this->parent->views . '/profile.php');
		}
		elseif( $this->parent->user->loggedin ){
			
			include($this->parent->views . '/navbar.php');
			
			include($this->parent->views . '/settings.php');
		}
	}
	
	public function get_profile_completeness($user_id){
		
		if( !isset($this->completeness[$user_id]) ){
			
			$completeness = array();
			
			// get user info
			
			if( $user = get_userdata($user_id) ){

				if( $user_meta = get_user_meta($user_id) ){
					
					$completeness = array(
						
						'nickname' => array(
								
							'name' 		=> 'Nickname',
							'complete' 	=> false,
							'points' 	=> 2,
						),
						'header_image' => array(
								
							'name' 		=> 'Header Image',
							'complete' 	=> false,
							'points' 	=> 2,
						),						
						'user_url' => array(
						
							'name' 		=> 'External URL',
							'complete' 	=> false,
							'points' 	=> 1,
						),
						'description' => array(
							
							'name' 		=> 'Description',
							'complete' 	=> false,
							'points' 	=> 2,
						),							
						$this->parent->_base . 'profile_html' => array(
								
							'name' 		=> 'Home Page',
							'complete' 	=> false,
							'points' 	=> 3,
						),
					);
												
					// get url
					
					if( !empty($user->user_url) ){
						
						$completeness['user_url']['complete'] = true;
					}
					
					// get header image
					
					$banner_url = $this->parent->image->get_banner_url($user->ID);
					
					if( $banner_url != $this->parent->settings->get_default_profile_header() ){
						
						$completeness['header_image']['complete'] = true;
					}
					
					// get meta
					
					foreach( $user_meta as $slug => $meta ){

						if( $slug == 'nickname' ){
												
							if( !empty($meta[0]) ){
								
								$completeness[$slug]['complete'] = true;
							}
						}				
						elseif( $slug == 'description' ){
							
							if( !empty($meta[0]) && strlen($meta[0]) > 10 ){
								
								$completeness[$slug]['complete'] = true;
							}
						}
						elseif( $slug == $this->parent->_base . 'profile_html' ){
												
							if( !empty($meta[0]) && strlen($meta[0]) > 10 ){
								
								$completeness[$slug]['complete'] = true;
							}
						}
					}
					
					$completeness = apply_filters('ltple_profile_completeness',$completeness,$user,$user_meta);
				}
			}

			$this->completeness[$user_id] = $completeness;
		}
		
		return $this->completeness[$user_id];
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
					
					$field_id = $field['id'];
					
					if( isset($_POST[$field_id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$field_id])) ) ){
						
						$content = wp_kses_post($_POST[$field_id]);
						
						if( in_array( $field_id, array( $this->parent->_base . 'profile_html', $this->parent->_base . 'profile_css' )) ){

							update_user_meta($this->parent->user->ID,$field_id,$content);
						}
						else{
						
							wp_update_user( array( 
								
								'ID' 		=> $this->parent->user->ID, 
								$field_id 	=> $content,
							));
						}
						
						$this->parent->user->{$field_id} = $content;
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
		
		/*
		if( !empty($userApps) ){
		
			foreach( $userApps as $i => $userApp ){
				
				$key = 'twitter-';
				
				if( strpos( $userApp->post_name, $key ) === 0 ){
					
					$name 		= str_replace($key,'',$userApp->post_name);
					$pictures[] = 'https://twitter.com/'.$name.'/profile_image?size=original&_'.time();
				}
			}
		}
		*/

		// get local picture

		$pictures[] = $this->parent->image->get_local_avatar_url( $user_id );
		
		$fields['profile_picture'] = array(

			'id' 			=> $this->parent->_base . 'profile_picture',
			'label'			=> 'Avatar',
			'description'	=> '',
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
		
			$tabs = [];

			// about me
			
			$tabs['about-me']['position'] = 1;
		
			$tabs['about-me']['name'] = 'About Me';
			
			$tabs['about-me']['content'] = '';
			
			if( $profile_html = $this->user->remaining_days > 0 ? get_user_meta( $this->user->ID , $this->parent->_base . 'profile_html', true ) : '' ){

				$this->profile_css = get_user_meta( $this->user->ID , $this->parent->_base . 'profile_css', true );
				
				if( !empty($this->profile_css) ){
					
					/*
					add_filter('ltple_document_classes',function($classes){
						
						$classes .= ' layer-'  . $this->user->ID;
						
						return $classes;
					});
					*/
					
					$this->profile_css = $this->parent->layer->parse_css_content($this->profile_css, '.layer-' . $this->user->ID);
				}
				
				add_action( 'wp_enqueue_scripts',function(){

					wp_register_style( $this->parent->_token . '-about-me', false, array());
					wp_enqueue_style( $this->parent->_token . '-about-me' );
				
					wp_add_inline_style( $this->parent->_token . '-about-me', '
					
						html {
							scroll-behavior: smooth !important;
						}

						#about-me {

							margin:0px !important;
							display:block !important;
							width:auto !important;
						}

						#about-me ul, #about-me li {
							
							list-style:none !important;
						}

						#about-me .layer-' . $this->user->ID . ' > *:first-child {

							position: initial !important;
							display: block !important;						
							top: 0 !important;
							bottom: 0 !important;
							left: 0 !important;
							right: 0 !important;
							clear: both !important;
							margin:0 !important;
							border:none !important;
							box-shadow:none !important;
						}

					');
					
					if( !empty($this->profile_css) ){
						
						wp_register_style( $this->parent->_token . '-profile-css', false, array());
						wp_enqueue_style( $this->parent->_token . '-profile-css' );
					
						wp_add_inline_style( $this->parent->_token . '-profile-css', $this->profile_css );							
					}
					
				},10 );
				
				$tabs['about-me']['content'] .= '<div class="layer-' . $this->user->ID . '">' . $profile_html . '</div>';
			}
			else{
			
				add_action( 'wp_enqueue_scripts',function(){

					wp_register_style( $this->parent->_token . '-about-me', false, array());
					wp_enqueue_style( $this->parent->_token . '-about-me' );
				
					wp_add_inline_style( $this->parent->_token . '-about-me', '

						#about-me {
							
							margin-top:15px !important;
						}
						
					');

				},10 );			
			
				$this->fields = $this->get_general_fields();

				$tabs['about-me']['content'] .= '<table class="form-table" style="margin:0 15px;display:inline-block;">';
				
					foreach( $this->fields as $field ){
						
						if( !empty($field['id']) && !in_array($field['id'],array( $this->parent->_base . 'profile_html', $this->parent->_base . 'profile_css')) ){
						
							$tabs['about-me']['content'] .= '<tr>';
							
								$tabs['about-me']['content'] .= '<th style="width:200px;><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
								
								$tabs['about-me']['content'] .= '<td>';
									
									if( isset($this->user->{$field['id']}) ){
										
										$meta = $this->user->{$field['id']};
									}
									else{
										
										$meta = get_user_meta( $this->user->ID , $field['id'] );
									}

									if(!empty($meta)){
									
										if(	$field['id'] == 'user_url'){
												
											$tabs['about-me']['content'] .=  '<a target="_blank" href="'.$meta.'">'.$meta.' <span style="font-size:11px;" class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>';
										}
										else{
											
											$tabs['about-me']['content'] .=  '<p>';
											
												$tabs['about-me']['content'] .=  str_replace(PHP_EOL,'</p><p>',strip_tags($meta));
												
											$tabs['about-me']['content'] .=  '</p>';
										}
									}
									else{
										
										$tabs['about-me']['content'] .=  '';
									}
								
								$tabs['about-me']['content'] .= '</td>';
								
							$tabs['about-me']['content'] .= '</tr>';
						}
					}
				
				$tabs['about-me']['content'] .= '</table>';
			}
			
			// add addon tabs
			
			$tabs = apply_filters('ltple_profile_tabs',$tabs);

			// sort tabs
			
			usort($tabs, function($a, $b) {
				
				return $a['position'] - $b['position'];
			});
			
			// parse tabs
			
			foreach( $tabs as $i => $tab ){
				
				$tabs[$i]['slug'] = sanitize_title($tab['name']);
			}
			 
			$this->tabs = $tabs;
		}
		
		return $this->tabs;
	}

	public function get_slug(){
		
		if( is_null( $this->slug ) ){
			
			$slug = get_option( $this->parent->_base . 'profileSlug' );
		
			if( empty( $slug ) ){
				
				$post_id = wp_insert_post( array(
				
					'post_title' 		=> 'Profile',
					'post_type'     	=> 'page',
					'comment_status' 	=> 'closed',
					'ping_status' 		=> 'closed',
					'post_content' 		=> '[ltple-client-profile]',
					'post_status' 		=> 'publish',
					'menu_order' 		=> 0
				));
				
				$slug = update_option( $this->parent->_base . 'profileSlug', get_post($post_id)->post_name );
			}
			
			$this->slug = $slug;
		}
		
		return $this->slug;
	}
	
	public function init_profile(){
		
		// get profile url
		
		$this->slug = $this->get_slug();
		
		$this->parent->urls->profile = $this->parent->urls->home . '/' . $this->slug . '/';
		
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
		
		add_rewrite_rule(
		
			$this->slug . '/([0-9]+)/([^/]+)/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&pr=$matches[1]&tab=$matches[2]&slug=$matches[3]',
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
			'description'	=> 'Your public name',
			'placeholder'	=> 'Nickname',
			'type'			=> 'text',
			'required'		=> true
		);
		
		$fields['user_url'] = array(
		
			'id' 			=> 'user_url',
			'label'			=> 'External URL',
			'description'	=> 'SEO optimized backlink (dofollow)',
			'placeholder'	=> 'https://',
			'type'			=> 'text'			
		);
		
		$fields['description'] = array(

			'id' 			=> 'description',
			'label'			=> 'Description',
			'description'	=> 'Brief text description of yourself',
			'placeholder'	=> '',
			'type'			=> 'textarea',
			'style'			=> 'height:80px;',
		);
		
		if( $this->parent->user->remaining_days > 0 ){
			
			$fields[$this->parent->_base . 'profile_html'] = array(

				'id' 			=> $this->parent->_base . 'profile_html',
				'label'			=> 'Home Page (HTML)',
				'description'	=> 'Customize your profile home page with HTML',
				'placeholder'	=> '',
				'type'			=> 'textarea',
				'disabled'		=> false,
				
			);
			
			$fields[$this->parent->_base . 'profile_css'] = array(

				'id' 			=> $this->parent->_base . 'profile_css',
				'label'			=> 'Home Page (CSS)',
				'description'	=> 'Customize your profile home page with CSS',
				'placeholder'	=> '',
				'type'			=> 'textarea',
				'disabled'		=> false,
			);			
		}
		else{
			
			$fields[$this->parent->_base . 'profile_html'] = array(

				'id' 			=> '',
				'label'			=> 'Home Page (HTML)',
				'description'	=> 'Customize your profile home page with HTML',
				'placeholder'	=> 'For paid license only',
				'type'			=> 'textarea',
				'disabled'		=> true,
				'data'			=> '',
				
			);
			
			$fields[$this->parent->_base . 'profile_css'] = array(

				'id' 			=> '',
				'label'			=> 'Home Page (CSS)',
				'description'	=> 'Customize your profile home page with CSS',
				'placeholder'	=> 'For paid license only',
				'type'			=> 'textarea',
				'disabled'		=> true,
				'data'			=> '',
			);			
		}

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
							'description'	=> 'Add <a target="_blank" href="' . $app->user_profile . '">' . ucfirst($app->user_name) . ' <span class="fa fa-external-link-alt" style="font-weight:bold;font-size:10px;"></span></a> social icon in My Profile',
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

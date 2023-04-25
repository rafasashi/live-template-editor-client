<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Profile {

	private $parent;
	
	var $id = 0;
	var $tab 		= null;
	var $slug 		= null;
	var $tabSlug 	= null;
	var $url 		= null;
	var $tabs 		= array();
	var $user 		= null;
	var $name		= null;
	
	var $urls 		= array();

	var $privacySettings 	= null;
	var $socialAccounts 	= null;
	var $pictures;
	
	var $profile_css 		= null;
	var $background_image 	= '';
	
	var $is_public		= null;
	var $is_unclaimed	= null;
	var $is_self		= null;
		
	var $in_tab 		= false;
	
	var $is_pro			= false;
	var $is_editable 	= false;

	var $completeness 	= array();
	
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
				
		add_filter('template_redirect', array( $this, 'get_current_parameters' ),1);

		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-menu/v1', '/profile', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_profile_menu'),
				'permission_callback' => '__return_true',
			) );
			
		} );

		add_shortcode('ltple-client-profile', array( $this , 'get_profile_shortcode' ) );
		
		add_action( 'template_include', array( $this , 'include_user_profile' ));
			
		add_action( 'show_user_profile', array( $this, 'show_privacy_settings' ),20,1 );
		add_action( 'edit_user_profile', array( $this, 'show_privacy_settings' ),20,1 );
		
		add_action( 'personal_options_update', array( $this, 'save_privacy_settings' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_privacy_settings' ) );
		 
		add_action( 'ltple_view_my_profile_settings', function(){
			
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->dashboard .'"><span class="fa fa-th-large" aria-hidden="true"></span> Dashboard</a>';

			echo'</li>';					

			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->profile .'"><span class="fa fa-cog" aria-hidden="true"></span> Profile Settings</a>';

			echo'</li>';			
		},1);
		
		add_action( 'ltple_view_my_profile', function(){
	
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->account .'"><span class="fa fa-key" aria-hidden="true"></span> Account Settings</a>';

			echo'</li>';

			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->account .'?tab=billing-info"><span class="fa fa-credit-card" aria-hidden="true"></span> Billing Info</a>';

			echo'</li>';
			
			if( !empty( $this->parent->apps->list ) ){
			
				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. $this->parent->urls->dashboard .'?list=user-app"><span class="fa fa-exchange-alt" aria-hidden="true"></span> Connected Apps</a>';

				echo'</li>';
			}
			
		},1);
	}
	
	public function get_current_parameters(){
		
		$user_id = apply_filters('ltple_profile_id',intval(get_query_var('pr')));
		
		if( $user_id > 0 ){

			$tab  = apply_filters('ltple_profile_tab',get_query_var('tab','home'));
			
			$slug = apply_filters('ltple_profile_slug',get_query_var('slug',''));
			
			$this->set_profile($user_id,$tab,$slug,true);
		}
		elseif( !is_admin() && $this->parent->user->loggedin ){
				
			$this->pictures	= $this->get_profile_picture_fields();
		}
		
		do_action('ltple_profile_loaded');
	}
	
	public function set_profile($user_id=0,$tab='',$slug='',$redirect=true){
		
		// displayed user data
		
		if( $user_id > 0 ){
			
			$this->id = $user_id;
			
			// profile user
			
			$this->user = get_user_by('id',$user_id);
			
			// profile name
					
			$this->name = $this->get_profile_name($this->id);
		
			if( empty($this->user->ID) )
				
				include( $this->parent->views . '/profile/restricted.php' );
			
			$this->user->period_end = $this->parent->plan->get_license_period_end( $this->user->ID);
			
			$this->user->remaining_days = $this->parent->plan->get_license_remaining_days( $this->user->period_end );
			
			$this->tab 		= $tab;
			
			$this->tabSlug 	= $slug;
			
			$this->url 		= $this->get_canonical_url();
			
			if( $redirect === true ){
			
				do_action('ltple_profile_redirect');
			}
			
			add_filter('ltple_header_canonical_url', array($this,'get_canonical_url'),10);

			add_filter('get_canonical_url', array($this,'get_canonical_url'),10);			
			
			// profile title
			
			add_filter('ltple_header_title', array($this,'get_profile_title'),10,1);

			// disable the_seo_framework
			
			if ( function_exists( 'the_seo_framework' ) ) {
			
				remove_action( 'wp_head', array(the_seo_framework(),'html_output'), 1 );
			}
			
			if( $this->is_public() || $this->is_self() ){			
				
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
					
				// in tab
				
				$this->in_tab = isset($this->tabs[$this->tab]) ? true : false;
				
				if( $this->in_tab === true ){
					
					do_action('ltple_profile_disclaimer');
				}
				
				if( $this->tab == 'home' && empty($this->tabs['home']['content']) ){
					
					include $this->parent->views . '/profile/card.php';
				}
				else{

					// get apps
					
					$this->apps = $this->parent->apps->getUserApps($this->user->ID);

					// get profile picture
					
					$this->picture = $this->parent->image->get_avatar_url( $this->user->ID );
					
					// get css framework
					
					add_filter('ltple_css_framework',function($framework){
			
						return apply_filters('ltple_profile_css_framework',$framework,$this->tab,$this->tabSlug);
						
					},99999999,1);			

					// enqueue inline style
					
					add_action( 'wp_enqueue_scripts',function(){

						wp_register_style( $this->parent->_token . '-profile', false, array());
						wp_enqueue_style( $this->parent->_token . '-profile' );

						wp_add_inline_style( $this->parent->_token . '-profile', $this->get_profile_style());
						
						if( !empty($this->profile_css) ){
							
							wp_register_style( $this->parent->_token . '-profile_css', false, array());
							wp_enqueue_style( $this->parent->_token . '-profile_css' );
						
							wp_add_inline_style( $this->parent->_token . '-profile_css', $this->profile_css );							
						}
						
						// enqueue inline script
						
						if( !$this->parent->inWidget ){
						
							wp_register_script( $this->parent->_token . '-profile_menu', '', array( 'jquery' ) );
							wp_enqueue_script( $this->parent->_token . '-profile_menu' );
					
							wp_add_inline_script( $this->parent->_token . '-profile_menu', $this->get_profile_script());					
						}
						
					},10 );
				}
			}
			else{
					
				include( $this->parent->views . '/profile/restricted.php' );
			}
		}
	}
	
	public function get_profile_menu( $rest = NULL ){
		
		$referer = $rest->get_header('referer');
		
		if( empty($referer) )
			
			die('unknown referer');

		// get content
		
		ob_start();
		
		echo'<div id="navbar-features" class="pull-left" style="padding:12px 0;">';	
		
			do_action('ltple_menu_buttons');	
		
		echo'</div>';
		
		// avatar
		
		do_action('ltple_avatar_menu');

		$picture = add_query_arg('_',time(),$this->parent->image->get_avatar_url( $this->parent->user->ID ));
		
		echo'<button style="margin:0 5px 0 0;padding:0;float:right;background:transparent;border:none;width:50px;height:50px;display:inline-block;" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img style="padding:10px;" class="img-circle" src="'.$picture.'" height="50" width="50" /></button>';
		
		// account settings
		
		echo'<ul id="avatar-menu" class="dropdown-menu dropdown-menu-right">';
			
			if( $this->parent->user->ID > 0 ){

				echo'<li style="position:relative;display:table;width:100%;background:#112331;box-shadow: inset 0 0 10px #060c10;">';
					
					echo'<div style="float:left;width:30%;padding:12px;">';
					
						echo'<img style="border: 2px solid #3b4954;" class="img-circle" src="'.$picture.'">';
					
					echo'</div>';
					
					echo'<div style="width:70%;float:left;line-height:20px;padding:14px 6px 2px 6px;">';
							
						echo'<div style="color:#eee;font-weight:bold;max-width:100%;overflow:hidden;">';
						
							echo $this->parent->user->nickname;

						echo'</div>';
						
						echo'<a href="'. $this->parent->urls->primary . '/profile/' . $this->parent->user->ID . '/" style="display:block;width:100%;">';
							
							echo'<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ';
							
							echo'View Profile';
						
						echo'</a>';

					echo'</div>';
					
				echo'</li>';	
					
				do_action('ltple_view_my_profile_settings',$referer);														
					
				do_action('ltple_view_my_profile',$referer);
				
				echo'<li style="position:relative;background:#182f42;">';
					
					$redirect_to = $this->parent->profile->id > 0 ? $this->parent->urls->current : $this->parent->urls->editor;
					
					echo '<a href="'. wp_logout_url( $redirect_to ) .'"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a>';

				echo'</li>';					
			}
			else{
				
				$login_url = home_url('/login/');

				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. esc_url( $login_url ) .'"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Login</a>';

				echo'</li>';
				
				echo'<li style="position:relative;background:#182f42;">';
					
					echo '<a href="'. esc_url( add_query_arg('action','register',$login_url) ) .'"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Register</a>';

				echo'</li>';
			}
			
		echo'</ul>';
		
		$html = ob_get_clean();
		
		// set CORS
		
		header('Access-Control-Allow-Origin: ' . $referer);
		header('Access-Control-Allow-Credentials: true');
	
		return array( 
		
			'html' => $html,
		);
	}
	
	public function get_profile_style(){
		
		$style = '
		
		.profile-heading {
			
			height:350px;
			background-color: #333;
			background-image: url("' . $this->background_image . '");
			background-position: center center;
			background-size: cover;
			background-attachment: '. ( $this->tab == 'home' ? 'scroll' : 'scroll' ).';
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
			opacity:'. ( $this->tab == 'home' ? '.5' : '.7' ).';
		}
		
		.profile-heading h1, .profile-heading h2 {
			
			padding-top:'.( $this->is_editable ? '80px' : '125px').';
			color: #fff !important;
			font-weight: normal;
			font-size: 53px;
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
		
		#profile_nav {
			
			border:none;
			box-shadow:0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);
			overflow:visible;
			margin:0;
			background:#182f42;
			height: 41px;
		}
		
		#profile_nav > li+li  {
			
			margin-left:0;
			border-left: 1px solid #132533;
		}
		
		#profile_nav > li {
			
			position: relative;
			display: block;
			line-height: 25px;
		}
		
		#profile_nav > li > a {
			
			padding:8px 15px !important;
			color:#fff;
			display:inline-block;
			height:41px;
			font-family: "Open Sans", Helvetica;
			text-transform: uppercase;
			font-size: 12px;
		}
		
		#profile_nav > li > a:hover {
			
			color:' . $this->parent->settings->mainColor . ';
		}
		
		#profile_nav > .active > a:hover {
			
			color:#fff;
		}
		
		.mobile-bar a {
			
			font-size:20px;
			height:35px;
			width:35px;
			text-align: center;
			z-index: 200;
			position: fixed;
			background:#fff;
			border-radius:25px;
			box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
		}
		
		.mobile-bar i {
			
			padding: 3px!important;
			margin: 5px!important;
		}
		
		.mobile-bar #whatsapp {

			bottom: 125px;
			right: 20px;
		}

		.mobile-bar #to_top {

			display: none;
			bottom: 80px;
			right: 20px;
		}
		';
		
		return $style;
	}
	
	public function get_profile_script(){
		
		$script = '
		
			;(function($){

				$(document).ready(function(){
					
					var scroll_timer;
					
					var displayed 	= false;
					var $message 	= $("#to_top");
					var $window 	= $(window);
					var top 		= $(document.body).children(0).position().top;
				 
					$("#to_top").on("click",function(e) {
						
						e.preventDefault();
						
						$("html, body").animate({scrollTop : 0},"slow");
					});
					
					/* react to scroll event on window */
					
					$window.on("scroll",function () {
						
						window.clearTimeout(scroll_timer);
						
						scroll_timer = window.setTimeout(function () { // use a timer for performance
							
							if($window.scrollTop()<=top){
								
								displayed = false;
								$message.fadeOut(500);
							}
							else if(displayed == false){
								
								displayed = true;
								$message.stop(true, true).show().on("click",function () { $message.fadeOut(500); });
							}
						}, 100);
					});

					if( $("#profile_menu").length > 0 ){

						$.ajax({
							
							type		: "GET",
							dataType	: "json",
							url  		: "' . $this->parent->urls->api . 'ltple-menu/v1/profile/",
							xhrFields	: {
								
								withCredentials: true
							},
							success: function(data) {
								
								$("#profile_menu").append(data.html);
							}
						});
					}
				});
					
			})(jQuery);			
		';
		
		return $script;
	}
	
	public function bail_profile_cache($bail){
		
		if( !$bail && $this->id > 0 ){
			
			return array('ltple','profile');
		}
		
		return $bail;
	}
	
	public function get_profile_post(){
		
		if( is_null($this->post) ){
			
			if( $this->id > 0 ){
				
				// get post
				
				global $post;
				
				if( is_null($post) || $post->post_type != 'cb-default-layer' ){
					
					$post = get_page_by_path('profile',OBJECT,'page');
				}
				
				if( $this->post = apply_filters('ltple_post',$post) ){
					
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
	
	public function get_profile_name($user_id){
		
		return ucfirst(get_user_meta( $user_id , 'nickname', true ));
	}
	
	public function get_profile_title($title=''){

		if( $this->tab != 'home' ){
			
			$tabs = $this->get_profile_tabs();
			
			if( $this->tab == 'about' ){
				
				$title = $tabs['about']['name'] . ' ' . $this->name;
			}
			else{
				
				foreach( $tabs as $tab ){
					
					if( $tab['slug'] == $this->tab){

						$title = apply_filters('ltple_profile_' . $tab['slug'] . '_title',$tab['name'],$this->id,$this->name);
				
						break;
					}
				}
			}
		}
		
		return $title;
	}
	
	public function get_canonical_url(){
		
		$this->parent->canonical_url = $this->get_user_url($this->id);
			
		return $this->parent->canonical_url;
	}
	
	public function get_user_url($user_id,$path=''){
		
		if( !isset($this->urls[$user_id]) ){
		
			$profile_url = apply_filters( 'ltple_profile_url', $this->parent->urls->profile . $user_id );
			
			if( defined('REW_DEV_ENV') && REW_DEV_ENV === true && strpos($profile_url,REW_SERVER) === false ){
				
				// set dev url
				
				$url = parse_url($profile_url);

				$profile_url = $url['scheme'] . '://' . str_replace('.','--',untrailingslashit($url['host'])) . '.' . REW_SERVER;
			}
			
			$this->urls[$user_id] = $profile_url;
		}
		
		return $this->urls[$user_id] . $path;
	}
	
	public function show_privacy_settings( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			echo '<div style="margin:10px auto;min-height:45px;">';
				
				echo '<h3 style="float:left;margin:10px;width:300px;display:inline-block;">' . __( 'Privacy Settings', 'live-template-editor-client' ) . '</h3>';
				
				if( $fields = $this->get_privacy_fields() ){
					
					echo '<div style="margin:10px 0 10px 0;display: inline-block;">';
						
						foreach( $fields as $field ){

							echo '<div style="width:150px;display:inline-block;font-weight:bold;">'.$field['label'].'</div>';
							
							$this->parent->admin->display_field(array(
								
								'id'		=> $field['id'],
								'type'		=> 'switch',
								'default'	=> !empty($field['default']) ? $field['default'] : 'off',
					
							), $user );
								
							echo'<br>';
						}
					
					echo '</div>';
				}
					
			echo'</div>';
		}	
	}
	
	public function include_user_profile($path){

		if( $this->id > 0 && $this->in_tab ){
			
			include($this->parent->views . '/profile.php');
		}
		
		return $path;
	}	
	
	public function get_profile_shortcode(){
		
		if( empty($this->id) ){
			
			ob_start();
			
			include($this->parent->views . '/navbar.php');
			
			if( $this->parent->user->loggedin ){
				
				if( !empty($_REQUEST['list']) ){
					
					add_action('ltple_list_sidebar',array($this,'get_sidebar'),10,3);
					
					include( $this->parent->views . '/list.php' );
				}
				else{
					
					add_action('ltple_settings_sidebar',array($this,'get_sidebar'),10,3);
					
					include($this->parent->views . '/settings.php');
				}
			}
			else{
				
				echo $this->parent->login->get_form();
			}
			
			return ob_get_clean();
		}
	}
	
	public function get_sidebar( $sidebar, $currentTab = 'home', $output = '' ){
			
		$storage_count = $this->parent->layer->count_layers_by_storage();
		
		$sidebar .= '<li class="gallery_type_title">Profile Settings</li>';
		
		$sidebar .= '<li'.( $currentTab == 'general-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '"><span class="fa fa-user-circle"></span> General Info</a></li>';
		
		$sidebar .= '<li'.( $currentTab == 'privacy-settings' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=privacy-settings"><span class="fa fa-user-shield"></span> Privacy Settings</a></li>';
		
		if( !empty($this->parent->apps->list) ){
		
			$sidebar .= '<li'.( $currentTab == 'social-accounts' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=social-accounts"><span class="fa fa-share-alt"></span> Social Accounts</a></li>';
		}
		
		$sidebar .= apply_filters('ltple_profile_settings_sidebar','',$currentTab,$storage_count);
		
		// website settings

		$section = apply_filters('ltple_website_settings_sidebar','',$currentTab,$storage_count);
				
		if( !empty($section) ){

			$sidebar .= '<li class="gallery_type_title">Web Pages</li>';
			
			$sidebar .= $section;
		}
			
		return $sidebar;
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
					}
					
					$completeness = apply_filters('ltple_profile_completeness',$completeness,$user,$user_meta);
				}
			}

			$this->completeness[$user_id] = $completeness;
		}
		
		return $this->completeness[$user_id];
	}
	
	public function get_whatsapp_url(){
		
		$url = '';
		
		if( $phone = get_option('ltple_phone_support',false) ){
			
			$phone = preg_replace('/[^0-9]/','',$phone);
		
			$url = 'https://wa.me/'.$phone;
		}
		
		return apply_filters('ltple_profile_whatsapp_url',$url,$this->id);
	}
	
	public function is_unclaimed(){
		
		if( is_null($this->is_unclaimed) ){
		
			$is_unclaimed = false;
			
			if( !empty($this->user->ID) ){
			
				$claimed = get_user_meta( $this->user->ID, $this->parent->_base . 'profile_claimed',true);
				
				if( $claimed === 'false' ){
					
					$is_unclaimed = true;
				}
			}
			
			$this->is_unclaimed = $is_unclaimed;
		}
		
		return $this->is_unclaimed;
	}
	
	public function is_public(){
		
		if( is_null($this->is_public) ){
			
			$is_public = false;
			
			if( !empty($this->user) ){
				
				$skip_unclaimed = false;
				
				if( !$last_seen = intval( get_user_meta( $this->parent->plan->get_license_holder_id($this->user->ID), 'ltple__last_seen',true) ) ){
					
					if( $this->is_unclaimed() ){
						
						// profile auto added
						
						$skip_unclaimed = true;
					}
				}
				
				if( $last_seen > 0 || $skip_unclaimed === true ){
					
					$aboutMe = get_user_meta( $this->user->ID, $this->parent->_base . 'policy_about-me',true );
					
					if( $aboutMe !== 'off' ){
						
						$is_public = true;
					}
				}
			}
			
			$this->is_public = $is_public;
		}
		
		return $this->is_public;
	}
	
	public function is_self(){
		
		if( is_null($this->is_self) ){
			
			$this->is_self = ( $this->parent->user->loggedin && !empty($this->user) && $this->user->ID  == $this->parent->user->ID ? true : false );
		}
		
		return $this->is_self;
	}
	
	public function handle_update_profile(){
			
		if(!empty($_POST['settings'])){
			
			if( $_POST['settings'] == 'general-info' ){
				
				// save general information
				
				foreach( $this->fields as $field ){
					
					$field_id = $field['id'];
					
					if( isset($_POST[$field_id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$field_id])) ) ){
						
						$content = wp_kses_post($_POST[$field_id]);
						
						if( in_array( $field_id, array( 'ltple_profile_html', 'ltple_profile_css' )) ){

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
			}
			elseif( $_POST['settings'] == 'privacy-settings' ){
				
				$this->save_privacy_settings($this->parent->user->ID);
			}
			elseif( $_POST['settings'] == 'social-accounts' ){
				
				$this->save_social_accounts($this->parent->user->ID);
			}
			
			do_action('ltple_update_profile');
		}
	}
	
	public function save_privacy_settings($user_id){

		if( $fields = $this->get_privacy_fields() ){
				
			foreach( $fields as $field){
				
				$id = $field['id'];
				
				$content = ( !empty($_POST[$id]) ? wp_kses_post($_POST[$id]) : 'off' );

				update_user_meta( $user_id, $id, $content );
			}
		}
	}
	
	public function save_social_accounts($user_id){
		
		if( $accounts = $this->get_social_accounts() ){

			foreach( $accounts as $label => $fields){
				
				foreach( $fields as $field ){
				
					$id = $field['id'];
				
					$content = ( !empty($_POST[$id]) ? wp_kses_post($_POST[$id]) : 'off' );

					update_user_meta( $user_id, $id, $content );
				}
			}
		}
	}
	
	public function get_profile_picture_fields( $user_id = 0, $userApps = array() ){
	 
		if( $user_id == 0) {
			
			$user_id = $this->parent->user->ID;
			
			$userApps = $this->parent->user->apps;
		}

		// get local picture

		$fields['profile_picture'] = array(

			'id' 			=> $this->parent->_base . 'profile_picture',
			'label'			=> 'Avatar',
			'description'	=> '',
			'type'			=> 'avatar',
			'data'			=> $this->parent->image->get_avatar_url( $user_id ),
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
		
		if( empty($this->tabs) ){

			// get home tab
			
			$this->tabs['home']['position'] = 0;
			
			$this->tabs['home']['name'] 	= 'Home';
				
			$this->tabs['home']['slug'] 	= 'home';
			
			$this->tabs['home']['content'] 	= '';

			if( $this->tab == 'home' ){
				
				if( $profile_html = $this->user->remaining_days > 0 ? apply_filters('ltple_user_profile_html','',$this->user->ID) : '' ){
				
					$this->tabs['home']['content'] = '<div class="layer-' . $this->user->ID . '">' . $profile_html . '</div>';
					
					// get home css
					
					$this->profile_css = apply_filters('ltple_user_profile_css','',$this->user->ID);
					
					if( !empty($this->profile_css) ){

						$this->profile_css = $this->parent->layer->parse_css_content($this->profile_css, '.layer-' . $this->user->ID);
					}

					wp_register_style( $this->parent->_token . '-home', false, array());
					wp_enqueue_style( $this->parent->_token . '-home' );
				
					wp_add_inline_style( $this->parent->_token . '-home', '
					
						html {
							
							font-size: 100% !important;
							scroll-behavior: smooth !important;
						}

						#home {

							margin:0px !important;
							display:block !important;
							width:auto !important;
						}

						#home ul, #about li {
							
							list-style:none !important;
						}

						#home .layer-' . $this->user->ID . ' > *:first-child {

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
				}
			}
			
			// get about tab
			
			$this->tabs['about']['position'] = 1;
		
			$this->tabs['about']['name'] = 'About';
			
			$this->tabs['about']['slug'] = 'about';
			
			$content = '';
			
			if( $fields = $this->get_profile_fields() ){

				foreach( $fields as $field ){
					
					if( !empty($field['id']) && !in_array($field['id'],array( 'nickname', 'ltple_profile_html', 'ltple_profile_css')) ){

						if( isset($this->user->{$field['id']}) ){
							
							$meta = $this->user->{$field['id']};
						}
						else{
							
							$meta = get_user_meta( $this->user->ID , $field['id'] );
						}
						
						if(	$field['id'] == 'user_url'){
							
							if( !empty($meta) ){
							
								$meta = '<a target="_blank" href="'.$meta.'">'.$meta.' <span style="font-size:11px;" class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>';
							}
						}
						else{
							
							if( !empty($meta) ){
								
								// add line break
								
								$meta = '<p>' . str_replace(PHP_EOL,'</p><p>',strip_tags($meta)) . '</p>';
							}							
							
							if( $field['id'] == 'description' ){
								
								$meta = apply_filters('ltple_profile_about_description',$meta);
							
								if( empty($meta) ){
									
									$meta = 'Nothing to say';
								}
							}
						}

						if( !empty($meta) ){
													
							$content .= '<h4>' . ucfirst($field['label']) . '</h4>';
								
							$content .= '<div>';
								
								$content .= $meta;
								
							$content .= '</div>';
						}
					}
				}
			}
			
			$this->tabs['about']['content'] = '<div class="col-md-9">';
			
				$this->tabs['about']['content'] .= apply_filters('ltple_profile_about_content',$content);
			
			$this->tabs['about']['content'] .= '</div>';
			
			if( $this->tab == 'about' ){
				
				// register about style

				wp_register_style( $this->parent->_token . '-about', false, array());
				wp_enqueue_style( $this->parent->_token . '-about' );
			
				wp_add_inline_style( $this->parent->_token . '-about', '

					#about {
						
						margin-top:15px !important;
					}
					
					#about div {
						
						margin:10px 0;
					}
					
					#social_icons img {
						background:#fff;
						border:1px solid #eee;
						padding:1px;
						height:30px;
						width:30px;
						border-radius:250px;
					}
					
					#about h2, #about h3, #about h4, #about h5 {
					
						padding:8px 0;						
						font-weight:bold;
						text-transform: uppercase;
						color:' . $this->parent->settings->mainColor . ';
					}
					
					#about h4{
						
						font-size:16px;
					}
					
					#about h5{
						
						font-size:14px;
					}
					
					#about table th {
						background: none;
						font-weight: bold;
					}
					
					#about table td, #about table th {
						padding: 8px;
						border-bottom: none;
						border-right: none;
						border-left: none;
						text-align: left;
					}
				');
			}
			
			// get addon tabs
			
			$tabs = apply_filters('ltple_profile_tabs',[],$this->user,$this->tab);
			
			// sort addon tabs
			
			usort($tabs, function($a, $b) {
				
				return $a['position'] - $b['position'];
			});
			
			// parse addon tabs
			
			foreach( $tabs as $i => $tab ){
				
				$tab['slug'] = empty($tab['slug']) ? sanitize_title($tab['name']) : $tab['slug'];
				
				$this->tabs[$tab['slug']] = $tab;
			}
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
		
		$this->parent->urls->profile = apply_filters('ltple_profile_url',$this->parent->urls->primary . '/' . $this->slug . '/');
		
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
					
				}
				elseif( !empty($_GET['tab']) && $_GET['tab'] == 'social-accounts' ){
					
					
				}
				else{
					
					$this->pictures	= $this->get_profile_picture_fields();
					
					$this->fields = $this->get_profile_fields();
				}
				
				// update profile fields
			
				$this->handle_update_profile();
			}
		}
	}
	
	public function get_profile_fields( $fields=[] ){
		
		/*
		$fields['user_login'] = array(

			'id' 			=> 'user_login',
			'label'			=> 'Username',
			'description'	=> '',
			'placeholder'	=> 'Username',
			'type'			=> 'text',
			'location'		=> 'general-info',
			'disabled'		=> true
		);
		*/
		
		$fields['nickname'] = array(

			'id' 			=> 'nickname',
			'label'			=> 'Nickname',
			'description'	=> 'Your public name',
			'placeholder'	=> 'Nickname',
			'type'			=> 'text',
			'location'		=> 'general-info',
			'required'		=> true
		);

		$fields['description'] = array(

			'id' 			=> 'description',
			'label'			=> 'Description',
			'description'	=> 'Brief text description of yourself',
			'placeholder'	=> '',
			'type'			=> 'textarea',
			'location'		=> 'general-info',
			'style'			=> 'height:80px;',
		);
		
		$fields['user_url'] = array(
		
			'id' 			=> 'user_url',
			'label'			=> 'External URL',
			'description'	=> 'SEO optimized backlink (dofollow)',
			'placeholder'	=> 'https://',
			'location'		=> 'general-info',
			'type'			=> 'text'			
		);
		
		if( $this->parent->user->remaining_days > 0 ){
			
			$fields['ltple_profile_html'] = array(

				'id' 			=> 'ltple_profile_html',
				'label'			=> 'Home Page (HTML)',
				'description'	=> 'Customize your profile page with HTML',
				'placeholder'	=> '',
				'type'			=> 'code_editor',
				'code'			=> 'html',
				'location'		=> 'home-page',
				'disabled'		=> false,
				
			);
			
			$fields['ltple_profile_css'] = array(

				'id' 			=> 'ltple_profile_css',
				'label'			=> 'Home Page (CSS)',
				'description'	=> 'Customize your profile page with CSS',
				'placeholder'	=> '',
				'type'			=> 'code_editor',
				'code'			=> 'css',
				'location'		=> 'home-page',
				'disabled'		=> false,
			);			
		}
		else{
			
			$fields['ltple_profile_html'] = array(

				'id' 			=> 'ltple_profile_html',
				'label'			=> 'Home Page (HTML)',
				'description'	=> 'Customize your profile page with HTML',
				'placeholder'	=> 'For paid license only',
				'type'			=> 'code_editor',
				'code'			=> 'html',
				'location'		=> 'home-page',
				'disabled'		=> true,
				'data'			=> '',
				
			);
			
			$fields['ltple_profile_css'] = array(

				'id' 			=> 'ltple_profile_css',
				'label'			=> 'Home Page (CSS)',
				'description'	=> 'Customize your profile page with CSS',
				'placeholder'	=> 'For paid license only',
				'type'			=> 'code_editor',
				'code'			=> 'css',
				'location'		=> 'home-page',
				'disabled'		=> true,
				'data'			=> '',
			);
		}

		return $fields;
	}
	
	public function get_social_accounts(){
		
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
		
		return $this->socialAccounts;
	}	
	
	public function get_privacy_fields(){
		
		if( is_null($this->privacySettings) ){
			
			$this->privacySettings['about'] = array(

				'id' 			=> $this->parent->_base . 'policy_about-me',
				'label'			=> 'Public Access',
				'description'	=> 'Anyone can see my profile & pages',
				'type'			=> 'switch',
				'default'		=> 'on',
			);
			
			do_action('ltple_privacy_settings');
		}
		
		return $this->privacySettings;
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

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
	var $name		= null;
	var $theme		= null;
	
	var $urls 		= array();

	var $privacySettings 	= null;
	var $socialAccounts 	= null;
	var $pictures;

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

		add_action('rest_api_init', function () {
			
			register_rest_route( 'ltple-menu/v1', '/profile', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_profile_menu'),
				'permission_callback' => '__return_true',
			) );
			
		} );

		add_shortcode('ltple-client-profile', array( $this , 'render_user_profile' ) );
		
		add_action('template_include', array( $this , 'include_user_profile' ));
			
		add_action('show_user_profile', array( $this, 'show_privacy_settings' ),20,1 );
		add_action('edit_user_profile', array( $this, 'show_privacy_settings' ),20,1 );
		
		add_action('personal_options_update', array( $this, 'save_privacy_settings' ) );
		add_action('edit_user_profile_update', array( $this, 'save_privacy_settings' ) );
		
		add_filter('ltple_default_user-theme_content', array( $this, 'get_default_page_template'),10,1 );
		add_filter('ltple_default_user-theme_css', array( $this, 'filter_default_page_style'),10,1 );
		
		add_filter('ltple_parse_css_variables',function($content){
			
			$content = $this->parse_page_urls($content);
			
			$content = $this->parse_theme_variables($content);
			
			return $content;
			
		},10,1);
		
		add_action( 'ltple_view_my_profile_settings', function(){
			
			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->dashboard .'"><span class="fa fa-th-large" aria-hidden="true"></span> Dashboard</a>';

			echo'</li>';					

			echo'<li style="position:relative;background:#182f42;">';
				
				echo '<a href="'. $this->parent->urls->profile .'"><span class="fa fa-cog" aria-hidden="true"></span> Profile Settings</a>';

			echo'</li>';			
		},1);
		
		add_action('ltple_view_my_profile', function(){
	
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

        add_action('ltple_theme_template', function($template,$tab){
            
            if( $tab == 'browse' ){

                $template = '<div class="browser-wrapper" style="background:#181e23;z-index:1;overflow:hidden;background-image:url('.$this->parent->assets_url . '/images/loader.svg);background-position:center center;background-repeat:no-repeat;width:100%;height:100vh;position:absolute;">';
                    
                    $template .= '{{ page.content }}';

                $template .= '</div>';
            }

            return $template;

        },PHP_INT_MAX,2);
	}
	
	public function get_current_profile_id(){
		
		$profile_id = apply_filters('ltple_profile_id',intval(get_query_var('pr')));
	
		return $profile_id;
	}
	
	public function get_current_parameters(){
		
		if( $user_id = $this->get_current_profile_id() ){

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
		
			if( empty($this->user->ID) ){
				
				include( $this->parent->views . '/profile/restricted.php' );
            }
            
			$this->user->period_end = $this->parent->plan->get_license_period_end($this->user->ID,false); // TODO refresh license once after disclaimer via no_cache (maybe)
			
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
				
				// disclaimer
				
				if( $this->in_tab() === true ){
					
					do_action('ltple_profile_disclaimer');
				}
				
				// card
				
				$tabs = $this->get_profile_tabs();
				
				if( $this->tab == 'home' && empty($tabs['home']['content']) ){
					
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

					add_action('ltple_header_end',function(){
						
						// profile inline style
						
						echo '<style id="profile-style">';
						
							echo $this->get_page_style();
							
							echo $this->get_floating_style();
							
							do_action('ltple_profile_header_style');
							
						echo '</style>';
						
					},PHP_INT_MAX);
					
					add_action('wp_enqueue_scripts',function(){
						
						// profile inline script
						
						if( !$this->parent->inWidget ){
						
							wp_register_script( $this->parent->_token . '-profile_script', '', array( 'jquery' ) );
							wp_enqueue_script( $this->parent->_token . '-profile_script' );
					
							wp_add_inline_script( $this->parent->_token . '-profile_script', $this->get_profile_script());					
						}
						
					},PHP_INT_MAX);
				}
			}
			else{
					
				include( $this->parent->views . '/profile/restricted.php' );
			}
		}
	}
	
	public function in_tab(){
		
		if( empty($this->in_tab) ){
			
			$in_tab = false;

            if( $tabs = $this->get_profile_tabs() ){
				
				if( isset($tabs[$this->tab]) ){
					
					$in_tab = true;
				}
			}
			
			$this->in_tab = apply_filters('ltple_profile_in_tab',$in_tab,$this->tab);
		}
		
		return $this->in_tab;
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
	
	public function filter_default_page_style(){
	
		$style = '
		
		html{
			
			font-size: 100%;
			scroll-behavior: smooth;
		}
		
		p{
		    line-height: 2;
		}
		
		a{
			
			color: {{ theme.css.link_color }};
		}
		
		a:hover{
			
			text-decoration:none;
		}
		
		.btn {
			
			color: #fff;
			text-transform: uppercase;
			box-shadow: none;
			transition: box-shadow 0.5s ease;
		}
		
		.btn:hover {
		
		    box-shadow: 0 14px 26px -12px rgb(0 0 0 / 42%), 0 4px 23px 0px rgb(0 0 0 / 12%), 0 8px 10px -5px rgb(0 0 0 / 20%);
		}
		
		.btn-sm {
			
			font-size: 11px;
		}
		
		.btn-primary, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active {
				
			background-color:{{ theme.css.link_color }};
				
			border-color:{{ theme.css.link_color }};

			border-radius: 25px;
		}
		
		.profile-heading {
			
			height:80px;
			padding:0;
			background-color: #333;
			background-image: url("{{ profile.banner.url }}");
			background-position: center center;
			background-size: cover;
			background-attachment:scroll;
			background-repeat: no-repeat;
			border-bottom:5px solid {{ theme.css.main_color }};
			position:relative;
			overflow:hidden;
		}

		.profile-overlay {
			
			width:100%;
			height:350px;
			position:absolute;
			background-image: linear-gradient(to bottom right,#284d6b,{{ theme.css.main_color }});
			opacity:.7;
		}
		
		.profile-avatar{
			
			padding:10px;
			position:absolute;
			text-align:left;
		}
		
		.profile-avatar img {

			border:none;
			border-radius: 100px;
			margin:0px;
			position: relative;
			background:#fff;
			box-shadow:6px -10px 6px -7px rgba(0, 0, 0, 0.27), -7px -10px 6px -7px rgba(0, 0, 0, 0.27);
		}
		
		.profile-title{
			
			padding:25px 0 0 85px;
			color: #fff !important;
			font-weight: normal;
			font-size:calc( 0.5vw + 15px );
			position: relative;
			text-shadow: 0px 0px 8px rgba(0, 0, 0, .4);
			box-shadow: none !important;
			background: none !important;
			float:left;
			margin:0;
			line-height:25px;
		}
		
		#ltple-content #panel {
			
			display: inline-block;
			width: 100%;
			background: rgb(249, 249, 249);
		}

		#ltple-content #sponsor.tab-pane {
			
			line-height: 20px;
		}

		#ltple-nav {
			
			position: relative;
			border:none;
			box-shadow:0 1px 3px 0 rgba(0,0,0,.2), 0 1px 1px 0 rgba(0,0,0,.14), 0 2px 1px -1px rgba(0,0,0,.12);
			overflow:visible;
			margin:0;
			background:{{ theme.css.navbar_color }};
			height: 41px;
		}

		#ltple-nav > li+li  {
			
			margin-left:0;
			border-left: 1px solid #132533;
		}
		
		#ltple-nav > li {
			
			position: relative;
			display: block;
			line-height: 25px;
		}
		
		#ltple-nav > li > a {
			
			padding:8px 15px !important;
			color:#fff;
			display:inline-block;
			height:41px;
			font-family: "Open Sans", Helvetica;
			text-transform: uppercase;
			font-size: 12px;
			border-radius:0;
		}

		#ltple-nav > li > a:hover {
			
			color:{{ theme.css.main_color }};
		}
		
		#ltple-nav > li.active > a {
		
			background-color:{{ theme.css.main_color }};
		}
		
		#ltple-nav > li.active > a:hover {
			
			color:#fff;
		}
		';
		
		return $style;
	}
	
	public function get_page_style(){
		
		return apply_filters('ltple_parse_css_variables',$this->filter_default_page_style());
	}
	
	public function get_floating_style(){
		
		$style = '
		
		#floating_bar a {
			
			font-size:20px;
			height:35px;
			width:35px;
			text-align: center;
			z-index: 200;
			position: fixed;
			background:#fff;
			border-radius:25px;
			box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
			color:{{ theme.css.main_color }};
		}
		
		#floating_bar i {
			
			padding: 3px!important;
			margin: 5px!important;
		}
		
		#floating_bar #whatsapp {

			bottom: 125px;
			right: 20px;
		}

		#floating_bar #to_top {

			display: none;
			bottom: 80px;
			right: 20px;
		}
		';
		
		$style = $this->parse_theme_variables($style);
		
		return $style;
	}
	
	public function get_card_style(){
		
		$style = '
		
		* {
		  box-sizing: border-box;
		  transition: .5s ease-in-out;
		}

		html, body {
		  background-image: linear-gradient(to bottom right,#284d6bdb,{{ theme.css.main_color }}63);
		  height: 100%;
		  margin: 0;
		  overflow: hidden;
		  font-family: helvetica neue,helvetica,arial,sans-serif;
		}
		html h1, body h1 {
		  font-size: 25px;
		  font-weight: 200;
		  color: white;
		  line-height: 30px;
		  margin-bottom: 15px;
		}
		html h2, body h2 {
			font-size: 16px;
			color: {{ theme.css.main_color }};
			background: #fff;
			display: inline;
			padding: 3px 11px;
			box-shadow: inset 0px 0px 1px #666;
			border-radius: 250px;
		}

		#wrapper {
		  /*opacity: 0;*/
		  opacity: 1;
		  display: table;
		  height: 100%;
		  width: 100%;
		}
		#wrapper.loaded {
		  opacity: 1;
		  transition: 2.5s ease-in-out;
		}
		#wrapper #content {
		  display: table-cell;
		  vertical-align: middle;
		}
		#logo{
			z-index: 1;
			position: absolute;
			left: 50%;
			margin-left: -50px;
			top: 25px;				
		}
		#logo img{
			height:50px;	
			width:auto;
		}
		#card {
		  height: 400px;
		  width: 300px;
		  margin: 0 auto;
		  position: relative;
		  z-index: 1;
		  perspective: 600px;
		}
		#card #front, #card #back {
		  border-radius: 10px;
		  height: 100%;
		  width: 100%;
		  position: absolute;
		  left: 0;
		  top: 0;
		  transform-style: preserve-3d;
		  backface-visibility: hidden;
		  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
		}
		#card #front {
		  transform: rotateY(0deg);
		  overflow: hidden;
		  z-index: 1;
		}
		#card #front #arrow {
		  position: absolute;
		  height: 50px;
		  line-height: 50px;
		  font-size: 30px;
		  z-index: 10;
		  bottom: 0;
		  right: 50px;
		  color: rgba(255, 255, 255, 0.5);
		  animation: arrowWiggle 1s ease-in-out infinite;
		}
		#card #front #top-pic {
		  height: 50%;
		  width: 100%;
		  background-image: url({{ profile.banner.url }});
		  background-image: linear-gradient(to bottom right,#284d6bdb,{{ theme.css.main_color }}63), url({{ profile.banner.url }});
		  background-size: cover;
		  background-position: center center;
		}
		#card #front #avatar {
		  width: 114px;
		  height: 114px;
		  top: 50%;
		  left: 50%;
		  margin: -77px 0 0 -57px;
		  border-radius: 100%;
		  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.8), 0 4px 5px rgba(107, 5, 0, 0.6), 0 0 50px 50px rgba(255, 255, 255, 0.25);
		  background-image: url({{ profile.avatar.url }});
		  background-size: contain;
		  position: absolute;
		  z-index: 1;
		}
		#card #front #info-box {
		  height: 50%;
		  width: 100%;
		  position: absolute;
		  display: table;
		  left: 0;
		  bottom: 0;
		  background: {{ theme.css.main_color }}cc;
		  padding: 50px 0px;
		}
		#card #front #social-bar {
		  height: 50px;
		  width: 100%;
		  position: absolute;
		  bottom: 0;
		  left: 0;
		  line-height: 50px;
		  font-size: 18px;
		  text-align: center;
		}
		#card #front #social-bar a {
		  display: inline-block;
		  color: #ffffffb0;
		  font-size:13px;
		  text-decoration: none;
		  padding: 5px;
		  line-height: 18px;
		  border-radius: 5px;
		}
		#card #front #social-bar a:hover {
		  color: #450300;
		  background: rgba(255, 255, 255, 0.3);
		  transition: .25s ease-in-out;
		}
		#card #back {
		  transform: rotateY(180deg);
		  background-color: rgba(255, 255, 255, 0.6);
		  display: table;
		  z-index: 2;
		  font-size: 13px;
		  line-height: 20px;
		  padding: 50px;
		}
		#card #back .back-info {
		  text-align: justify;
		  text-justify: inter-word;
		}
		#card #back .back-info a {
			
			color:{{ theme.css.link_color }};
		}
		#card #back #social-bar {
		  height: 50px;
		  width: 100%;
		  position: absolute;
		  bottom: 0;
		  left: 0;
		  line-height: 50px;
		  font-size: 18px;
		  text-align: center;
		}
		#card #back #social-bar a {
		  display: inline-block;
		  line-height: 18px;
		  color: {{ theme.css.link_color }};
		  text-decoration: none;
		  padding: 5px;
		  border-radius: 5px;
		}
		#card #back #social-bar a:hover {
		  color: #450300;
		  background: rgba(223, 74, 66, 0.5);
		  transition: .25s ease-in-out;
		}
		#card .info {
		  display: table-cell;
		  height: 100%;
		  vertical-align: middle;
		  text-align: center;
		}
		#card.flip #front {
		  transform: rotateY(180deg);
		}
		#card.flip #back {
		  transform: rotateY(360deg);
		}

		#background {
		  position: fixed;
		  background-color: black;
		  top: 0;
		  left: 0;
		  height: 100%;
		  width: 100%;
		}
		#background #background-image {
		  height: calc(100% + 60px);
		  width: calc(100% + 60px);
		  position: absolute;
		  top: -30px;
		  left: -30px;
		  -webkit-filter: blur(10px);
		  background-image: url({{ profile.banner.url }});
		  background-image: linear-gradient(to bottom right,#284d6bdb,{{ theme.css.main_color }}63), url({{ profile.banner.url }});
		  background-size: cover;
		  background-position: center;
		}

		@keyframes arrowWiggle {
		  0% {
			right: 50px;
		  }
		  50% {
			right: 35px;
		  }
		  100% {
			right: 50px;
		  }
		}
		';

		return apply_filters('ltple_parse_css_variables',$style);
	}
	
	public function get_about_style(){
		
		$style = '
		
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
		
			padding:25px 0 0 0;						
			font-weight:bold;
			text-transform: uppercase;
			color:{{ theme.css.main_color }};
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
			
			line-height:2;
			padding: 8px;
			border-bottom: none;
			border-right: none;
			border-left: none;
			vertical-align: top;
			text-align: left;
		}
		';
					
		$style = $this->parse_theme_variables($style);
		
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
					
					if( $("#ltple-content").length > 0 ){
						
						function adjustProfileContentHeight() {
						
							var profileContent = $("#ltple-content");
							
							var footerHeight = $("#ltple-footer").length > 0 ? $("#ltple-footer").height() : 0;
														
							var profileContentOffset = profileContent.offset().top;
							
							var remainingHeight = $(window).height() - profileContentOffset - footerHeight;
							
							profileContent.css("min-height", remainingHeight + "px");
						}
						
						adjustProfileContentHeight();

						$(window).resize(function(){
							
							adjustProfileContentHeight();
						});
					}
					
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
			
			if( $fields = $this->get_privacy_fields() ){

				echo '<h2>' . __( 'Privacy Settings', 'live-template-editor-client' ) . '</h2>';

				echo '<table class="form-table">';
				echo '<tbody>';

					foreach( $fields as $field ){
						
						echo '<tr>';
						
							echo '<th><label>'.$field['label'].'</label></th>';
							
							echo '<td>';
							
								$this->parent->admin->display_field(array(
									
									'id'		=> $field['id'],
									'type'		=> 'switch',
									'default'	=> !empty($field['default']) ? $field['default'] : 'off',
						
								), $user );
								
							echo '</td>';
							
						echo '</tr>';
					}
					
				echo '</tbody>';
				echo '</table>';
			}
		}	
	}
	
	public function include_user_profile($path){
		
		if( $this->id > 0 && $this->in_tab() ){
			
			include $this->parent->views . '/profile.php';
		}
		
		return $path;
	}
	
	public function render_about_page(){
		
		$content = '<div class="profile-heading text-center" style="height:100px;padding:0;">';
		
			$content .= '<div class="profile-overlay"></div>';
		
			// mobile avatar
			
			$content .= '<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">';

				$content .= '<div class="profile-avatar text-left hidden-sm hidden-md hidden-lg" style="padding:12px 8px;position:absolute;">';
				
					$content .= '<img style="border:none;" src="{{ profile.avatar.url }}" height="70" width="70" />';
					
				$content .= '</div>';					
			
			$content .= '</div>';
			
			$content .= '<div class="col-xs-9 col-sm-9 col-md-9 col-lg-10">';
			
				$content .= '<h1 style="font-size:calc( 0.5vw + 15px );float:left;padding:35px 0 0 0;margin:0;color:#fff;">{{ site.name }}</h1>';
			
			$content .= '</div>';
			
		$content .= '</div>';
	
		$content .= '<div id="panel" style="display:inline-block !important;box-shadow:inset 0px 2px 11px -4px rgba(0,0,0,0.75);">';

			$content .= '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 hidden-xs text-center" style="padding:30px;">';
					
				// desktop avatar	
					
				$content .= '<div class="profile-avatar text-center hidden-xs" style="margin: -90px 10px 25px 10px;position:relative;">';
				
					$content .= '<img src="{{ profile.avatar.url }}" height="150" width="150" />';
					
					if( $this->is_pro ){
						
						$content .= '<span class="label label-primary" style="position:absolute;bottom:10%;right:16%;background:{{ theme.css.main_color }};font-size:14px;">pro</span>';					
					}						
					
				$content .= '</div>';
				
				if( $this->parent->settings->is_enabled('ranking') ){
					
					// user stars
					
					$content .= '<span class="badge" style="background-color:#fff;color:{{ theme.css.main_color }};font-size:18px;border-radius: 25px;padding: 8px 18px;box-shadow: inset 0px 0px 1px #666;">';
						
						$content .= '<span class="fa fa-star" aria-hidden="true"></span> ';
						
						$content .= $this->parent->stars->get_count($this->user->ID);
				
					$content .= '</span>';
				}
				
				// social icons

				$content .= '<div id="social_icons" class="text-center" style="margin:20px 0 0 0;">';
						
					$content .= apply_filters('ltple_before_social_icons','');
					
					if( !empty($this->apps) ){		
						
						foreach( $this->apps as $app ){
							
							if( !empty($app->user_profile) && !empty($app->social_icon) ){
								
								$show_profile = get_user_meta($this->user->ID,$this->parent->_base . 'app_profile_' . $app->ID,true);
								
								if( $show_profile != 'off' ){
									
									$content .= '<a href="' . $app->user_profile . '" style="margin:5px;display:inline-block;" ref="nofollow" target="_blank">';
										
										$content .= '<img src="' . $app->social_icon . '" />';
										
									$content .= '</a>';
								}
							}
						}
					}
					
					$content .= apply_filters('ltple_after_social_icons','');
					
				$content .= '</div>';
			
			$content .= '</div>';

			$content .= '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 library-content" style="padding:0;border-left:1px solid #ddd;background:#fff;padding-bottom:0px;min-height:calc( 100vh - 133px );">';
				
				$content .= '{{ theme.navbar }}';
				
				$content .= '{{ page.content }}';
				
			$content .= '</div>';
			
		$content .= '</div>';

		$content = $this->parse_page_content($content);
		
		return apply_filters('ltple_parse_css_variables',$content);
	}
	
	public function get_navbar_content(){
		
		$content = '<ul id="ltple-nav" class="nav nav-pills nav-resizable" role="menubar">';
			
			if( $tabs = $this->get_profile_tabs() ){
				
				foreach( $tabs as $tab){
					
					if( !empty($tab['name']) ){
						
						$active = ( $tab['slug'] == $this->tab ? ' active' : '');

						$url = $this->url . '/';

						if( $tab['slug'] != 'home' ){
							
							$url .= $tab['slug'] . '/';
						}
						
						$content .= '<li class="'.$active.'" role="menuitem">';
						
							$content .= '<a href="' . $url . '">'.$tab['name'].'</a>';
						
						$content .= '</li>';
					}
				}
			}
			
		$content .= '</ul>';

		if( $this->is_preview() ){
			
			$content = '<ltple-mod ltple-prop="theme.navbar">' . $content . '</ltple-mod>';
		}
		
		$notice = '';
	
		if( !$this->is_public() && $this->is_self() ){
			
			$notice .= '<div class="alert alert-warning row" style="margin:0 0 0 0 !important;">';
				
				$notice .= '<div class="col-xs-9">';
				
					$notice .= 'Your profile is restricted, only you can see this page.';
				
				$notice .= '</div>';
				
				$notice .= '<div class="col-xs-3 text-right">';
				
					$notice .= '<a class="btn btn-sm btn-success" href="' . $this->parent->urls->profile . '?tab=privacy-settings">Start</a>';
				
				$notice .= '</div>';
				
			$notice .= '</div>';			
		}
		elseif( $this->is_unclaimed() ){
			
			$notice .= '<div class="alert alert-info row" style="line-height:25px;margin:0px 0 20px 0 !important;">';
				
				$notice .= '<div class="col-xs-9">';
					
					$notice .= 'This profile was auto generated';
				
				$notice .= '</div>';
				
				$notice .= '<div class="col-xs-3 text-right">';
				
					$notice .= '<a class="btn btn-sm btn-success" href="' . $this->parent->urls->home . '/contact/" style="color">Claim it</a>';
				
				$notice .= '</div>';
				
			$notice .= '</div>';
		}
		
		if( !empty($notice) ){
			
			if( $this->is_preview() ){
				
				$notice = '<ltple-mod ltple-prop="page.notice">' . $notice . '</ltple-mod>';
			}
			
			$content .= $notice;
		}
					
		return $content;
	}
	
	public function render_page_content(){
		
		$content = $this->get_page_template();

		$content = $this->parse_page_content($content);
		
		return apply_filters('ltple_parse_css_variables',$content);
	}
	
	public function get_default_page_template(){
		
		$template = '
		<div class="profile-heading text-center" style="height:80px;padding:0;">
		
			<div class="profile-overlay"></div>
		
			<div class="profile-avatar">
			
				<img style="border:none;" src="{{ profile.avatar.url }}" height="55" width="55" />
				
			</div>				
			
			<div class="profile-title">{{ site.name }}</div>
			
		</div>

		<div id="panel" style="padding:0;background:#fff;">

			{{ theme.navbar }}
				
			{{ page.content }}

		</div>';
		
		return $template;
	}
	
	public function get_page_template(){
		
		return apply_filters('ltple_theme_template',$this->get_default_page_template(),$this->tab);
	}
	
	public function parse_page_content($content){
		
		if( strpos($content,'{{ site.name }}') !== false ){

			$name = ucfirst(get_user_meta( $this->user->ID , 'nickname', true ));
			
			$content = str_replace('{{ site.name }}',$name,$content);
		}
		
		if( strpos($content,'{{ theme.navbar }}') !== false ){
			
			$navbar = $this->get_navbar_content();
			
			$content = str_replace('{{ theme.navbar }}',$navbar,$content);
		}
		
		if( strpos($content,'{{ page.content }}') !== false ){
			
			$tab_content = $this->get_tab_content();
			
			$content = str_replace('{{ page.content }}',$tab_content,$content);
		}
		
		return $content;
	}
	
	public function parse_page_urls($content){
		
		if( strpos($content,'{{ profile.banner.url }}') !== false ){
			
			$banner = $this->parent->image->get_banner_url($this->id) . '?' . time();
			
			$content = str_replace('{{ profile.banner.url }}',$banner,$content);
		}
		
		if( strpos($content,'{{ profile.avatar.url }}') !== false ){
			
			$avatar = $this->parent->image->get_avatar_url( $this->id );
			
			$content = str_replace('{{ profile.avatar.url }}',$avatar,$content);
		}
		
		return $content;
	}
	
	public function get_current_theme(){
		
		if( is_null($this->theme) ){
			
			$user_id = 0;
			$theme_id = 0;
			
			if( $user_id = $this->get_current_profile_id() ){
				
				$theme_id = apply_filters('ltple_profile_theme_id',$theme_id,$user_id);
			}
			elseif( $layer = LTPLE_Editor::instance()->get_layer() ){
				
				$user_id = intval($layer->post_author);
				
				if( $layer->post_type == 'user-theme' ){
					
					$theme_id = $layer->ID;
				}
				elseif( $layer->post_type == 'cb-default-layer' ){
					
					$layer_type = $this->parent->layer->get_layer_type($layer->ID);
					
					if( $layer_type->storage == 'user-theme' ){
						
						$theme_id = $layer->ID;
					}
					elseif( !empty($this->parent->user->ID) ){
						
						$theme_id = apply_filters('ltple_user_theme_id',$theme_id,$this->parent->user->ID);
					}
				}
				else{
					
					$theme_id = apply_filters('ltple_current_theme_id',$theme_id,$layer);
				}
			}
            
            $vars = array(
					
                'css' => array(
                
                    'main_color' 	=> $this->parent->settings->mainColor,
                    'navbar_color' 	=> $this->parent->settings->navbarColor,
                    'link_color' 	=> $this->parent->settings->linkColor,
                ),
            );
            
			if( $theme = LTPLE_Editor::instance()->get_layer($theme_id) ){
				
				if( $theme->post_type == 'user-theme' ){
					
					if( $theme->post_author == $user_id || ( !empty($layer) && $layer->post_type == 'cb-default-layer' ) ){
						
						// get css variables
						
						if( $theme->post_type == 'cb-default-layer' ){
							
							if( $data = get_post_meta($theme->ID,'layerCssVars',true) ){
								
								if( !empty($data['input']) ){
									
									foreach( $data['input'] as $e => $input ){
										
										if( !in_array($input,array(
											
											'select',
											'checkbox',
											
										))){
											
											$name = isset($data['name'][$e]) ? sanitize_title($data['name'][$e]) : '';

											if( !empty($name) ){
												
												$required 	= isset($data['required'][$e]) ? sanitize_title($data['required'][$e]) : '';
												$value 		= isset($data['value'][$e]) ? sanitize_text_field($data['value'][$e]) : '';
												
												if( $required != 'required' || !empty($value) ){
													
													$vars['css'][$name] = $value;
												}
											}
										}
									}
								}
							}
						}
						else{
							
							if( $values = get_post_meta($theme->ID,'themeCssVars',true) ){
								
								foreach( $values as $name => $value ){
									
									if( $name = sanitize_title($name) ){
										
										$vars['css'][$name] = sanitize_text_field($value);
									}
								}
							}
						}
					}
				}
			}
            
            if( empty($theme) ){
                
                $theme = (object) array(
                
                    'ID'        => 0,
                    'post_type' => 'user-theme',
                );
            }
            
            $theme->variables = $vars;
			
			$this->theme = $theme;
		}
		
		return $this->theme;
	}
	
	public function parse_theme_variables($content){
		
		if( $theme = $this->get_current_theme() ){
            
			foreach( $theme->variables['css'] as $key => $value ){
				
				if( strpos($content,'{{ theme.css.'.$key.' }}') !== false ){
					
					$content = str_replace('{{ theme.css.'.$key.' }}',$value,$content);
				}
			}
		}
		
		return $content;
	}
	
	public function get_tab_content(){
		
		$content = '';
		
		if( $tabs = $this->get_profile_tabs() ){
			
			foreach( $tabs as $tab){
				
				if( !empty($tab['content']) && $tab['slug'] == $this->tab  ){

					$content .= '<div class="tab-pane active" id="'.$tab['slug'].'">';
					
						if(!empty($this->parent->message)){ 
						
							//output message
						
							$content .= $this->parent->message;
						}									
					
						$content .= $tab['content'];

					$content .= '</div>';
					
					break;
				}							
			}
		}
		
		return $content;
	}
	
	public function render_user_profile(){
		
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
	
		$sidebar .= '<li class="gallery_type_title gallery_head"><a href="'.$this->parent->urls->dashboard . '" style="display:contents;color:#fff;"><span class="fas fa-angle-double-left" style="color:#fff;"></span> Web Settings</a></li>';
		
		$sidebar .= '<li class="gallery_type_title">Profile</li>';

		$sidebar .= '<li'.( $currentTab == 'general-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '"><span class="fa fa-user-circle"></span> General Info</a></li>';
		
		$sidebar .= '<li'.( $currentTab == 'privacy-settings' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=privacy-settings"><span class="fa fa-user-shield"></span> Privacy Settings</a></li>';
		
		if( !empty($this->parent->apps->list) ){
		
			$sidebar .= '<li'.( $currentTab == 'social-accounts' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=social-accounts"><span class="fa fa-share-alt"></span> Social Accounts</a></li>';
		}
		
		$sidebar .= apply_filters('ltple_profile_settings_sidebar','',$currentTab,$storage_count);

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

						wp_update_user( array( 
							
							'ID' 		=> $this->parent->user->ID, 
							$field_id 	=> $content,
						));
						
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
		
		if( is_null($this->tabs) ){
			
			$tabs = array();
			
			if( !empty($this->tab) ){
				
				// get home tab
				
				$tabs['home']['position'] 	= 0;
				$tabs['home']['name'] 		= 'Home';
				$tabs['home']['slug'] 		= 'home';
				$tabs['home']['content'] 	= '';

				if( $this->tab == 'home' ){
					
					$layer = LTPLE_Editor::instance()->get_layer();
					
					$layer_type = $this->parent->layer->get_layer_type($layer);
					
					$content = '';
					
					if( !empty($layer->post_type) && $layer->post_type == 'cb-default-layer' && $layer_type->storage == 'user-theme' ){
						
						if( $demo_id = intval(get_post_meta($layer->ID,'demoLayerId',true)) ){
						
							$demo = LTPLE_Editor::instance()->get_layer($demo_id);
						
							if( $demo->post_type == 'cb-default-layer' ){
								
								$classes = array('layer-' . $demo->ID);
								
								if( $layerCssLibraries = $this->parent->layer->get_libraries($demo->ID,'css') ){
									
									foreach($layerCssLibraries as $library){
										
										$classes[] = $library->prefix;
									}
								}

								$content = '<div class="'.implode(' ',$classes).'">' . $demo->html . '</div>';
							}
						}
						
						if( empty($content) ){
							
							// some content to skip the card
							
							$content = '<div style="display: block;background:#eee;text-align:center;font-size:30px;padding:32vh 0;color:#888;">';
							
								$content .= 'Page content goes here';
							
							$content .= '</div>';
						}
						
						if( $this->is_preview() ){
							
							$content = '<ltple-mod ltple-prop="page.content">' . $content . '</ltple-mod>';
						}
					}
					elseif( $this->user->remaining_days > 0 ){
						
						if( $profile_html = apply_filters('ltple_user_profile_html','',$this->user->ID) ){
						
							//$content = '<div class="site-' . $this->user->ID . '">' . $profile_html . '</div>';
							
							$content = $profile_html;
						}
					}
			
					$tabs['home']['content'] = $content;
				}
				
				// get about tab
				
				$tabs['about']['position'] 	= 1;
				$tabs['about']['name'] 		= 'About';
				$tabs['about']['slug'] 		= 'about';
				
				$content = '';
				
				if( $fields = $this->get_profile_fields() ){

					foreach( $fields as $field ){
						
						if( !empty($field['id']) && !in_array($field['id'],array('nickname'))){

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
								
								if( $field['id'] == 'description' ){
									
									$meta = apply_filters('ltple_profile_about_description',$meta);
									
									if( empty($meta) ){
										
										$meta = '<p>Nothing to say</p>';
									}
								}
								
								$meta = $this->sanitize_text_editor($meta);
							}

							if( !empty($meta) ){
								
								if( $field['id'] == 'description' ){
									
									$content .= $meta;
								}
								else{
									
									$content .= '<h5>' . ucfirst($field['label']) . '</h5>';
									
									$content .= '<p>';
										
										$content .= $meta;
										
									$content .= '</p>';
								}
							}
						}
					}
				}
				
				$tabs['about']['content'] = '<div class="col-md-9">';
				
					$tabs['about']['content'] .= apply_filters('ltple_profile_about_content',$content);
				
				$tabs['about']['content'] .= '</div>';
				
				if( $this->tab == 'about' ){
					
					// register about style

					wp_register_style( $this->parent->_token . '-about', false, array());
					wp_enqueue_style( $this->parent->_token . '-about' );
				
					wp_add_inline_style( $this->parent->_token . '-about',$this->get_about_style());
				}
                
                if( $this->tab == 'browse' ){
                    
                    $folder = get_page_by_path($this->tabSlug,OBJECT,'folder');
                    
                    $style = 'background:#181e23;border:0;width:100%;height:100vh;position:absolute;top:0;bottom:0;right:0;left:0;';

                    if( !empty($folder->post_author) && intval($folder->post_author) == $this->user->ID ){

                        $browser_url = $this->parent->media->get_browser_url($this->user->ID,$folder->ID);
                        
                        $content = '<iframe data-src="'.$this->sanitize_primary_url($browser_url).'" style="'.$style.'"></iframe>';
                    }
                    else{

                        $content = '<div style="'.$style.'" ><div class="alert alert-danger">This storage doesn\'t exist.</div></div>';
                    }

                    $tabs['browse']['position'] = 2;
                    $tabs['browse']['name']     = 'Browser';
                    $tabs['browse']['slug']     = 'browse';
                    $tabs['browse']['content'] 	= $content;
                }

				// get addon tabs
				
				$extra = apply_filters('ltple_profile_tabs',$tabs,$this->user,$this->tab);
				
				// sort addon tabs
				
				usort($extra, function($a, $b) {
					
					return $a['position'] - $b['position'];
				});
				
				// parse addon tabs
				
				foreach( $extra as $i => $tab ){
					
					$tab['slug'] = empty($tab['slug']) ? sanitize_title($tab['name']) : $tab['slug'];
					
					$tabs[$tab['slug']] = $tab;
				}
			}
			
			$this->tabs = $tabs;
		}
		
		return $this->tabs;
	}

    public function sanitize_primary_url($url) {

        $parsed_url = parse_url($url);

        // Get the primary base (already includes https://... and no trailing slash)
        $primary = $this->parent->urls->primary;

        // Keep the original scheme if it exists, otherwise use the one from primary
        $scheme = $parsed_url['scheme'] ?? parse_url($primary, PHP_URL_SCHEME) ?? 'https';

        // Extract the host from the primary
        $host = parse_url($primary, PHP_URL_HOST);

        // Handle optional path
        $path = $parsed_url['path'] ?? '';

        // Rebuild the URL
        $url = $scheme . '://' . $host . $path;

        // Preserve query string if exists
        if (!empty($parsed_url['query'])) {
            $url .= '?' . $parsed_url['query'];
        }

        // Preserve fragment if exists
        if (!empty($parsed_url['fragment'])) {
            $url .= '#' . $parsed_url['fragment'];
        }

        return $url;
    }


	
	public function sanitize_text_editor($str){
		
		$str = apply_filters('wpautop',$str);
		
		$str = apply_filters('nl2br',$str);
	
		$str =  strip_tags($str,'<p><table><tr><th><td><b><strong><em><span><i><br><ul><ol><li><h4><h5>');
		
		if( !empty($str) ){
		
			$str = preg_replace('/ (class|id|style)="[^"]*"/i', '', $str);
		
			$str = '<p>' . str_replace(PHP_EOL,'</p><p>',$str) . '</p>';
		
			$str = preg_replace('/^<p>(<p[^>]*>(.*?)<\/p>)<\/p>$/','$1',$str);
		}
		
		return $str;
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
	
	public function is_preview(){
		
		return LTPLE_Editor::instance()->is_preview();
	}
	
	public function get_profile_fields( $fields=[] ){
		
		$fields['nickname'] = array(

			'id' 			=> 'nickname',
			'label'			=> 'Name',
			'description'	=> 'Your public name',
			'placeholder'	=> 'Name',
			'type'			=> 'text',
			'location'		=> 'general-info',
			'required'		=> true
		);

		$fields['description'] = array(

			'id' 			=> 'description',
			'label'			=> 'About',
			'type'			=> 'text_editor',
			'location'		=> 'general-info',
			'style'			=> 'height:80px;',
			'settings'		=> array(
			
				'wpautop' 			=> true,
				'media_buttons'		=> false,
				'drag_drop_upload'	=> false,
				'textarea_name'		=> 'description',
				'textarea_rows'		=> 20,
				'teeny'				=> true,
				'quicktags'			=> false,
				'tinymce' 			=> array(
				
					'toolbar1' => 'bold,italic,underline,bullist,numlist',
				)
			),
		);
		
		$fields['user_url'] = array(
		
			'id' 			=> 'user_url',
			'label'			=> 'External URL',
			'description'	=> 'SEO optimized backlink (dofollow)',
			'placeholder'	=> 'https://',
			'location'		=> 'general-info',
			'type'			=> 'url'			
		);
		
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

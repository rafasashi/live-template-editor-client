<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Urls {
	
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
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		$this->current 		= $this->parent->request->proto . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->home 		= ( is_ssl() ? home_url('','https') : home_url() );
		
		$this->api 			= $this->home . '/wp-json/';
		$this->api_embedded	= $this->api . 'ltple-embedded/v1/info';
		
		$this->host = get_option( $this->parent->_base . 'host_url' );	
		
		if( $this->editorSlug = get_option( $this->parent->_base . 'editorSlug' )){
			
			$this->editor = $this->home . '/' . $this->editorSlug . '/';
		}
		
		if( $this->appsSlug = get_option( $this->parent->_base . 'appsSlug' )){
			
			$this->apps = $this->home . '/' . $this->appsSlug . '/';
		}
		
		add_filter('init', array( $this, 'init_urls'));
	}
	
	public function init_urls(){
		
		// force permalink structure
		
		if( get_option('permalink_structure') == '' ){
			
			global $wp_rewrite;
			
			$wp_rewrite->set_permalink_structure('/%postname%/');
		}
		
		// get editor url

		$editor = get_option( $this->parent->_base . 'editorSlug' );
		
		if( empty( $editor ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Editor',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-editor]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$editor = update_option( $this->parent->_base . 'editorSlug', get_post($post_id)->post_name );
		}
		
		$this->editor = $this->home . '/' . $editor . '/';		
		
		// get apps url
		
		$apps = get_option( $this->parent->_base . 'appsSlug' );

		if( empty( $apps ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Apps',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-apps]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$apps = update_option( $this->parent->_base . 'appsSlug', get_post($post_id)->post_name );
		}
		
		$this->apps = $this->home . '/' . $apps . '/';		
		
		// get login url
		
		$login = get_option( $this->parent->_base . 'loginSlug' );
		
		if( empty( $login ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Login',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-login]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$login = update_option( $this->parent->_base . 'loginSlug', get_post($post_id)->post_name );
		}
		
		$this->login 	= $this->home . '/' . $login . '/';
		
		// get plans url
		
		$plans = get_option( $this->parent->_base . 'plansSlug' );
		
		if( empty( $plans ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Plans',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> 'Right an article listing your plans here. Use the plan shortcodes to generate a checkout button.',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$plans = update_option( $this->parent->_base . 'plansSlug', get_post($post_id)->post_name );
		}
		
		$this->plans 	= $this->home . '/' . $plans . '/';
		
		// get product url
		
		$product = get_option( $this->parent->_base . 'productSlug' );
		
		if( empty( $product ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Product',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-product]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$product = update_option( $this->parent->_base . 'productSlug', get_post($post_id)->post_name );
		}
		
		$this->product 	= $this->home . '/' . $product . '/';
		
		// get profile url
		
		$profile = get_option( $this->parent->_base . 'profileSlug' );
		
		if( empty( $profile ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Profile',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-profile]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$profile = update_option( $this->parent->_base . 'profileSlug', get_post($post_id)->post_name );
		}
		
		if( defined('REW_PRIMARY_SITE') ){
			
			$this->profile 	= REW_PRIMARY_SITE . '/' . $profile . '/';
		}
		else{
			
			$this->profile 	= $this->home . '/' . $profile . '/';
		}
		
		// get ranking url
		
		if( $this->parent->settings->options->enable_ranking == 'on' ){
			
			$ranking = get_option( $this->parent->_base . 'rankingSlug' );
			
			if( empty( $ranking ) ){
				
				$post_id = wp_insert_post( array(
				
					'post_title' 		=> 'Ranking',
					'post_type'     	=> 'page',
					'comment_status' 	=> 'closed',
					'ping_status' 		=> 'closed',
					'post_content' 		=> '[ltple-client-ranking]',
					'post_status' 		=> 'publish',
					'menu_order' 		=> 0
				));
				
				$ranking = update_option( $this->parent->_base . 'rankingSlug', get_post($post_id)->post_name );
			}
			
			$this->ranking 	= $this->home . '/' . $ranking . '/';
		}
		
		// get addon urls
		
		do_action('ltple_urls');
	}
	
	/**
	 * Main LTPLE_Client_Urls Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Urls is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Urls instance
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

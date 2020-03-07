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

		$this->parent 		= $parent;
		
		$this->home 		= ( is_ssl() ? home_url('','https') : home_url() );	
		$this->current 		= $this->home . $_SERVER['REQUEST_URI'];

		$this->api 			= $this->home . '/' . rest_get_url_prefix() . '/';
		$this->api_embedded	= $this->api . 'ltple-embedded/v1/info';
		
		$this->host 		= get_option( $this->parent->_base . 'host_url' );	
		
		$this->edit 		= $this->home . '/edit/';
		
		if( $this->editorSlug = get_option( $this->parent->_base . 'editorSlug' )){
			
			$this->editor 	= $this->home . '/' . $this->editorSlug . '/';
		
			$this->gallery 	= $this->editor;
		}
		
		if( $this->appsSlug = get_option( $this->parent->_base . 'appsSlug' )){
			
			$this->apps = $this->home . '/' . $this->appsSlug . '/';
		}
		
		add_filter('init', array( $this, 'init_urls'));
		
		add_filter('post_type_link', array( $this, 'parse_permalink'),1,2);
	}
	
	public function parse_permalink( $post_link, $post ){
		
		$post_link = str_replace('%author%', $post->post_author, $post_link);

		if( $post->post_type == 'user-layer' ){

			$post_link = add_query_arg(array( 
			
				'post_type' => $post->post_type,
				'p' 		=> $post->ID,
			
			),$post_link);
		}
			
		return $post_link;
	}
	
	public function current_url_in($slug){

		if( !empty($slug) && is_string($slug) && isset($this->{$slug}) && strpos($this->current, $this->{$slug}) !== false ){
			
			return true;		
		}
		
		return false;
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

		// get admin frontend url
		
		$admin = get_option( $this->parent->_base . 'adminSlug' );

		if( empty( $admin ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Admin',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-admin]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$admin = update_option( $this->parent->_base . 'adminSlug', get_post($post_id)->post_name );
		}
		
		$this->admin = $this->home . '/' . $admin . '/';		
		
		// get dashboard url
		
		$dashboard = get_option( $this->parent->_base . 'dashboardSlug' );

		if( empty( $dashboard ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Dashboard',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-dashboard]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$dashboard = update_option( $this->parent->_base . 'dashboardSlug', get_post($post_id)->post_name );
		}
		
		$this->dashboard = $this->home . '/' . $dashboard . '/';			
		
		// get checkout url
		
		$checkout = get_option( $this->parent->_base . 'checkoutSlug' );

		if( empty( $checkout ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Checkout',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-checkout]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$checkout = update_option( $this->parent->_base . 'checkoutSlug', get_post($post_id)->post_name );
		}
		
		$this->checkout = $this->home . '/' . $checkout . '/';	
		
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

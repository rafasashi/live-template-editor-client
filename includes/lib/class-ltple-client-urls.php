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

		$this->primary		= defined('REW_PRIMARY_SITE') && !empty(REW_PRIMARY_SITE) ? REW_PRIMARY_SITE : $this->home;

		$this->api 			= $this->home . '/' . rest_get_url_prefix() . '/';
		$this->api_embedded	= $this->api . 'ltple-embedded/v1/info';
				
		// get edit gallery
		
		$this->edit = $this->home . '/edit/';
		
		if( $this->editorSlug = get_option( $this->parent->_base . 'editorSlug' )){
			
			$this->editor 	= $this->home . '/' . $this->editorSlug . '/';
		
			$this->gallery 	= $this->editor;
		}
		
		// get account url
		
		if( $this->accountSlug = get_option( $this->parent->_base . 'accountSlug' )){
			
			$this->account = $this->home . '/' . $this->accountSlug . '/';
		}
		
		// get apps url
		
		if( $this->appsSlug = get_option( $this->parent->_base . 'appsSlug' )){
			
			$this->apps = $this->home . '/' . $this->appsSlug . '/';
		}
		
		add_filter('init', array( $this, 'init_urls'));
		
		add_filter('post_type_link', array( $this, 'parse_permalink'),1,2);
	}
	
	public function parse_permalink( $post_link, $post ){
		
		$post_link = str_replace('%author%', $post->post_author, $post_link);
		
		if( !$this->parent->layer->is_hosted( $post->post_type ) ){

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
		
		if( get_option('permalink_structure') == '' ){
			
			// force permalink structure

			global $wp_rewrite;
			
			$wp_rewrite->set_permalink_structure('/%postname%/');
		}
		
		$this->register_url('editor',array(
			
			'post_title' 		=> 'Editor',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-editor]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
	
		$this->register_url('dashboard',array(
			
			'post_title' 		=> 'Dashboard',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-dashboard]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
		
		$this->register_url('checkout',array(
			
			'post_title' 		=> 'Checkout',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-checkout]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
		
		$this->register_url('account', array(
			
			'post_title' 		=> 'Account',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-account]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));		
		
		$this->register_url('apps', array(
			
			'post_title' 		=> 'Apps',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-apps]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));

		$this->register_url('login',array(
			
			'post_title' 		=> 'Login',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-login]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
		
		$this->register_url('plans',array(
			
			'post_title' 		=> 'Plans',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> 'Right an article listing your plans here. Use the plan shortcodes to generate a checkout button.',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
		
		$this->register_url('product',array(
			
			'post_title' 		=> 'Product',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-product]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));
				
		$this->register_url('ranking',array(
				
			'post_title' 		=> 'Ranking',
			'post_type'     	=> 'page',
			'comment_status' 	=> 'closed',
			'ping_status' 		=> 'closed',
			'post_content' 		=> '[ltple-client-ranking]',
			'post_status' 		=> 'publish',
			'menu_order' 		=> 0
		));

		// get addon urls
		
		do_action('ltple_urls');
	}

	private function register_url($id,$args){
		
		// get product url
		
		$option_name = $this->parent->_base . $id . 'Slug';
		
		$slug = get_option($option_name);
		
		if( empty($slug) ){
			
			if( $post_id = wp_insert_post($args) ){
			
				$slug = update_option( $option_name, get_post($post_id)->post_name );
			}
		}
		
		$this->{$id} = $this->home . '/' . $slug . '/';
		
		add_filter('pre_update_option_' . $option_name, function($value, $old_value, $option_name){
			
			if( $value != $old_value  ){

				$value = sanitize_title($value);

				if( $post_id = url_to_postid($old_value) ){
					
					// edit page slug
					
					if( wp_update_post( array(
					
						'ID' 		=> $post_id,
						'post_name' => $value,
						
					)) ){
						
						// flush rewrite rules
						
						update_option('rewrite_rules',false);
					}
				}
			}
			
			return $value;
			
		},99999,3);
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

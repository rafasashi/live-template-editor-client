<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Campaign extends LTPLE_Client_Object {
	
	var $parent;
	var $taxonomy;	
	var $triggers;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;

		$this->taxonomy = 'campaign-trigger';	

		$this->parent->register_taxonomy( 'campaign-trigger', __( 'Campaign Trigger', 'live-template-editor-client' ), __( 'Campaign Trigger', 'live-template-editor-client' ),  array('email-campaign'), array(
			
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> false,
			'sort' 					=> '',
		));		

		add_action( 'admin_init', array($this,'get_campaign_triggers'));
	}
	
	public function get_campaign_triggers(){

		$this->triggers = $this->get_terms( $this->taxonomy, array(
			
			'user-registration' => 'User Registration',
		));
	}

	/**
	 * Main LTPLE_Client_Campaign Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Campaign is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Campaign instance
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
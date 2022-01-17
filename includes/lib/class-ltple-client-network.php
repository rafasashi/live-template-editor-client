<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Network extends LTPLE_Client_Object {
	
	var $parent;
	var $taxonomy;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$post_types = array(
		
			'user',
			'cb-default-layer'
		);

		$this->parent->register_taxonomy( 'user-contact', __( 'User emails', 'live-template-editor-client' ), __( 'User email', 'live-template-editor-client' ), $post_types, array(
			
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
			'rewrite' 				=> true,
			'sort'					=> '',
		));		
	
		add_action( 'init', array($this,'init_network'));
	}
	
	public function init_network(){
		
		if( is_admin() ){
			
			
		}
	}
}  
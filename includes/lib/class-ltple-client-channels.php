<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Channels extends LTPLE_Client_Object {
	
	var $parent;
	var $taxonomy;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->taxonomy = 'marketing-channel';
		
		$this->parent->register_taxonomy( 'marketing-channel', __( 'Marketing Channel', 'live-template-editor-client' ), __( 'Marketing Channel', 'live-template-editor-client' ),  array('user'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort'					=> '',
		));		
	
		add_action( 'init', array($this,'init_channels'));
	}
	
	public function init_channels(){
		
		if( is_admin() ){
			
			// add channel taxonomy custom fields
			
			add_action( 'show_user_profile', array( $this, 'get_user_marketing_channel' ),2,10 );
			add_action( 'edit_user_profile', array( $this, 'get_user_marketing_channel' ) );
			
			// save channel taxonomy custom fields
			
			add_action( 'personal_options_update', array( $this, 'save_custom_user_channel_taxonomy_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_channel_taxonomy_fields' ) );
				
			add_action( 'admin_init', array($this,'get_marketing_channels'));
		}
	}
	
	public function get_marketing_channels(){

		$this->list = $this->get_terms( $this->taxonomy, array(
			
			'blog' 					=> 'Blog',
			'email-campaign' 		=> 'Email Campaign',
			'forums' 				=> 'Forums',
			'friend-recommendation' => 'Friend Recommendation',
			'other' 				=> 'Other',
			'search-engines' 		=> 'Search Engines',
			'social-networks' 		=> 'Social Networks',
		));
	}
	
	public function get_user_marketing_channel( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Marketing Channel', 'live-template-editor-client' ) . '</h3>';
				
				$tax = get_taxonomy( $this->taxonomy );

				/* Make sure the user can assign terms of the user taxonomy before proceeding. */
				if ( !current_user_can( $tax->cap->assign_terms ) )
				return;
			
				$terms = wp_get_object_terms( $user->ID, $this->taxonomy );

				echo wp_dropdown_categories(array(
				
					'show_option_none' => 'Select a channel',
					'taxonomy'     => $this->taxonomy,
					'name'    	   => $this->taxonomy,
					'show_count'   => false,
					'hierarchical' => true,
					'selected'     => ( ( !isset($terms->errors) && isset($terms[0]->term_taxonomy_id) ) ? $terms[0]->term_taxonomy_id : ''),
					'echo'		   => false,
					'class'		   => 'form-control',
					'hide_empty'   => false
				));
					
			echo'</div>';
		}	
	}
	
	public function save_custom_user_channel_taxonomy_fields( $user_id ) {
		
		$tax = get_taxonomy( $this->taxonomy );

		/* Make sure the current user can edit the user and assign terms before proceeding. */
		if ( !current_user_can( 'administrator', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
			return false;
		
		if(isset($_POST)){
		
			$terms = [];

			if(isset($_POST[$this->taxonomy]) && is_numeric($_POST[$this->taxonomy])){
				
				$terms = intval($_POST[$this->taxonomy]);						
			}

			$response = wp_set_object_terms( $user_id, $terms, $this->taxonomy);

			clean_object_term_cache( $user_id, $this->taxonomy );
		}
	}
}  
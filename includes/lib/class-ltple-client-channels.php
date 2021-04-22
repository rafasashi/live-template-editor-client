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
		
		$this->parent->register_taxonomy( 'marketing-channel', __( 'Marketing Channels', 'live-template-editor-client' ), __( 'Marketing Channel', 'live-template-editor-client' ),  array('user'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> false,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
			'rewrite' 				=> true,
			'sort'					=> '',
		));		
	
		add_action( 'init', array($this,'init_channels'));
	}
	
	public function init_channels(){
		
		if( is_admin() ){
			
			// add channel taxonomy custom fields
			
			add_action( 'show_user_profile', array( $this, 'show_user_marketing_channel' ),23,1 );
			add_action( 'edit_user_profile', array( $this, 'show_user_marketing_channel' ),23,1 );
			
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
			'user-invitation' 		=> 'User Invitation',
			'user-profile' 			=> 'User Profile',
			//'manager' 			=> 'Manager',
			'other' 				=> 'Other',
			'search-engines' 		=> 'Search Engines',
			'social-networks' 		=> 'Social Networks',
		));
	}
	
	public function update_user_channel( $user_id, $name = '' ){	
		
		$taxonomy = 'marketing-channel';

		// get term_id
		
		if( isset($_POST[$taxonomy]) &&  is_numeric($_POST[$taxonomy]) ){
			
			$term_id = intval($_POST[$taxonomy]);
		}
		elseif( !empty($name) ){
			
			$term = get_term_by('name', $name, $taxonomy);
			
			if( !empty($term->term_id) ){
				
				$term_id = intval($term->term_id);
			}
			else{
				
				$term = wp_insert_term(
				
					ucfirst($name),
					$taxonomy,
					array(
					
						'description'	=> '',
						'slug' 			=> str_replace(' ','-',$name),
					)
				);

				$term_id = intval($term->term_id);
			}
		}
		
		if(!empty($term_id)){
			
			//-------- save channel --------
			
			$response = wp_set_object_terms( $user_id, $term_id, $taxonomy);
			
			clean_object_term_cache( $user_id, $taxonomy );	

			if( empty($response) ){

				echo 'Error saving user channel...';
				exit;
			}				
		}			
	}
	
	public function show_user_marketing_channel( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Marketing Channel', 'live-template-editor-client' ) . '</h3>';
				
				$tax = get_taxonomy( $this->taxonomy );

				/* Make sure the user can assign terms of the user taxonomy before proceeding. */
				if ( !current_user_can( $tax->cap->assign_terms ) )
				return;
			
				$terms = wp_get_object_terms( $user->ID, $this->taxonomy );

				echo '<div style="display:inline-block;">';
				
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
					
				echo '</div>';
					
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
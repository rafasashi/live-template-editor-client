<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Channels {
	
	var $parent;
	var $taxonomy;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->taxonomy = 'marketing-channel';
	
		// add channel taxonomy custom fields
		
		add_action( 'show_user_profile', array( $this, 'get_user_marketing_channel' ) );
		add_action( 'edit_user_profile', array( $this, 'get_user_marketing_channel' ) );
		
		// save channel taxonomy custom fields
		
		add_action( 'personal_options_update', array( $this, 'save_custom_user_channel_taxonomy_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_channel_taxonomy_fields' ) );

		// prevent new term insertion on save
		/*
		add_action( 'pre_insert_term', function ( $term, $taxonomy ){

			return ( $this->taxonomy === $taxonomy )
				? new WP_Error( 'term_addition_blocked', __( 'You cannot add terms to this taxonomy' ) )
				: $term;
		}, 0, 2 );
		*/
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
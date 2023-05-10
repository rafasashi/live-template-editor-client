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
		
		$term_id = false;
		
		if( isset($_POST[$taxonomy]) &&  is_numeric($_POST[$taxonomy]) ){
			
			$term_id = floatval($_POST[$taxonomy]);
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
				
				$term_id = intval($term['term_id']);
			}
		}
		
		if( is_numeric($term_id) && $term_id > 0 ){
			
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
			

			$tax = get_taxonomy( $this->taxonomy );

			/* Make sure the user can assign terms of the user taxonomy before proceeding. */
			if ( !current_user_can( $tax->cap->assign_terms ) ) return;
			
			echo '<h2>' . __( 'Marketing Channel', 'live-template-editor-client' ) . '</h2>';

			echo '<table class="form-table">';
			echo '<tbody>';
				
				echo '<tr>';
				
					echo '<th><label>Channel</label></th>';
					
					echo '<td>';
						
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
						
					echo '</td>';
				
				echo '</tr>';
				
				echo '<tr>';
				
					echo '<th><label>Referent Id</label></th>';
					
					echo '<td>';
						
						$referredBy = get_user_meta( $user->ID, $this->parent->_base . 'referredBy', true );

						$this->parent->admin->display_field( array(
							
							'id' 			=> $this->parent->_base . 'referentId',
							'label'			=> '',
							'description'	=> '',
							'placeholder'	=> 0,
							'type'			=> 'number',
							'data'			=> !empty($referredBy) ? key($referredBy) : 0,
						
						), false, true );
						
					echo '</td>';
				
				echo '</tr>';
				
			echo '</tbody>';
			echo '</table>';
		}	
	}
	
	public function save_custom_user_channel_taxonomy_fields( $user_id ) {
		
		$tax = get_taxonomy( $this->taxonomy );

		if ( !current_user_can( 'administrator', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
			return false;
		
		if(isset($_POST)){

			// save channel
		
			$terms = [];

			if(isset($_POST[$this->taxonomy]) && is_numeric($_POST[$this->taxonomy])){
				
				$terms = intval($_POST[$this->taxonomy]);						
			}

			$response = wp_set_object_terms( $user_id, $terms, $this->taxonomy);

			clean_object_term_cache( $user_id, $this->taxonomy );
		
			// save referent 
			
			$field = $this->parent->_base . 'referentId';
			
			if( isset($_POST[$field]) && is_numeric($_POST[$field]) ){

				if( $referent = get_user_by('id',$_POST[$field]) ){
					
					update_user_meta( $user_id, $this->parent->_base . 'referredBy', [ $referent->ID => $referent->user_login ] );
				}
			}
		}
	}
}  
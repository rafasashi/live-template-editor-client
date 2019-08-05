<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Script extends LTPLE_Client_Object { 
	
	public $parent;

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;

		$this->parent->register_taxonomy( 'php-library', __( 'PHP Libraries', 'live-template-editor-client' ), __( 'PHP Library', 'live-template-editor-client' ),array('cb-default-layer'), 
	
			array(
				'hierarchical' 			=> true,
				'public' 				=> false,
				'show_ui' 				=> true,
				'show_in_nav_menus' 	=> false,
				'show_tagcloud' 		=> false,
				'meta_box_cb' 			=> null,
				'show_admin_column' 	=> true,
				'update_count_callback' => '',
				'show_in_rest'          => true,
				'rewrite' 				=> false,
				'sort' 					=> '',
			)
		);
		
		add_action( 'add_meta_boxes', function(){

			global $post;
			
			if( $post->post_type == 'user-script' ){
				
				$this->parent->admin->add_meta_box (
					
					'script-content',
					__( 'HTML content', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'script-image',
					__( 'Image URL', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
			}

		});		

		add_filter('init', array( $this, 'init_script' ));
		
		add_filter('admin_init', array( $this, 'init_script_backend' ));
		
		add_filter('init', array( $this, 'init_script_frontend' ));
	}

	public function init_script(){

	
	}
	
	public function init_script_backend(){

		add_action('php-library_edit_form_fields', array( $this, 'get_fields' ) );
	
		add_action('create_php-library', array( $this, 'save_fields' ) );
		
		add_action('edit_php-library', array( $this, 'save_fields' ) );	
	
		add_filter('rew_export_term', array( $this, 'filter_exported_term' ),10,1 );
	}
	
	function filter_exported_term($term){
		
		if( !empty($term['slug']) ){
		
			$term['options']['scripts_' . $term['slug']] = get_option( 'scripts_' . $term['slug'] );
		}
		
		return $term;
	}
	
	public function init_script_frontend(){

	
	}
	
	public function get_fields($term){	
	
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Repository</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field( array(
				
					'type'				=> 'repository',
					'id'				=> 'php_repo_'.$term->slug,
					'name'				=> 'php_repo_'.$term->slug,
					'array' 			=> [],
					'description'		=> ''
					
				), false );
				
			echo'</td>';
			
		echo'</tr>';		
	}
	
	public function save_fields($term_id){

		//collect all term related data for this new taxonomy
		
		$term = get_term($term_id);

		//save our custom fields as wp-options
		
		if( isset($_POST['php_repo_'.$term->slug]['name']) && isset($_POST['php_repo_'.$term->slug]['type']) && isset($_POST['php_repo_'.$term->slug]['image']) && isset($_POST['php_repo_'.$term->slug]['content'])  ){
			
			if( is_array($_POST['php_repo_'.$term->slug]['name']) && is_array($_POST['php_repo_'.$term->slug]['type']) && is_array($_POST['php_repo_'.$term->slug]['image']) && is_array($_POST['php_repo_'.$term->slug]['content'])  ){

				update_option('php_repo_'.$term->slug, $_POST['php_repo_'.$term->slug]);			
			}
			else{
					
				echo 'Error saving scripts...';
				exit;
			}
		}
	}
}

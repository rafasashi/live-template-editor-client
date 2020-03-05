<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Element extends LTPLE_Client_Object { 
	
	public $parent;

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;

		$this->parent->register_taxonomy( 'element-library', __( 'Element Libraries', 'live-template-editor-client' ), __( 'Element Library', 'live-template-editor-client' ),array('cb-default-layer'), 
	
			array(
				'hierarchical' 			=> true,
				'public' 				=> false,
				'show_ui' 				=> true,
				'show_in_nav_menus' 	=> false,
				'show_tagcloud' 		=> false,
				'meta_box_cb' 			=> null,
				'show_admin_column' 	=> false,
				'update_count_callback' => '',
				'show_in_rest'          => true,
				'rewrite' 				=> false,
				'sort' 					=> '',
			)
		);
		
		add_action( 'add_meta_boxes', function(){

			global $post;
			
			if( $post->post_type == 'user-element' ){
				
				$this->parent->admin->add_meta_box (
					
					'element-content',
					__( 'HTML content', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'element-image',
					__( 'Image URL', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
			}

		});		

		add_filter('init', array( $this, 'init_element' ));
		
		add_filter('admin_init', array( $this, 'init_element_backend' ));
		
		add_filter('init', array( $this, 'init_element_frontend' ));
		
		add_action('wp_loaded', array($this,'get_element_types'));	
	}
	
	public function get_element_types(){

		$this->types = $this->get_terms( 'element-library', array(
			
			'bootstrap-3-grid' => array(
			
				'name' 		=> 'Bootstrap 3 - Grid',
				'options'	=> array(
				
					'elements'	=> $this->index_keys(array(
					
						array(
						
							'name' 		=> '1 block',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/1-block.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);">block<span></span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/2-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/3-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '4 columns',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/4-columns.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 rows',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/2-rows.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 rows',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/3-rows.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'landing page',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/landing-page.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12 text-center" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav left',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/nav-left.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav right',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/nav-right.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="clearfix"></div>',
						),
						array(
						
							'name' 		=> 'L grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/l-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'M grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/m-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'S grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/s-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'XS grid',
							'type'		=> 'grid',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/xs-grid.jpg',
							'content' 	=> '<div class="row"><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div></div>',
						),							
					)),
				),
			),
			/*
			'bootstrap-3-blog' => array(
			
				'name' 		=> 'Bootstrap 3 - Blog',
				'options'	=> array(
				
					'elements'	=> $this->index_keys(array(
					
						array(
						
							'name' 		=> '1 block',
							'image' 	=> $this->parent->assets_url . 'images/flow-charts/grid/1-block.jpg',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);">block<span></span></div><div class="clearfix"></div></div>',
						),
					)),
				),
			),
			*/
		));
	}

	public function init_element(){

	
	}
	
	public function init_element_backend(){

		add_action('element-library_edit_form_fields', array( $this, 'get_fields' ) );
	
		add_action('create_element-library', array( $this, 'save_fields' ) );
		
		add_action('edit_element-library', array( $this, 'save_fields' ) );	
	
		add_filter('rew_export_term', array( $this, 'filter_exported_term' ),10,1 );
	}
	
	function filter_exported_term($term){
		
		if( !empty($term['slug']) ){
		
			$term['options']['elements_' . $term['slug']] = get_option( 'elements_' . $term['slug'] );
		}
		
		return $term;
	}
	
	public function init_element_frontend(){

	
	}
	
	public function get_fields($term){	
	
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Elements</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field( array(
				
					'type'				=> 'element',
					'id'				=> 'elements_'.$term->slug,
					'name'				=> 'elements_'.$term->slug,
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
		
		if( isset($_POST['elements_'.$term->slug]['name']) && isset($_POST['elements_'.$term->slug]['type']) && isset($_POST['elements_'.$term->slug]['image']) && isset($_POST['elements_'.$term->slug]['content'])  ){
			
			if( is_array($_POST['elements_'.$term->slug]['name']) && is_array($_POST['elements_'.$term->slug]['type']) && is_array($_POST['elements_'.$term->slug]['image']) && is_array($_POST['elements_'.$term->slug]['content'])  ){

				update_option('elements_'.$term->slug, $_POST['elements_'.$term->slug],false);			
			}
			else{
					
				echo 'Error saving elements...';
				exit;
			}
		}
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Element extends LTPLE_Client_Object { 
	
	public $parent;
	
	public $list = array();

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;
	
		$this->parent->register_post_type( 'default-element', __( 'Default Elements', 'live-template-editor-client' ), __( 'Default Element', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports'			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title', 'thumbnail'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
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
				'show_in_rest'          => false,
				'rewrite' 				=> false,
				'sort' 					=> '',
			)
		);
		
		add_filter('init', array( $this, 'init_element' ));
		
		add_filter('admin_init', array( $this, 'init_element_backend' ));
		
		add_filter('init', array( $this, 'init_element_frontend' ));
		
		add_action('wp_loaded', array($this,'set_default_elements'));	
	
		add_shortcode('ltple-element-site', array( $this , 'get_element_site' ) );
	}
	
	public function get_element_site($content){
		
		if( defined('REW_SITE') )
			
			return REW_SITE;
			
		return $_SERVER['SERVER_NAME'];
	}
	
	public function set_default_elements(){

		$libraries = $this->get_terms( 'element-library', array(
			
			'bootstrap-3-grid' => array(
			
				'name' 		=> 'Bootstrap 3 - Grid',
				'options'	=> array(
				
					'elements'	=> $this->index_keys(array(
					
						array(
						
							'name' 		=> '1 block',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);">block<span></span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 columns',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 columns',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '4 columns',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '2 rows',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> '3 rows',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="col-xs-12" style="background: rgba(128, 194, 249, 0.18);"><span>row</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'landing page',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-xs-12 text-center" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav left',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'nav right',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-9" style="background: rgba(128, 194, 249, 0.18);"><span>block</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>nav</span></div><div class="clearfix"></div>',
						),
						array(
						
							'name' 		=> 'L grid',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-6" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),	
						array(
						
							'name' 		=> 'M grid',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="col-sm-4" style="background: rgba(128, 194, 249, 0.18);"><span>cell</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'S grid',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-3" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="clearfix"></div></div>',
						),
						array(
						
							'name' 		=> 'XS grid',
							'type'		=> 'grid',
							'image' 	=> '',
							'content' 	=> '<div class="row"><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div><div class="col-sm-1 ltple-ex"></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-2" style="background: rgba(128, 194, 249, 0.18);"><span>col</span></div><div class="col-sm-1 ltple-ex"></div><div class="clearfix"></div></div>',
						),							
					)),
				),
			),
		));
	}

	public function init_element(){

	
	}
	
	public function init_element_backend(){

		add_action('element-library_edit_form_fields', array( $this, 'get_fields' ) );
	
		add_action('create_element-library', array( $this, 'save_fields' ) );
		
		add_action('edit_element-library', array( $this, 'save_fields' ) );	
	
		add_filter('rew_export_term', array( $this, 'filter_exported_term' ),10,2 );

		add_filter('manage_edit-element-library_columns', array( $this, 'filter_element_library_columns' ) );
		
		add_filter('manage_element-library_custom_column', array( $this, 'filter_element_library_column_content' ),10,3);
	}
	
	public function filter_exported_term($term=array(),$term_id){
		
		if( !empty($term_id) ){

			$term['meta'][$this->parent->_base . 'elements'] = $this->get_library_elements($term_id);
		}
		
		return $term;
	}
	
	public function get_library_elements($term){
		
		if( is_numeric($term) ){
			
			$term = get_term($term);
		}
		
		if( isset($term->term_id) ){
			
			$term_id = $term->term_id;
			
			if( !isset($this->list[$term_id]) ){
			
				if( !$elements = get_term_meta($term_id,$this->parent->_base . 'elements',true) ){
					
					if( $elements = get_option('elements_' . $term->slug,false) ){
						
						// migrate to meta
						
						update_term_meta($term_id,$this->parent->_base . 'elements', $elements);
					}
				}
				
				if( 1==1 ){
					
					// normalize content
					
					$old_elements = $elements;
					
					foreach( $elements['content'] as $i => $content ){
						
						// normalize content
						
						$content = str_replace( array(
							
							'wordpress.recuweb.com',
						
						),'[ltple-element-site]',$content);
						
						$elements['content'][$i] = $content;
					}
				}
				
				// do shortcodes
				
				foreach( $elements['content'] as $i => $content ){
					
					// normalize content

					$elements['content'][$i] = do_shortcode($content);
				}
				
				$this->list[$term_id] = $elements;
			}
			
			return $this->list[$term_id];
		}
		
		return false;
	}
	
	public function init_element_frontend(){

	
	}
	
	public function filter_element_library_columns($columns){
		
		$columns = [];
		
		$columns['cb'] 				= '<input type="checkbox" />';
		$columns['name'] 			= 'Name';
		//$columns['description'] 	= 'Description';
		$columns['slug'] 			= 'Slug';
		$columns['elements'] 		= 'Elements';

		return $columns;
	}
	
	public function filter_element_library_column_content($content, $column_name, $term_id){
		
		if( $column_name == 'elements' ){
			
			$content = $this->count_elements($term_id);
		}
		
		return $content;
	}
	
	public function count_elements($term){
		
		$count = 0;
		
		if( is_numeric($term) ){
			
			$term = get_term($term);
		}
		
		if( isset($term->term_id) ){
			
			$term_id = $term->term_id;
			
			if( $elements = $this->get_library_elements($term) ){
				
				if( isset($elements['name']) ){
					
					foreach( $elements['name'] as $i => $name ){
						
						if( !empty($name) && !empty($elements['content'][$i]) )
						
							$count++;
					}
				}
			}
			
			if( $term->parent == 0 ){
				
				if( $children = get_term_children($term->term_id,$term->taxonomy) ){

					foreach( $children as $child ){
						
						$count += $this->count_elements($child);
					}
				}
			}
		}
		
		return $count;
	}
	
	public function get_fields($term){	
	
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Elements</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				
				$this->parent->admin->display_field( array(
				
					'type'				=> 'element',
					'id'				=> 'elements_'.$term->term_id,
					'name'				=> 'elements_'.$term->term_id,
					'data' 				=> $this->get_library_elements($term),
					'description'		=> ''
					
				), false );
				
			echo'</td>';
			
		echo'</tr>';		
	}
	
	public function save_fields($term_id){

		if( isset($_POST['elements_'.$term_id]['name']) && isset($_POST['elements_'.$term_id]['type']) && isset($_POST['elements_'.$term_id]['image']) && isset($_POST['elements_'.$term_id]['content'])  ){
			
			if( is_array($_POST['elements_'.$term_id]['name']) && is_array($_POST['elements_'.$term_id]['type']) && is_array($_POST['elements_'.$term_id]['image']) && is_array($_POST['elements_'.$term_id]['content'])  ){

				update_term_meta($term_id,$this->parent->_base . 'elements', $_POST['elements_'.$term_id]);
			}
			else{
					
				echo 'Error saving elements...';
				exit;
			}
		}
	}
}

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
			'supports' 				=> array('title','thumbnail'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_taxonomy( 'element-library', __( 'Element Libraries', 'live-template-editor-client' ), __( 'Element Library', 'live-template-editor-client' ),array('cb-default-layer','default-element'), 
	
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
		
		add_filter('admin_init', array( $this, 'init_element_backend' ));

		add_action('wp_loaded', array($this,'set_default_elements'));	
		
		add_action('ltple_element_types', array($this,'filter_element_types'));	
		
		add_shortcode('ltple-element-site', array( $this , 'get_element_site' ) );
	}
	
	public function get_element_site($content){
		
		if( defined('REW_SITE') )
			
			return REW_SITE;
			
		return $_SERVER['SERVER_NAME'];
	}
	
	public function filter_element_types($types=array()){
		
		$types['default-element'] = 'Default Element';
		
		return $types;
	}
	
	public function get_default_sections(){
		
		$types = [
						
			'headers'	=>	__( 'Headers', 'live-template-editor-client' ),
			'sections'	=>	__( 'Sections', 'live-template-editor-client' ),
			'actions'	=>	__( 'Actions', 'live-template-editor-client' ),
			'contents'	=>	__( 'Contents', 'live-template-editor-client' ),
			'components'=>	__( 'Components', 'live-template-editor-client' ),
			'buttons'	=>	__( 'Buttons', 'live-template-editor-client' ),
			'features'	=>	__( 'Features', 'live-template-editor-client' ),
			'blogs'		=>	__( 'Blogs', 'live-template-editor-client' ),
			'teams'		=>	__( 'Teams', 'live-template-editor-client' ),
			'profiles'	=>	__( 'Profiles', 'live-template-editor-client' ),
			'projects'	=>	__( 'Projects', 'live-template-editor-client' ),
			'products'	=>	__( 'Products', 'live-template-editor-client' ),
			'pricing'	=>	__( 'Pricing', 'live-template-editor-client' ),
			'testimonials'	=>	__( 'Testimonials', 'live-template-editor-client' ),
			'contact'	=>	__( 'Contact', 'live-template-editor-client' ),
			'images'	=>	__( 'Images', 'live-template-editor-client' ),
			'videos'	=>	__( 'Videos', 'live-template-editor-client' ),
			'widgets'	=>	__( 'Widgets', 'live-template-editor-client' ),
			'menus'		=>	__( 'Menus', 'live-template-editor-client' ),
			'forms'		=>	__( 'Forms', 'live-template-editor-client' ),
			'footers'	=>	__( 'Footers', 'live-template-editor-client' ),
		];
		
		return $types;		
	}
	
	public function set_default_elements(){

		$libraries = $this->get_terms( 'element-library', array(
			
			/*
			'bootstrap-3-grid' => array(
			
				'name' 	=> 'Bootstrap 3 - Grid',
			),
			*/
		));
	}

	public function init_element_backend(){
	
		add_filter('manage_edit-element-library_columns', array( $this, 'filter_element_library_columns' ) );
		add_filter('manage_element-library_custom_column', array( $this, 'filter_element_library_column_content' ),10,3);
	
		add_filter('manage_default-element_posts_columns', 	array( $this, 'filter_default_element_columns' ),999 );
		add_filter('manage_default-element_posts_custom_column', array( $this, 'filter_default_element_column_content' ),10,3);
	
		add_filter('ltple_admin_editor_actions',function($editor_actions){
			
			if( $this->is_element_panel() && current_user_can('administrator') ){
				
				$editor_actions['edit-with-ltple'] 	= 'Editor';
				$editor_actions['refresh-preview'] 	= 'Refresh Preview';
			}
			
			return $editor_actions;
		});
	}
	
	public function get_library_elements($term,$size='thumbnail'){
		
		if( is_numeric($term) ){
			
			$term = get_term($term);
		}
		
		if( isset($term->term_id) ){
			
			$term_id = $term->term_id;
			
			if( !isset($this->list[$term_id]) ){
				
				$elements = array();
				
				// get elements
				
				if( $elems = get_posts( array(
				
					'post_type' 	=> 'default-element',
					'numberposts' 	=> -1,
					'orderby'		=> 'id',
					'order' 		=> 'ASC',
					'tax_query' 	=> array(
						
						array(
						
							'taxonomy' 		=> 'element-library',
							'field' 		=> 'term_id', 
							'terms' 		=> $term->term_id,
							'include_children' => false
						)
					)
					
				))){
					
					foreach( $elems as $elem ){
						
						$meta = get_post_meta($elem->ID);
						
						$content 	= isset($meta['layerContent'][0]) ? $meta['layerContent'][0] : '';
						$type 		= isset($meta['elementType'][0])  ? $meta['elementType'][0]	 : 'sections';
						$drop 		= isset($meta['elementDrop'][0])  ? $meta['elementDrop'][0]  : 'out';
						$image 	 	= $this->parent->layer->get_preview_image_url($elem->ID,$size);
						
						$elements['name'][] 	= $elem->post_title;
						$elements['content'][] 	= $content;
						$elements['type'][] 	= $type;
						$elements['drop'][] 	= $drop;
						$elements['image'][] 	= $image;
					}
				}
				
				if( !empty($elements) ){
				
					// do shortcodes
				
					foreach( $elements['content'] as $i => $content ){
						
						// normalize content

						$elements['content'][$i] = do_shortcode($content);
					}
				}
				
				$this->list[$term_id] = $elements;
			}
			
			return $this->list[$term_id];
		}
		
		return false;
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
	
	public function filter_default_element_columns($columns){
		
		// Remove description, posts, wpseo columns
		
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 		= '<input type="checkbox" />';
		$columns['title'] 	= 'Title';
		$columns['taxonomy-element-library'] = 'Libraries';
		$columns['thumb'] 	= 'Preview'; // must remain last for mobile view
		
		return $columns;
	}

	public function filter_default_element_column_content($column_name, $post_id){
	
		if( $column_name === 'thumb' ){

			$url = $this->parent->layer->get_preview_image_url($post_id,'post-thumbnail',$this->parent->assets_url . 'images/default-element.jpg');
			
			echo '<div style="height:100px;margin:5px 0;overflow:auto;">';

				echo '<a class="preview-' . $post_id . '" target="_blank" href="'.$url.'">';
				
					echo '<img loading="lazy" style="width:150px;" src="'.$url.'">';
				
				echo '</a>';
			
			echo '</div>';
		}

		return $column_name;
	}
	
	public function is_element_panel(){
		
		if( is_admin() ){
			
			$post_type = !empty($_GET['post_type']) ? $_GET['post_type'] : '';
			
			if( $post_type == 'default-element' )
			
				return true;
		}
			
		return false;
	}
}

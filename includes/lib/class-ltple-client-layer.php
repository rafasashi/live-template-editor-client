<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer extends LTPLE_Client_Object { 
	
	public $parent;
	
	public $layer_types 	= array();
	
	public $localTypes 		= null;
	public $storageTypes	= null;
	
	public $storage_count	= null;
	public $editors			= null;
	public $sections		= null;
	public $types			= null; 
	public $ranges			= null;
	
	public $default_ids 	= array();
	
	public $defaultFields 	= array();
	public $userFields 		= array();
	
	public $id				= -1;
	public $defaultId		= -1;
	public $themeId			= -1;
	public $uri				= '';
	public $key				= ''; // gives the server proxy access to the layer
	public $slug			= '';
	public $title			= '';
	public $type			= '';
	public $form			= '';
	public $embedded		= '';
	
	public $layerJson;
	public $pageDef;
	
	public $is_default		= false;
	public $is_local		= false;
	public $is_storage		= false;
	public $is_media		= false;	
	
	public $accountOptions 	= array();
	public $columns			= '';
	public $column			= '';
	public $options			= array(); 
	
	/**
	 * Constructor function
	 */ 
	
	public function __construct( $parent ) {
		
		$this->parent = $parent;
	
		$this->parent->register_post_type( 'cb-default-layer', __( 'Default Templates', 'live-template-editor-client' ), __( 'Default Template', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'cb-default-layer',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> array('slug'=>'preview'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		add_filter('ltple_cb-default-layer_layer_area',function($area,$layer){ 
			
			return 'backend';
			
		},10,2);
		
		add_filter('ltple_live-editor_layer_area',function(){ 
			
			return 'frontend';
			
		});

		$this->parent->register_post_type( 'user-layer', __( 'Templates', 'live-template-editor-client' ), __( 'Template', 'live-template-editor-client' ), '', array(

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
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		add_filter('ltple_user-layer_layer_area',function(){ 
			
			return 'frontend';
		});	
		
		$this->parent->register_post_type( 'user-psd', __( 'Images', 'live-template-editor-client' ), __( 'Image', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title','author'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		add_filter('ltple_user-psd_layer_area',function(){ 
			
			return 'frontend';
		});	
		
		$this->parent->register_taxonomy('layer-type', __( 'Template Gallery', 'live-template-editor-client' ), __( 'Template Gallery', 'live-template-editor-client' ),  array('user-plan','cb-default-layer','user-layer','user-psd'), array(
			
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'layer-range', __( 'Template Range', 'live-template-editor-client' ), __( 'Template Range', 'live-template-editor-client' ),array('user-plan','cb-default-layer'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => array($this,'count_layer_range'),
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));

		$this->parent->register_taxonomy( 'layer-features', __( 'Template Features', 'live-template-editor-client' ), __( 'Template Feature', 'live-template-editor-client' ),array('cb-default-layer'), 
	
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
		
		$this->parent->register_taxonomy( 'account-option', __( 'Plan Options', 'live-template-editor-client' ), __( 'Plan Option', 'live-template-editor-client' ),  array('user-plan'), array(
			
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'css-library', __( 'CSS Libraries', 'live-template-editor-client' ), __( 'CSS Library', 'live-template-editor-client' ),  array('cb-default-layer','default-element'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> false,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'js-library', __( 'JS Libraries', 'live-template-editor-client' ), __( 'JS Library', 'live-template-editor-client' ),  array('cb-default-layer','default-element'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> false,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'font-library', __( 'Font Libraries', 'live-template-editor-client' ), __( 'Font Library', 'live-template-editor-client' ),  array('cb-default-layer','default-element'), array(
			
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> false,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));

		add_action( 'add_meta_boxes', function(){
			
			$post = get_post();
			
			if( empty($_REQUEST['post']) && !empty($_REQUEST['post_type']) && ( $_REQUEST['post_type'] == 'cb-default-layer' ) ){
				
				// remove all metaboxes except submit button
				
				global $wp_meta_boxes;
				
				$submitbox 	= $wp_meta_boxes[$post->post_type]['side']['core']['submitdiv'];

				$wp_meta_boxes[$post->post_type]['side']['core'] = array( 
					
					'submitdiv' => $submitbox
				);
				
				$wp_meta_boxes[$post->post_type]['side']['low'] 	= array();
				$wp_meta_boxes[$post->post_type]['normal'] 			= array();
				$wp_meta_boxes[$post->post_type]['advanced'] 		= array();
				
				if( $fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type ) ){
			
					// add metaboxes
					
					$this->parent->admin->add_meta_boxes($fields);
				}
			}
			elseif( $this->is_storage($post) || $this->is_local($post) || $this->is_default($post) ){
				
				$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );
				
				// remove metaboxes
				
				$layer_type = $this->get_layer_type($post);	
				
				if( $this->is_default($post) ){
					
					// remove taxonomy boxes

					if( !$this->has_html_elements($layer_type) ){
					
						remove_meta_box( 'element-librarydiv', 'cb-default-layer', 'side' );
					}
					
					if( !$this->is_html_output($layer_type->output) || !$this->is_hosted_output($layer_type->output) ){
											
						remove_meta_box( 'css-librarydiv', 'cb-default-layer', 'side' );
						
						remove_meta_box( 'js-librarydiv', 'cb-default-layer', 'side' );
						
						remove_meta_box( 'font-librarydiv', 'cb-default-layer', 'side' );
					}
				}
				
				do_action('ltple_remove_layer_metaboxes',$post,$layer_type);
				
				remove_meta_box( 'tagsdiv-layer-type', $post->post_type, 'side' );
				
				// add metaboxes
				
				$this->parent->admin->add_meta_boxes($fields);
			}
		});
		
		add_action('restrict_manage_posts', array($this, 'add_manage_layer_filters'),10,2 );
		
		add_action('current_screen', function ( $screen ) {
			
			if( $screen->id == 'edit-cb-default-layer' ){
				
				add_filter( 'months_dropdown_results', '__return_empty_array' );
			}
			
		});
		
		add_filter('pre_get_posts', array($this, 'pre_get_layers'),9999999);
		
		// default layer
		
		add_filter('manage_cb-default-layer_posts_columns', array( $this, 'set_default_layer_columns'),99999);
		add_action('manage_cb-default-layer_posts_custom_column', array( $this, 'add_layer_type_column_content'), 10, 2);
		
		add_filter('ltple_admin_editor_actions',function($editor_actions){
			
			if( !empty($_GET['post_type']) && $_GET['post_type'] == 'cb-default-layer' && current_user_can('administrator') ){
				
				$editor_actions['refresh-preview'] 	= 'Refresh Preview';
			}
			
			return $editor_actions;
		});

		// user layer
		
		add_filter('manage_user-layer_posts_columns', array( $this, 'set_user_layer_columns'),99999);
		add_action('manage_user-layer_posts_custom_column', array( $this, 'add_layer_type_column_content'), 10, 2);
		
		add_filter('ltple_default_user-layer_content', array( $this, 'filter_default_user_layer_content'),10,2 );
		add_filter('ltple_default_user-layer_css', array( $this, 'filter_default_user_layer_style'),10,2 );
		
		// user psd
		
		add_filter('manage_user-psd_posts_columns', array( $this, 'set_user_layer_columns'),99999);
		add_action('manage_user-psd_posts_custom_column', array( $this, 'add_layer_type_column_content'), 10, 2);
				
		// account option fields
		
		add_action('account-option_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );	
	
		add_filter('manage_edit-account-option_columns', array( $this, 'set_account_option_columns' ),99999 );
		add_filter('manage_account-option_custom_column', array( $this, 'add_layer_tax_column_content' ),10,3);			
	
		add_action('create_account-option', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_account-option', array( $this, 'save_layer_taxonomy_fields' ) );	
		
		// layer type fields
		
		add_action('layer-type_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
		
		add_filter('manage_edit-layer-type_columns', array( $this, 'set_layer_type_columns' ),99999 );
		add_filter('manage_layer-type_custom_column', array( $this, 'add_layer_tax_column_content' ),10,3);		
		
		add_action('create_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );	
		
		// layer range fields
		
		add_action('layer-range_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		add_filter('manage_edit-layer-range_columns', array( $this, 'set_layer_range_columns' ),99999 );
		add_filter('manage_layer-range_custom_column', array( $this, 'add_layer_tax_column_content' ),10,3);
	
		add_action('create_layer-range', array( $this, 'save_layer_taxonomy_fields' ),99999 );
		add_action('edit_layer-range', array( $this, 'save_layer_taxonomy_fields' ) );			
		
		// layer tabs
	
		add_filter('ltple_inline-css_project_tabs', array($this,'get_editable_layer_tabs'),10,2);			
		add_filter('ltple_external-css_project_tabs', array($this,'get_editable_layer_tabs'),10,2);
		add_filter('ltple_hosted-page_project_tabs', array($this,'get_hosted_layer_tabs'),10,2);
		add_filter('ltple_canvas_project_tabs', array($this,'get_editable_layer_tabs'),10,2);
		add_filter('ltple_image_project_tabs', array($this,'get_editable_layer_tabs'),10,2);
		add_filter('ltple_vector_project_tabs', array($this,'get_editable_layer_tabs'),10,2);
		
		add_filter('ltple_project_advance_tabs', array($this,'get_layer_advance_tabs'),10,3);
		
		add_filter('ltple_edit_layer_options', array($this,'add_edit_layer_options'),10,2);

		// css library fields
		
		add_action('css-library_edit_form_fields', array( $this, 'get_css_library_fields' ) );	
		add_action('create_css-library', array( $this, 'save_library_fields' ) );
		add_action('edit_css-library', array( $this, 'save_library_fields' ) );	
	
		// js library fields
		
		add_action('js-library_edit_form_fields', array( $this, 'get_js_library_fields' ) );	
		add_action('create_js-library', array( $this, 'save_library_fields' ) );
		add_action('edit_js-library', array( $this, 'save_library_fields' ) );	
		
		// font library fields
		
		add_action('font-library_edit_form_fields', array( $this, 'get_font_library_fields' ) );		
		add_action('create_font-library', array( $this, 'save_library_fields' ) );
		add_action('edit_font-library', array( $this, 'save_library_fields' ) );			
		
		// init
		
		add_filter('init', array( $this, 'init_layer' ),10);
		
		add_filter('admin_init', array( $this, 'init_layer_backend' ));
		
		/*
		add_action('wp_loaded', array($this,'get_layer_types'));
		add_action('wp_loaded', array($this,'get_layer_ranges'));
		add_action('wp_loaded', array($this,'get_account_options'));
		add_action('wp_loaded', array($this,'get_js_libraries'));
		add_action('wp_loaded', array($this,'get_css_libraries'));
		add_action('wp_loaded', array($this,'get_font_libraries'));
		//add_action('wp_loaded', array($this,'get_default_layers'));
		*/
		
		add_action( 'set_object_terms', array($this,'set_default_layer_type'), 10, 4 );
		
		add_action( 'save_post', array($this,'upload_static_contents'), 10, 3 );
		
		add_action( 'before_delete_post', array($this,'delete_static_contents'), 10, 3 );
	
		add_action( 'ltple_layer_loaded', array($this,'output_static_layer') );
		
		add_filter( 'ltple_local_layer_content', array($this,'filter_local_layer_content'),99999,2 );
			
		add_action( 'ltple_local_layer_scripts', array($this,'add_local_layer_scripts') );
		
		add_action( 'ltple_local_layer_head', array( $this, 'filter_local_layer_head'),99999,1 );
		
		add_action( 'ltple_local_layer_saved', array( $this, 'save_default_id'),10,1);
		
		add_filter( 'preview_post_link', array($this,'filter_preview_layer_link'),99999,2 );
		
		add_filter( 'ltple_preview_post_url', array($this,'filter_view_layer_link'),99999,2 );
		
		// layer parameters

		add_filter('ltple_layer_id', array($this,'get_layer_id'),10,1);
		
		add_filter('ltple_layer_output', array($this,'get_layer_editor'),10,2);
		
		add_filter('ltple_layer_is_editable', array($this,'filter_layer_is_editable'),10,2 );
	
		add_filter('ltple_layer_is_storable', array($this,'filter_layer_is_storable'),10,2 );
	
		add_filter('ltple_editor_layer',function($layer){
			
			if( isset($layer->ID) && !isset($layer->default_id) ){
				
				$layer->default_id = !$layer->is_default ? $this->get_default_id($layer->ID) : 0;
			}
			
			return $layer;
			
		},10,1);
	}
	
	public function count_layer_range($terms,$taxonomy){
		
		if( defined('WP_IMPORTING') && WP_IMPORTING === true )
			
			return;
		
		if( empty($terms) || empty($taxonomy) )
			
			return;
			
		foreach( $terms as $term ){
			
			$term_id = is_object($term) ? $term->term_id : $term;

			// count default layers
			
			$query = new WP_Query(array(
			
				'posts_per_page' 	=> 1,
				'post_type' 		=> 'cb-default-layer',
				'post_status' 		=> 'publish',
				'tax_query' 		=> array(
					
					array(
						'taxonomy' 			=> $taxonomy->name,
						'terms' 			=> $term_id,
						'field' 			=> 'id',
						'include_children'	=> false,
					)
				)
			));
			
			update_term_meta($term_id,'default_layer_count',$query->found_posts);
		}
	}
	
	public function get_local_types(){
		
		if( is_null($this->localTypes) ){
		
			$this->localTypes = apply_filters('ltple_local_post_types',array(
				
				'page',
				'post',
				'cb-default-layer',
				'default-element',
			));
		}

		return $this->localTypes;		
	}
	
	public function is_local($post){
		
		if( !isset($post->is_local) ){
			
			$is_local = false;
			
			if( is_numeric($post) ){
				
				$post = get_post($post);
			}
			
			if( !empty($post) ){
				
				$layer_type = $this->get_layer_type($post);
				
				if( !empty($layer_type->output) && $this->is_app_output($layer_type->output) ){
					
					$is_local = false;
				}
				elseif( $local_types = $this->get_local_types() ){
				
					if( in_array( $post->post_type, $local_types ) ){
					
						$is_local = true;
					}
				}
			}
			
			return $is_local;
		}
		
		return $post->is_local;
	}
	
	public function is_storage($layer){
					
		$types = $this->get_storage_types();
		
		if( isset( $types[$layer->post_type] ) )
			
			return true;
		
		return false;
	}
	
	public function is_media($layer){
					
		$types = LTPLE_Editor::get_media_types();
		
		if( isset( $types[$layer->post_type] ) )
			
			return true;
		
		return false;
	}
	
	public function is_element( $layer ){
		
		$types = LTPLE_Editor::get_element_types();
		
		if( isset( $types[$layer->post_type] ) )
			
			return true;
		
		return false;
	}
	
	public function is_public($post){
		
		$is_public = false;
		
		$post_type = '';
		
		if( is_object($post) ){
			
			if(!empty($post->post_type)){
			
				$post_type = $post->post_type;
			}
			elseif(!empty($post->name)){
				
				$post_type = $post->name;
			}
		}
		else{
			
			$post_type = $post;
		}
		
		if( $object = get_post_type_object( $post_type ) ){
			
			if( $object->publicly_queryable === true ){
				
				$is_public = true;
			}
		}
		
		return $is_public;
	}
	
	public function is_default($post){
		
		$is_default = false;
		
		$post_type = '';
		
		if( is_numeric($post) )
		
			$post = get_post($post);
		
		if( is_object($post) ){
			
			if(!empty($post->post_type)){
			
				$post_type = $post->post_type;
			}
			elseif(!empty($post->name)){
				
				$post_type = $post->name;
			}
		}
		else{
			
			$post_type = $post;
		}
		
		if( in_array( $post_type, array(
		
			'cb-default-layer',
			'default-element',
			
		)) ){
			
			$is_default = true;
		}
		
		return $is_default;
	}
	
	public function is_hosted($post){
		
		$is_hosted = false;
		
		$post_type = '';
		
		if( is_object($post) ){
			
			if(!empty($post->post_type)){
			
				$post_type = $post->post_type;
			}
			elseif(!empty($post->name)){
				
				$post_type = $post->name;
			}
		}
		else{
			
			$post_type = $post;
		}
		
		if( !in_array( $post_type, array(
		
			'user-layer',
			'user-psd',
		)) ){
			
			$is_hosted = true;
		}
	
		return $is_hosted;
	}
	
	public function count_layers_by($type = 'storage'){
		
		if( $type == 'storage' ){
			
			return $this->count_layers_by_storage();
		}
		
		dump( 'missing count_layer_by : ' . $type );
		
		return false;
	}
	
	public function count_layers_by_storage(){
		
		if( is_null($this->storage_count) ){
			
			$storage_count = array();
			
			if( $types = $this->get_layer_types() ){
				
				foreach( $types as $type ){
					
					if( empty($type->ranges) || $type->visibility != 'anyone' )
						
						continue;
					
					foreach( $type->ranges as $range ){
						
						$count = intval($range['count']);
						
						if( !isset($storage_count[$type->storage]) ){
							
							$storage_count[$type->storage] = $count;
						}
						else{
							
							$storage_count[$type->storage] += $count;
						}
					}
				}
			}
			
			$this->storage_count = $storage_count;
		}
		
		return $this->storage_count;
	}	
	
	public function can_customize_url($post){
		
		$can_customize_url = false;
		
		$post_type = '';
		
		if( is_object($post) ){
			
			if(!empty($post->post_type)){
			
				$post_type = $post->post_type;
			}
			elseif(!empty($post->name)){
				
				$post_type = $post->name;
			}
		}
		else{
			
			$post_type = $post;
		}
		
		if( $this->is_public($post) && $this->is_hosted($post) && !in_array( $post_type, array(
		
			'user-post',
			'user-product',
		)) ){
			
			$can_customize_url = true;
		}
	
		return $can_customize_url;
	}
	
	public function get_default_layer_fields($fields,$post=null){
		
		if( empty($this->defaultFields) ){		
			
			//get post
			
			if( $layer = LTPLE_Editor::instance()->get_layer($post) ){
				
				$storage_name = $layer->post_type == 'default-element' ? 'Element' : 'Template';
				
				if( $taxonomies = get_object_taxonomies($layer->post_type) ){
					
					if( in_array('layer-range',$taxonomies) ){
						
						$this->defaultFields[] = array(
						
							'metabox' => array( 
							
								'name' 		=> 'layer-rangediv',
								'title' 	=> __( $storage_name . ' Range', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'side',
								'taxonomy'	=> 'layer-range',
								'frontend'	=> false,
							),
							'type'		=> 'dropdown_categories',
							'id'		=> 'layer-range',
							'name'		=> 'tax_input[layer-range][]',
							'taxonomy'	=> 'layer-range',
							'callback' 	=> array($this,'get_layer_range_id'),
							'description'=>''
						);
					}
				}
				
				if( $layer->post_type == 'cb-default-layer' ){

					$this->defaultFields[] = array(
					
						'metabox' => array( 
						
							'name' 		=> 'layer-gallery',
							'title' 	=> __( 'Gallery Images', 'live-template-editor-client' ), 
							'screen'	=> array($layer->post_type),
							'context' 	=> 'side',
							'frontend'	=> false,
						),
						
						'type'			=> 'gallery',
						'id'			=> 'layer-gallery',
					);
				}
				
				//get layer type
				
				$layer_type = $this->get_layer_type($layer);	
				
				if( !empty($layer_type->output) ){
					
					if( $this->is_html_output($layer_type->output) ){
					
						// get layer content
						
						$metabox = array(
							
							'name' 		=> 'layer-content',
							'title' 	=> __( $storage_name . ' HTML', 'live-template-editor-client' ), 
							'screen'	=> array($layer->post_type),
							'context' 	=> 'advanced',
							'add_new'	=> false,
						);
						
						$this->defaultFields[]=array(
						
							'metabox' 		=> $metabox,
							'id'			=> 'layerContent',
							'type'			=> 'code_editor',
							'code'			=> 'html',
							'placeholder'	=> 'HTML content',
							'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_content','',$layer),
						);
						
						if( $layer_type->storage == 'user-theme' ){

							$this->defaultFields[]=array(
							
								'metabox' 		=> $metabox,
								'id'			=> 'layerContentVarsTable',
								'type'			=> 'html',
								'data'			=> '<table class="widefat striped" style="border:1px solid #eee;">
									<tr>
										<th colspan="2">
											<b>Default HTML Variables</b>
										</th>
									</tr>
									<tr>
										<td>
											Profile
										</td>
										<td>
											{{ profile.avatar.url }} 
											<br>
											{{ profile.banner.url }}
										</td>
									</tr>
									<tr>
										<td>
											Site
										</td>
										<td>
											{{ site.name }}
										</td>
									</tr>
									<tr>
										<td>
											Theme
										</td>
										<td>
											{{ theme.navbar }}
										</td>
									</tr>
									<tr>
										<td>
											Page
										</td>
										<td>
											{{ page.content }}
										</td>
									</tr>
								</table>',
							);
						}
						
						if( $layer_type->output != 'inline-css' ){
							
							// get layer css
							
							$metabox = array(
								
								'name' 		=> 'layer-css',
								'title' 	=> __( $storage_name . ' CSS', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'advanced',
								'add_new'	=> false,
							);
							
							$this->defaultFields[]=array(
							
								'metabox' 		=> $metabox,
								'id'			=> 'layerCss',
								'type'			=> 'code_editor',
								'code'			=> 'css',
								'stripcslashes'	=> false,
								'htmlentities'	=> false,
								'placeholder'	=> 'CSS rules without '.htmlentities('<style></style>'),
								'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_css','',$layer),
								'description'	=> ''
							);
							
							$this->defaultFields[]=array(
							
								'metabox' 		=> $metabox,
								'id'			=> 'layerCssVarsTable',
								'type'			=> 'html',
								'data'			=> '<table class="widefat striped" style="border:1px solid #eee;">
									<tr>
										<th colspan="2">
											<b>Default CSS Variables</b>
										</th>
									</tr>
									<tr>
										<td>
											Theme
										</td>
										<td>
											{{ theme.css.main_color }}
											<br>
											{{ theme.css.navbar_color }}
											<br>
											{{ theme.css.link_color }}
										</td>
									</tr>
								</table>',
							);
							
							if( $layer_type->storage == 'user-theme' ){
								
								$this->defaultFields[]=array(
								
									'metabox' 		=> $metabox,
									'id'			=> 'layerCssVars',
									'type'			=> 'form',
									'inputs'		=> array(
									
										'select',
										'color',
									),
									'default' 		=> apply_filters('ltple_default_layer_css_variables_form_data',array(),$layer_type),
									'description'	=> '',
								);
							}
						}
						
						if( $layer->post_type == 'cb-default-layer' ){
							
							if( $this->is_hosted_output($layer_type->output)  ){		
								
								$this->defaultFields[]=array(
								
									'metabox' => array(
									
										'name' 		=> 'layer-js',
										'title' 	=> __( $storage_name . ' JS', 'live-template-editor-client' ), 
										'screen'	=> array($layer->post_type),
										'context' 	=> 'advanced'
									),
									
									'id'			=> 'layerJs',
									'type'			=> 'code_editor',
									'code'			=> 'javascript',
									'placeholder'	=> 'Additional Javascript',
									'stripcslashes'	=> false,
									'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_js','',$layer),
									'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
								);
								
								if( $this->is_public_output($layer_type->output) ){
								
									$this->defaultFields[]=array(
									
										'metabox' => array(
										
											'name' 		=> 'layer-meta',
											'title' 	=> __( $storage_name . ' Meta Data', 'live-template-editor-client' ), 
											'screen'	=> array($layer->post_type),
											'context' 	=> 'advanced'
										),
										'id'			=> 'layerMeta',
										'type'			=> 'code_editor',
										'code'			=> 'json',
										'placeholder'	=> 'JSON',
										'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_json','',$layer),
										'description'	=> '<i>Additional Meta Data</i>'
									);	
								}
							}
							elseif( $layer_type->output == 'inline-css' ){
								
								$this->defaultFields[]=array(
								
									'metabox' => array(
									
										'name' 		=> 'layer-margin',
										'title' 	=> __( 'Editor Margin', 'live-template-editor-client' ), 
										'screen'	=> array($layer->post_type),
										'context' 	=> 'side',
										'add_new'	=> false,
									),
									
									'id'			=> "layerMargin",
									'type'			=> 'text',
									'placeholder'	=> '0px auto',
									'default'		=> '',
								);	
							}
							
							if( $this->has_html_elements($layer_type) ){
								
								$this->defaultFields[]=array(
								
									'metabox' => array(
									
										'name' 		=> 'layer-elements',
										'title' 	=> __( 'Template Elements', 'live-template-editor-client' ), 
										'screen'	=> array($layer->post_type),
										'context' 	=> 'advanced',
										'frontend' 	=> false,
									),
									
									'id'	=> "layerElements",
									'name'	=> "layerElements",
									'type'	=> 'element',
								);
							}
						}
						elseif( $layer->post_type == 'default-element' ){
							
							$metabox = array(
								
								'name' 		=> 'element-options',
								'title' 	=> __( $storage_name . ' Options', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'advanced',
								'add_new'	=> false,
							);
							
							$this->defaultFields[]=array(
							
								'metabox' 		=> $metabox,
								'id'			=> 'elementType',
								'label'			=> 'Element Section',
								'type'			=> 'select',
								'options'		=> $this->parent->element->get_default_sections(),
								'description'	=> '',
							);
							
							$this->defaultFields[]=array(
							
								'metabox' 		=> $metabox,
								'id'			=> 'elementDrop',
								'label'			=> 'Target Drop',
								'type'			=> 'select',
								'options'		=> array(
									
									'out'	=> 'Out',
									'in'	=> 'In',
								),
							);	
						}
					}
					elseif( $this->is_image_output($layer_type->output) ){
						
						$this->defaultFields[]=array(
						
							'metabox' => array(
							
								'name' 		=> 'layer-image-url',
								'title' 	=> __( 'Image Template', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'advanced'
							),
							
							'id'			=> 'layerImageTpl',
							'type'			=> 'file',
							'label'			=> '<b>Upload File</b>',
							'accept'		=> '.psd,.xfc,.sketch',
							'script'		=> 'jQuery(document).ready(function($){$(\'form#post\').attr(\'enctype\',\'multipart/form-data\');});',
							'placeholder'	=> "image.psd",
							'style'			=> "padding:5px;margin: 15px 0 5px 0;",
							'description'	=> "Upload an image template ( Photoshop, GIMP, Sketch )",
						);						
					}
					elseif( $this->is_vector_output($layer_type->output) ){
					
						// get layer content
						
						$this->defaultFields[]=array(
						
							'metabox' => array(
							
								'name' 		=> 'layer-json',
								'title' 	=> __( 'Template JSON', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'advanced',
								'add_new'	=> false,
							),
							
							'id'			=> 'layerJson',
							'type'			=> 'code_editor',
							'code'			=> 'json',
							'placeholder'	=> 'JSON content',
						);
					}
					
					if( $layer->post_type == 'cb-default-layer' ){
						
						if( $layer_type->output == 'inline-css' || $layer_type->output == 'external-css' ){
							
							$this->defaultFields[]=array( 
							
								'metabox' => array(
								
									'name' 		=> 'layer-form',
									'title' 	=> __( 'Template Action', 'live-template-editor-client' ), 
									'screen'	=> array($layer->post_type),
									'context' 	=> 'side',
									'frontend' 	=> false,
								),
								'id'			=> 'layerForm',
								'type'			=> 'radio',
								'options'		=> array(
								
									'none'		=> 'None',
									'importer'	=> 'Importer',
								),
								'inline'		=> false,
							);					
						}
						
						$this->defaultFields[]=array( 
						
							'metabox' => array(
							
								'name' 		=> 'layer-visibility',
								'title' 	=> __( 'Template Visibility', 'live-template-editor-client' ), 
								'screen'	=> array($layer->post_type),
								'context' 	=> 'side',
								'frontend' 	=> false,
							),	
							'id'			=> 'layerVisibility',
							'type'			=> 'radio',
							'options'		=> array(
							
								'subscriber'	=> 'Subscriber (plan)',
								'assigned'		=> 'Assigned (tailored)',
								'registered'	=> 'Registered (loggedin)',
								'anyone'		=> 'Anyone',
							),
							'inline'		=> false,
							'description'	=> ''
						);
					}
				}
				
				do_action('ltple_default_layer_fields',$layer_type,$layer);
			}
		}
		
		return $this->defaultFields;
	}
	
	public function filter_layer_is_editable($is_editable,$post){
		
		if( $this->is_default($post) || $this->is_storage($post) ){
			
			return $this->is_editable_output($post->ID,$is_editable);
		}
		
		return $is_editable;
	}
	
	public function is_editable_output($output,$is_editable = false){
		
		if( is_numeric($output) ){
			
			if( $layer_type = $this->get_layer_type($output) ){
		
				$output = $layer_type->output;
			}
		}
		
		if( $this->is_html_output($output) ){
			
			$is_editable = true;
		}
		elseif( $this->is_image_output($output) ){
			
			$is_editable = true;
		}
		elseif( $this->is_vector_output($output) ){
			
			$is_editable = true;
		}
		
		return apply_filters('ltple_editable_' . $output,$is_editable);
	}
	
	public function filter_layer_is_storable($is_storable,$post){
		
		if( $this->is_default($post) || $this->is_storage($post) ){

			return $this->is_storable_output($post->ID,$is_storable);
		}
		
		return $is_storable;
	}

	public function is_storable_output($output,$is_storable = true){
		
		if( is_numeric($output) ){
			
			if( $layer_type = $this->get_layer_type($output) ){
		
				$output = $layer_type->output;
			}
		}
		
		return apply_filters('ltple_storable_' . $output,$is_storable);
	}
	
	public function is_html_output($output){
		
		$outputs = apply_filters('ltple_layer_html_output',array(
			
			'inline-css',
			'external-css',
			'hosted-page',
			'canvas',
			'web-app',
		));
		
		if( in_array($output,$outputs) ){
			
			return true;
		}
		
		return false;
	}
	
	public function is_vector_output($output){
		
		$outputs = apply_filters('ltple_layer_vector_output',array(
			
			'vector',
		));
		
		if( in_array($output,$outputs) ){
			
			return true;
		}
		
		return false;
	}
	
	public function has_html_elements($layer_type){
		
		if( $layer_type->storage == 'user-element' || $layer_type->storage == 'web-app' )
			
			return false;
		
		$outputs = apply_filters('ltple_layer_html_elements',array(
		
			'inline-css',
			'external-css',
			'hosted-page',
			'canvas',
		));
		
		if( in_array($layer_type->output,$outputs) ){
			
			return true;
		}
		
		return false;
	}
	
	public function is_hosted_output($output){
		
		if( $this->is_app_output($output) ){
			
			return true;
		}
		else{
			
			$hosted_output = apply_filters('ltple_layer_hosted_output',array(
			
				'hosted-page',
			));
			
			if( in_array($output,$hosted_output) ){
				
				return true;
			}
		}
		
		return false;
	}
	
	public function is_app_output($output){
		
		$app_output = apply_filters('ltple_layer_app_output',array(
		
			'web-app',
			'vector',
			
		));
		
		if( in_array($output,$app_output) ){
			
			return true;
		}
		
		return false;
	}
	
	public function is_public_output($output){
					
		$public_output = apply_filters('ltple_layer_public_output',array(
		
			'hosted-page',
		));
		
		if( in_array($output,$public_output) ){
			
			return true;
		}
		
		return false;
	}
		
	public function is_downloadable_output($output){
		
		$is_downloadable = apply_filters('ltple_downloadable_' . $output,false);

		return $is_downloadable;
	}
	
	public function is_image_output($output){
					
		$image_output = apply_filters('ltple_layer_image_output',array(
		
			'image',
		));
		
		if( in_array($output,$image_output) ){
			
			return true;
		}
		
		return false;
	}
	 
	public function has_preview($output){
		
		$has_preview = false;
		
		if( 	$this->is_html_output($output)
			|| 	$this->is_image_output($output)
			|| 	$this->is_vector_output($output) 
			|| 	$this->is_public($output)
		){
			
			$has_preview = true;
		}
		
		return apply_filters('ltple_layer_has_preview',$has_preview,$output);
	}
	
	public function get_project_tabs($layer,$fields=array()){
		
		$tabs = array();
		
		if( $layer_type = $this->get_layer_type($layer) ){

			if( $image = $this->get_image_tab_content($layer) ){

				$tabs['image'] = array(
				
					'name' 		=> 'Image',
					'slug'		=> 'image',
					'content'	=> $image,
				);
				
				$tabs = apply_filters('ltple_project_image_tabs',$tabs,$layer,$fields);
			}
			
			$tabs = apply_filters('ltple_project_advance_tabs',$tabs,$layer,$fields);

			$tabs = apply_filters('ltple_' . $layer_type->output . '_project_tabs',$tabs,$layer,$layer_type);

			if( $installation = $this->get_installation_info($layer) ){
				
				$tabs['install'] = array(
				
					'name' 		=> '<i class="fa fa-cloud-download-alt" aria-hidden="true"></i> Install',
					'slug'		=> 'install',
					'content'	=> $installation,
				);			
			}		
		}
		
		return $tabs;
	}
	
	public function get_image_tab_content($layer){
		
		$tab = '';
		
		if( $this->is_public_output($this->layerOutput) ){
			
			// image preview
			
			$media_url = add_query_arg( array(
			
				'output' 	=> 'widget',
				'section' 	=> 'images',
				
			), $this->parent->urls->media . 'user-images/' );
			
			$md5 = md5($media_url);
			
			$modal_id 	= 'modal_' . $md5;
			$preview_id = 'preview_' . $md5;
			$input_id 	= 'input_' . $md5;
			
			$tab .= '<div class="col-md-6">';
				
				$tab .= '<div style="padding:5px 0px;">';
					
					$tab .= '<b class="pull-left" style="margin:5px 5px 5px 0;">Featured Image :</b>';
					
					$tab .= '<button type="button" onclick="return false;" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#'.$modal_id.'">Edit</button>';

				$tab .= '</div>';
				
				$tab .= '<div style="text-align:left;margin-top:10px;">';
					
					$tab .= '<img loading="lazy" id="'.$preview_id.'" src="'.$this->get_thumbnail_url($layer).'" style="width:auto;"/>';
					
					$tab .= '<input type="hidden" id="'.$input_id.'" name="image_url" value="" />';
					
					$tab .= '<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
						
						$tab .= '<div class="modal-dialog modal-lg" role="document" style="margin:0;width:100% !important;position:absolute;">'.PHP_EOL;
							
							$tab .= '<div class="modal-content">'.PHP_EOL;
								
								$tab .= '<div class="modal-header">'.PHP_EOL;
									
									$tab .= '<button type="button" class="close m-0 p-0 border-0 bg-transparent" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
									
									$tab .= '<h4 class="modal-title text-left" id="myModalLabel">Media Library</h4>'.PHP_EOL;
								
								$tab .= '</div>'.PHP_EOL;

								$tab .= '<iframe id="iframe_'.$modal_id.'" data-src="' . $media_url . '" data-input-id="#' . $input_id . '" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';						
								
								wp_register_script( $this->parent->_token . '-image-tab', '', array( 'jquery' ) );
								wp_enqueue_script( $this->parent->_token . '-image-tab' );
								wp_add_inline_script( $this->parent->_token . '-image-tab', $this->get_image_tab_script($input_id,$preview_id) );
								
							$tab .= '</div>'.PHP_EOL;
							
						$tab .= '</div>'.PHP_EOL;
						
					$tab .= '</div>'.PHP_EOL;
				
				$tab .= '</div>'.PHP_EOL;
				
			$tab .= '</div>'.PHP_EOL;
		}
		
		return $tab;
	}
	
	public function get_image_tab_script($input_id,$preview_id){

		$script = ';(function($){';

			$script .= '$(document).ready(function(){
				
				$("#'.$input_id.'").on("change", function(e){
					
					$("#'.$preview_id.'").attr("src",$(this).val());
				});
			
			});';
		
		$script .= '})(jQuery);';

		return $script;
	}
	
	public function get_installation_info($layer){
		
		// get steps

		$steps = array();
		
		$layer_type = $this->get_layer_type($layer);
		
		if( !empty($layer_type->term_id) ){
			
			if( $install = get_term_meta($layer_type->term_id,'installation',true) ){
				
				$title =  $this->get_output_name($layer_type->output) . ' installation for ' . $layer_type->name;
				
				$steps[$title] = apply_filters('the_content',$install);
			}
			
			$steps = apply_filters('ltple_layer_installation_steps',$steps,$layer);
		}
		
		// get content
				
		$html 	= '';
		$expanded 	= 'true';
		
		if( !empty($steps) ){
			
			$html .= '<div id="install_info" style="line-height:30px;">';

				foreach( $steps as $title => $content ){
					
					$slug = sanitize_title('step_'.$title);
					
					$html .= '<div style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" role="tab" id="heading_'.$slug.'">';
						
						$html .= '<button style="display:block;background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:15px;border:none;" role="button" data-toggle="collapse" data-parent="#install_info" data-target="#collapse_'.$slug.'" aria-expanded="'.$expanded.'" aria-controls="collapse_'.$slug.'">';
						  
							$html .= $title . ' ';
							
							$html .= '<i class="fas fa-angle-down pull-right" style="font-size:25px;"></i>';
						
						$html .= '</button>';
					
					$html .= '</div>';
					
					$html .= '<div style="margin:10px;" id="collapse_'.$slug.'" class="panel-collapse '.( $expanded === 'true' ? 'in show' : 'collapse' ).'" role="tabpanel" aria-labelledby="heading_'.$slug.'">';
						
						$html .= $content;
						
					$html .='</div>';
					
					$expanded = 'false';
				}
				
			$html .= '</div>';
		}
		
		return $html;
	}
		
	public function get_blocks_info($layer){
		
		$html = '';
		
		if( !empty($layer) ){
			
			if( $libraries = $this->get_libraries($layer->ID,'element') ){
				
				$blocks = array();
				$added	= array();
				
				foreach( $libraries as $library ){
					
					$elements = $this->parent->element->get_library_elements($library);
					
					if( !empty($elements['name']) ){

						foreach( $elements['name'] as $i => $name ){
							
							$image 	= $elements['image'][$i];
							$type 	= $elements['type'][$i]; 
							
							if( !isset($added[$type]) ){
								
								$added[$type] = array();
							}
							
							if( !in_array($name,$added[$type]) ){
							
								if( !isset($blocks[$type]) ){
									
									$blocks[$type] = '';
								}
									
								$blocks[$type] .= '<div class="col-xs-6 col-sm-4 col-md-2">';
									
									$blocks[$type] .= '<div class="panel-body" style="padding:15px 0 15px 15px;">';
										
										$blocks[$type] .= '<b>' . $name . '</b>';
									
									$blocks[$type] .= '</div>';								
									
									$blocks[$type] .= '<div class="media_wrapper" style="max-height:150px;">';
										
										$blocks[$type] .= '<img loading="lazy" class="lazy" data-original="'.$image.'" src="'.$image.'">';
									
									$blocks[$type] .= '</div>';

								$blocks[$type] .= '</div>';

								$added[$type][] = $name;
							}
						}
					}
				}

				if( !empty($blocks) ){
					
					$expanded = 'false'; // keep all collapsed
					
					$html .= '<div id="blocks_info">';

						foreach( $blocks as $type => $content ){
							
							$slug = sanitize_title('blocks_'.$type);
							
							$html .= '<div class="panel panel-default" style="border-bottom:1px solid #DDDDDD;background:rgb(252, 252, 252);" role="tab" id="heading_'.$slug.'">';
								
								$html .= '<button style="display:block;pointer:cursor;background:none;text-align:left;font-size:15px;font-weight:bold;width:100%;padding:15px;border:none;" role="button" data-toggle="collapse" data-parent="#install_info" data-target="#collapse_'.$slug.'" aria-expanded="'.$expanded.'" aria-controls="collapse_'.$slug.'">';
								  
									$html .= '<div style="width:40px;display:inline-block;">';
									
										$html .= '<span class="badge" style="margin-left:5px;font-size:11px;">'.count($added[$type]).'</span> ';
									
									$html .= '</div>';
									
									$html .= ' ' . ucfirst($type) . '</span> ';
									
									$html .= '<i class="fas fa-angle-down pull-right" style="font-size:25px;"></i>';
								
								$html .= '</button>';
							
							$html .= '</div>';
							
							$html .= '<div id="collapse_'.$slug.'" class="panel-collapse '.( $expanded === 'true' ? 'in show' : 'collapse' ).'" role="tabpanel" aria-labelledby="heading_'.$slug.'">';
								
								$html .='<div class="row" style="margin-top:10px;">';
								
									$html .= $content;
									
								$html .='</div>';
								
							$html .='</div>';
							
							$expanded = 'false';
						}
						
					$html .= '</div>';
				}
			}
		}
		
		return $html;
	}
			
	public function get_hosted_layer_tabs($tabs,$layer){
		
		$tabs = $this->get_editable_layer_tabs($tabs,$layer);
				
		return $tabs;
	}
	
	public function get_editable_layer_tabs($tabs,$layer){

		$edit = '<div style="background:#fbfbfb;border:1px solid #eee;padding:152px 0;text-align:center;">'; 
	
			$edit .= '<a class="btn btn-lg btn-primary" href="' . $this->parent->urls->edit . '?uri=' . $layer->ID . '">Open in Editor</a>';

		$edit .= '</div>';
	
		$tabs['edit'] = array(
		
			'name' 		=> 'Edit',
			'slug'		=> 'edit',
			'content'	=> $edit,
		
		);

		return $tabs;
	}
	
	public function get_layer_advance_tabs($tabs,$layer,$fields){
		
		if( !empty($fields) ){
			
			foreach ( $fields as $field ) {
				
				if( !isset($field['metabox']['frontend']) || $field['metabox']['frontend'] === true ){

					$slug = $field['metabox']['name'];
					
					if( !isset($tabs[$slug]) ){
						
						$tabs[$slug]  = array(
						
							'name' 		=> 	$field['metabox']['title'],
							'slug'		=>	$slug,
							'content'	=> 	$this->parent->admin->display_meta_box_field( $field, $layer, false),			
						);
					}
					else{
						
						$tabs[$slug]['content'] .= $this->parent->admin->display_meta_box_field( $field, $layer, false);
					}
				}
			}			
		}
		
		return $tabs;		
	}
	
	public function filter_preview_layer_link($url,$post){
		
		if( $post->post_type == 'cb-default-layer' ){
			
			if( $post->post_status == 'publish' ){
			
				$url = $this->parent->urls->home . '/preview/' . $post->post_name . '/';
			}
			else{
				
				$url = add_query_arg(array(
					
					'post_type' => $post->post_type,
					'p' 		=> $post->ID,
					'preview' 	=> 'true',
					'_'			=> time(),
					
				),get_home_url());
			}
		}

		return $url;
	}
	
	public function filter_view_layer_link($url,$post){
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}
		
		if( $post->post_type == 'cb-default-layer' ){
			
			$layer_type = $this->get_layer_type($post);
			
			if( $this->is_app_output($layer_type->output) ){
				
				return false;
			}
		}
		
		return $url;
	}
	
	public function get_layer_id($id){
		
		if( isset($_GET['uri']) ){
			
			$id = intval($_GET['uri']);
		}
		elseif( is_user_logged_in() && !is_admin() && !empty($_GET['p']) && !empty($_GET['preview']) && !empty($_GET['post_type']) ){
			
			// get id from preview url
			
			if( $post = get_post(intval($_GET['p'])) ){
				
				$user = wp_get_current_user();
				
				if( intval($post->post_author) == $user->ID ){
				
					$id = $post->ID;
				}
			}
		}
		elseif( $post = $this->parent->profile->get_profile_post() ){
			
			$id = $post->ID;
		}
		elseif( !is_admin() ){
			
			if( !empty($_SERVER['REQUEST_URI']) ){
				
				if( $_SERVER['REQUEST_URI'] == '/' ){
					
					if( !defined('REW_LTPLE') || REW_LTPLE == 'client' ){
						
						$id = get_option('page_on_front');
					}
				}
				elseif( $post = get_page_by_path($_SERVER['REQUEST_URI']) ){
					
					$id = $post->ID;
				}
			}
		}
		else{
			
			$id = get_the_ID();
		}
		
		return $id;
	}
	
	public function add_edit_layer_options($layer,$post_type){
		
		
	}
	
	public function get_user_projects($user_id,$layer_type){
		
		$user_projects = array();
		
		if( $projects = get_posts(array(
			
			'post_type' 	=> $layer_type->storage,
			'author' 		=> $user_id,
			'post_status' 	=> array('publish','draft'),
			'numberposts'	=> -1,
			
		))){
			
			foreach( $projects as $project ){
				
				$project->type = $this->get_layer_type($project);
				
				if( !empty($project->type->slug) && $project->type->slug == $layer_type->slug ){
				
					$user_projects[] = $project;
				}
			}
		}

		return $user_projects;
	}

	public function get_user_layer_fields($fields,$post=null){
		
		// TODO move to LTPLE plugin
	
		if( empty($this->userFields) ){
			
			//get post

			if( empty($post) ){
				
				if( is_admin() ){
					
					$post_id = get_the_ID();
				}
				elseif( !empty($_GET['id']) ){
			
					$post_id = intval($_GET['id']);
				}
				
				if( !empty($post_id) ){
					
					$post = get_post($post_id);
				}
			}
			
			if( $layer = LTPLE_Editor::instance()->get_layer($post) ){

				$layer_type = $this->get_layer_type($layer);
				
				$post_type = get_post_type_object($layer->post_type);
		
				$storage_name = isset($post_type->labels->singular_name) ? $post_type->labels->singular_name  : 'template';
				
				if( $this->is_html_output($layer_type->output) ){
					
					$metabox = array( 
			
						'name' 		=> 'layer-code',
						'title' 	=> 'Code',
						'screen'	=> array($layer->post_type),
						'context' 	=> 'advanced',
						'frontend'	=> $this->is_hosted_output($layer_type->output) || $layer_type->output == 'canvas' ? false : true,
					);
						
					$this->userFields[] = array(
					
						'metabox' 		=> $metabox,
						'type'			=> 'code_editor',
						'code'			=> 'html',
						'id'			=> 'layerContent',
						'label'			=> __( $storage_name . ' HTML', 'live-template-editor-client' ),
						'placeholder'	=> "HTML content",
						'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_content','',$layer),
						'description'	=>''
					);
					
					if( $layer_type->output != 'inline-css' ){
						
						$this->userFields[] = array(
						
							'metabox' 		=> $metabox,
							'type'			=> 'code_editor',
							'code'			=> 'css',
							'stripcslashes'	=> false,
							'htmlentities'	=> false,
							'id'			=> 'layerCss',
							'label'			=> __( $storage_name . ' CSS', 'live-template-editor-client' ),
							'placeholder'	=> "Internal CSS style sheet",
							'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_css','',$layer),
							//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
						);
					}
					
					if( $this->is_hosted($layer) ){
					
						$this->userFields[] = array(
						
							'metabox' 		=> $metabox,
							'type'			=> 'code_editor',
							'code'			=> 'js',
							'id'			=> 'layerJs',
							'label'			=> __( $storage_name . ' JS', 'live-template-editor-client' ),
							'placeholder'	=> "Additional Javascript",
							'default'		=> apply_filters('ltple_default_' . $layer_type->storage . '_js','',$layer),
							'stripcslashes'	=> false,
							'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
						);
					}
				}
				elseif( $this->is_vector_output($layer_type->output) ){
					
					// get layer content
					
					$this->userFields[]=array(
					
						'metabox' => array(
						
							'name' 		=> 'layer-json',
							'title' 	=> __( 'Template JSON', 'live-template-editor-client' ), 
							'screen'	=> array($layer->post_type),
							'context' 	=> 'advanced',
							'frontend'	=> false,
							'add_new'	=> false,
						),
						
						'id'			=> 'layerJson',
						'type'			=> 'code_editor',
						'code'			=> 'json',
						'placeholder'	=> 'JSON content',
					);
				}
					
				do_action('ltple_user_layer_fields',$layer,array( 
					
					'name' 		=> 'ltple-settings',
					'title' 	=> __( $storage_name . ' Editor Settings', 'live-template-editor-client' ), 
					'screen'	=> array($layer->post_type),
					'context' 	=> 'advanced',
					'frontend'	=> false,
				));
			}
		}
		
		return $this->userFields;
	}
	
	public function get_layer_editors(){
		
		if( empty($this->editors) ){

			$this->editors = apply_filters('ltple_layer_editors',array(
					
				'inline-css'	=>'HTML',
				'external-css'	=>'HTML + CSS',
				'hosted-page'	=>'Hosted',
				'canvas'		=>'HTML to PNG',
				'image'			=>'Image',
				'web-app'		=>'Standalone',
			));
		} 
		
		return $this->editors;
	}
	
	public function get_output_name($output){
		
		$editor_name = 'Template';
		
		if( !empty($output) ){
			
			$editors = $this->get_layer_editors();
			
			if( !empty($editors[$output]) ){
				
				$editor_name = $editors[$output];
				
				if( $this->is_html_output($output) ){
					
					if( $output == 'web-app' ){
						
						$editor_name .= ' application';
					}
					else{
					
						$editor_name .= ' template';
					}
				}
			}
		}
		
		return $editor_name;
	}
	
	public function get_storage_types(){
		
		if( is_null($this->storageTypes) ){
		
			$this->storageTypes = apply_filters('ltple_layer_storages',array(
					
				'user-layer'	=>'HTML Template',
				'user-element'	=>'HTML Element',
				'user-psd'		=>'Image Design',
			));
		}
		
		return $this->storageTypes;
	}	
	
	public function is_layer_part($storage){
		
		return in_array($storage,apply_filters('ltple_layer_parts',[]));
	}
	
	public function get_gallery_sections(){
		
		if( is_null($this->sections) ){
			
			$this->sections = array();
			
			if( $sections = $this->get_terms( 'gallery-section', array())){
				
				foreach( $sections as $section ){
					
					$this->sections[$section->term_id] = $section;
				}
			}
		}

		return $this->sections;
	}
	
	public function get_type_addon_range($term){
		
		$term_id = 0;
		
		if( is_object($term) && !empty($term->term_id) ){
			
			$term_id = $term->term_id;
		}
		elseif( is_numeric($term) ){
			
			$term_id = intval($term);
		}
		elseif($term = get_term_by('slug',$term,'layer-type')){
			
			$term_id = $term->term_id;
		}
		
		$addon = null;
		
		if( $term_id > 0 ){
		
			$id = intval(get_term_meta($term_id,'addon_range',true));
			
			if( $id > 0 ){
				
				$addon = get_term_by('id',$id,'layer-range');
			}
		}
		
		return $addon;
	}
	
	public function get_type_gallery_section($term){
		
		$term_id = 0;
		
		if( is_object($term) && !empty($term->term_id) ){
			
			$term_id = $term->term_id;
		}
		elseif( is_numeric($term) ){
			
			$term_id = intval($term);
		}
		elseif($term = get_term_by('slug',$term,'layer-type')){
			
			$term_id = $term->term_id;
		}
		
		$gallery_section = null;
		
		if( $term_id > 0 ){
		
			$id = intval(get_term_meta($term_id,'gallery_section',true));
			
			if( $id > 0 ){
				
				$this->sections = $this->get_gallery_sections();
				
				if( isset($this->sections[$id]) ){
					
					$gallery_section = $this->sections[$id];
				}
			}
		}
		
		return $gallery_section;
	}
	
	public function get_layer_types(){
		
		if( is_null($this->types) ){
			
			$current_types	= array();
			
			if( $types = $this->get_terms('layer-type') ){
			
				foreach( $types as $term ){
					
					if( $term->storage = $this->get_type_storage($term) ){
											
						// todo move visibility to term meta
							
						$term->visibility = $this->get_type_visibility($term);

						$term->output = $this->get_type_output($term);

						$term->gallery_section = $this->get_type_gallery_section($term);
						
						$term->addon = $this->get_type_addon_range($term);
						
						$term->ranges = $this->get_type_ranges($term,$term->addon);

						$term->count = 0;
						
						foreach( $term->ranges as $range ){
							
							$term->count += $range['count'];
						}

						$current_types[] = $term;
					}
				}
			}
			
			$this->types = array();
			
			if( !empty($current_types) ){
			
				// order by count
				
				$counts = array();
				
				foreach( $current_types as $key => $type ){
					
					$counts[$key] = $type->count;
				}
				
				array_multisort($counts, SORT_DESC, $current_types);
				
				foreach( $current_types as $type ){
					
					$this->types[$type->term_id] = $type;
				}
			}
		}
		
		return $this->types;
	}
	
	public function get_type_ranges($layer_type,$addon=null){
		
		$ranges = [];

		// get layer ranges

		$exclude = !empty($addon->term_id) ? $addon->term_id : '';
		
		if( $terms = get_terms( array(
		
			'taxonomy'		=> 'layer-range',
			'hide_empty' 	=> false,
			'exclude'		=> $exclude,
			'meta_query'	=> array( 
				
				array(
			
					'key' 	 	=> 'range_type',
					'value' 	=> $layer_type->term_id,
					'compare'   => '=',
				)
			)
		)) ){
			
			foreach( $terms as $range ){

				$meta = get_term_meta($range->term_id);
				
				$ranges[$range->slug]['term_id'] 	= $range->term_id;
				$ranges[$range->slug]['name'] 		= $range->name;
				$ranges[$range->slug]['slug'] 		= $range->slug;
				$ranges[$range->slug]['short'] 		= !empty($meta['shortname'][0]) ? $meta['shortname'][0] : $range->name;
				$ranges[$range->slug]['count'] 		= !empty($meta['default_layer_count'][0]) ? $meta['default_layer_count'][0] : 0;
				$ranges[$range->slug]['taxonomy'] 	= $range->taxonomy;
			}
		}
		
		if( !empty($this->parent->user->user_email) &&  !empty($addon) ){
			
			// get addon range

			$tax_query = array('relation'=>'AND');

			$tax_query[] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type->slug,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$tax_query[] = array(
			
				'taxonomy' 			=> 'user-contact',
				'field' 			=> 'slug',
				'terms' 			=> $this->parent->user->user_email,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$query = new WP_Query( array( 
				
				'post_type' 		=> 'cb-default-layer', 
				'posts_per_page'	=> 1,
				'fields'		 	=> 'ids',
				'tax_query' 		=> $tax_query,
				'no_found_rows'		=> false,
			));
		
			if( !empty($query->posts) ){

				foreach( $query->posts as $post_id ){
					
					$meta = get_term_meta($addon->term_id);
						
					$ranges[$addon->slug]['term_id'] 	= $addon->term_id;
					$ranges[$addon->slug]['name'] 	= $addon->name;
					$ranges[$addon->slug]['slug'] 	= $addon->slug;
					$ranges[$addon->slug]['short'] 	= !empty($meta['shortname'][0]) ? $meta['shortname'][0] : $addon->name;
					$ranges[$addon->slug]['count'] 	= $query->found_posts;
					$ranges[$addon->slug]['taxonomy'] = $range->taxonomy;
				}
			}
		}

		// sort ranges
		
		if( !empty($ranges) ){
		
			// order by count
			
			$counts = array();
			
			foreach( $ranges as $key => $range ){
				
				$counts[$key] = $range['count'];
			}
			
			array_multisort($counts, SORT_DESC, $ranges);
		}
		
		return $ranges;
	}
	
	public function get_layer_range_id($post){
		
		$layer_range_id = 0;
		
		$layer_range = $this->get_layer_range($post);
		
		if( !empty($layer_range->term_id) ){
			
			$layer_range_id = $layer_range->term_id;
		}
		
		return $layer_range_id;
	}
	
	public function get_layer_range($post){
		
		$term = null;
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}

		if( !empty($post->post_type) ){
			
			if( $post->post_type == 'user-layer' ){
					
				// get default layer id
				
				$default_id = $this->get_default_id($post->ID);
				
				$post = get_post($default_id);
			}
			
			if( !is_null($post) ){
				
				$terms = wp_get_post_terms($post->ID,'layer-range');
			
				if( !is_wp_error( $terms ) && !empty($terms[0]) ){
					
					$term = $terms[0];
					
					// get short name
					
					$shortname = get_term_meta($term->term_id,'shortname',true);

					$term->shortname = !empty($shortname) ? $shortname : $term->name;
				}				
			}
		}
		
		return $term;
	}	
	
	public function get_layer_ranges(){
		
		if( is_null($this->ranges) ){
		
			$this->ranges = $this->get_terms('layer-range');
		}
		
		return $this->ranges;
	}
	
	public function get_account_options(){
		
		if( is_null($this->accountOptions) ){
		
			$this->accountOptions = $this->get_terms('account-option');
		}
		
		return $this->accountOptions;
	}
	
	public function get_css_libraries(){
		
		$this->cssLibraries = $this->get_terms( 'css-library', array(
			
			/*
			'jquery-ui-1-12-1' => array(
			
				'name' 		=> 'Jquery UI 1.12.1',
				'options' 	=> array(
				
					'css_url'	 => 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css',
					'css_content' => '',
				),
			),			
			'bootstrap-3-3-7' => array(
			
				'name' 		=> 'Bootstrap 3.3.7',
				'options' 	=> array(
				
					'css_url'	 => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
					'css_content' => '',
				),
				'children'	=> array(
				
					'material-kit-1-1-0' => array(
					
						'name' 		=> 'Material Kit 1.1.0',
						'options' 	=> array(
						
							'css_url'	 => $this->parent->assets_url . 'css/material-kit.css',
							'css_content' => '.card .card-image{height:auto;}',
						),
					),				
				),
			),
			'font-awesome-4-7-0' => array(
			
				'name' 		=> 'Font Awesome 4.7.0',
				'options' 	=> array(
				
					'css_url'	 => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
					'css_content' => '',
				),
			),			
			'animate-3-5-2' => array(
			
				'name' 		=> 'Animate 3.5.2',
				'options' 	=> array(
				
					'css_url'	  => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css',
					'css_content' => '',
				),
			),
			*/
			/*
			'elementor-2-2-7' => array(
			
				'name' 		=> 'Elementor 2.2.7',
				'options' 	=> array(
				
					'css_url'	 	=> 'https://ltple.recuweb.com/c/p/live-template-editor-resources/assets/elementor/2.2.7/frontend.min.css',
					'css_content' 	=> '',
				),
			),
			*/
		));
	}
	
	public function get_js_libraries(){

		$this->jsLibraries = $this->get_terms( 'js-library', array(
			'jquery-3-1-1' => array(
			
				'name' 		=> 'Jquery 3.1.1',
				'meta' 	=> array(
				
					'js_url'	 => 'https://code.jquery.com/jquery-3.1.1.min.js',
					'js_content' => '',
				),
				'children'	=> array(
				
					'jquery-ui-1-12-1' => array(
					
						'name' 		=> 'Jquery UI 1.12.1',
						'meta' 	=> array(

							'js_url'		=> 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
							'js_content'	=> '
								<script>
								;(function($){
									$(document).ready(function(){						
										if($("#scrapeLayer").length){
											$("#scrapeLayer").dialog({autoOpen: true});
										}								
									});
								})(jQuery);
								</script>
							',
						),
					),				
					'bootstrap-3-3-7' => array(
					
						'name' 		=> 'Bootstrap 3.3.7',
						'meta' 	=> array(

							'js_url'		=> 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js',
							'js_content'	=> '
								<script>
								;(function($){
									$(document).ready(function(){							
										$(\'.modal\').appendTo("body");
										$(\'[data-slide-to]\').on(\'click\',function(e){
											e.preventDefault();
											if( typeof $(this).attr(\'data-target\') !== typeof undefined ){
												var carouselId 	= $(this).attr(\'data-target\');
											}
											else{
												var carouselId 	= $(this).attr("href");
											}
											var slideTo 	= parseInt( $(this).attr(\'data-slide-to\') );
											$(carouselId).carousel(slideTo);
											return false;
										});
										
										$(\'[data-slide]\').on(\'click\',function(e){
											e.preventDefault();
											if( typeof $(this).attr(\'data-target\') !== typeof undefined ){
												var carouselId 	= $(this).attr(\'data-target\');
											}
											else{
												var carouselId 	= $(this).attr("href");
											}
											var slideTo 	= $(this).attr(\'data-slide\');
											$(carouselId).carousel(slideTo);
											return false;
										});
									});
								})(jQuery);
								</script>
							',
						),
					),				
				)
			),
		));
	}

	public function get_font_libraries(){
		
		$this->cssLibraries = $this->get_terms( 'font-library', array(
			
			'material-icons' => array(
			
				'name' 		=> 'Material Icons',
				'meta' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Material+Icons',
				),
			),
			'roboto' => array(
			
				'name' 		=> 'Roboto',
				'meta' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700',
				),
			),
		));
	}
	
	public function get_layer_type_slug($post){
		
		$layer_type_slug = '';
		
		$layer_type = $this->get_layer_type($post);
		
		if( !empty($layer_type->slug) ){
			
			$layer_type_slug = $layer_type->slug;
		}
		
		return $layer_type_slug;
	}
	
	public function get_layer_editor($editor,$layer){
		
		$layer_type = $this->get_layer_type($layer);
		
		if( !empty($layer_type->output) ){
			
			
			$editor = $layer_type->output;
		}
		
		return $editor;
	}

	public function get_layer_type($post){
		
		if( !empty($post) ){
		
			$post_id = 0;
			
			if( is_numeric($post) ){
				
				$post_id = intval($post);
			}
			elseif( is_object($post) ){
				
				$post_id = $post->ID;
			}
			
			if( !empty($post_id) ){
				
				if( !isset($this->layer_types[$post_id]) ){
					
					$term = new stdClass();
					
					if( !is_object($post) ){
						
						$post = get_post($post_id);
					}
					
					if( !empty($post->post_type) ){
						
						if( $post->post_type == 'default-element' ){
						
							$term->name 	= 'Hosted Page';
							$term->output 	= 'hosted-page';
							$term->storage 	= 'user-element';
						}
						elseif( $post->post_type == 'page' ){

							// TODO migrate pages to element shortcode
							
							$term->name 	= 'Page';
							$term->output 	= 'hosted-page';
							$term->storage 	= 'page';
						}
						elseif( $post->post_type == 'post' ){
							
							// TODO migrate posts to element shortcode
							
							$term->name 	= 'Post';
							$term->output 	= 'hosted-page';
							$term->storage 	= 'post';
						}
						elseif( $this->is_media($post) ){
							
							$term->name 	= 'Image';
							$term->output 	= 'image';
							$term->storage 	= 'attachment';				
						}
						else{
						
							$terms = wp_get_post_terms($post->ID,'layer-type');
							
							if( !is_wp_error( $terms ) && !empty($terms[0]) ){
							
								$term = $terms[0];
							}
							else{
								
								if( $post->post_type != 'cb-default-layer' ){
								
									// get default layer id
									
									$default_id = $this->get_default_id($post->ID);
									 
									$default_post = get_post($default_id);
								
									if( !is_null($default_post) && $default_post->post_type == 'cb-default-layer' ){
										
										$terms = wp_get_post_terms($default_post->ID,'layer-type');			
										
										if( !empty($terms[0]) ){
											
											$term = $terms[0];
										}
									}					
								}
								elseif( is_admin() ){
									
									if( $default_range = $this->get_layer_range($post)){
										
										if( $range_type = $this->get_range_type($default_range->term_id) ){
											
											$term = $range_type;
										}
									}
								}
								
								if( !empty($term->term_id) ){

									// update layer type
											
									wp_set_object_terms( $post->ID, $term->term_id, 'layer-type', false ); 					
								}
							}
							
							if( !empty($term->term_id) ){
								
								$term->output = $this->get_type_output($term);

								$term->storage = $this->get_type_storage($term);
							}
						}
					}
					
					if( !isset($term->output) ){
						
						$term->output = '';
					}
					
					if( !isset($term->storage) ){

						$term->storage 	= '';
					}
					
					$this->layer_types[$post_id] = $term;
				}
				
				return $this->layer_types[$post_id];
			}
		}
		
		return false;
	}
	
	public function get_range_type($range_id){
		
		$type_term = null;
										
		if( $type_id = get_term_meta($range_id,'range_type',true)){
			
			$type_term = get_term($type_id);
		}
		
		return $type_term;
	}

	public function get_layer_features($layer){
		
		if( is_numeric($layer) ){
			
			$layer = get_post($layer);
		}
		
		$features = get_the_terms($layer->ID, 'layer-features');
		
		return $features;
	}
	
	public function get_layer_visibility($layer){
		
		if( is_numeric($layer) ){
			
			$layer = get_post($layer);
		}
		
		if( $layer->post_type == 'default-element' ){
			
			$visibility = 'registered';
		}
		else{
			
			$layer_type = $this->get_layer_type($layer);
			
			$visibility = $this->get_type_visibility($layer_type);
			
			if( $visibility == 'admin' || $visibility == 'none' ){
				
				$visibility = 'assigned';
			}
			elseif( !$visibility = get_post_meta( $layer->ID, 'layerVisibility', true ) ){
					
				$visibility = 'anyone';
			}
		}
		
		return $visibility;
	}
	
	public function get_type_visibility($term){
		
		$visibility = 'none';
		
		if( !empty($term->term_id) ){
		
			if( !$visibility = get_term_meta( $term->term_id, 'visibility', true ) ){
				
				//migrate visibility
				
				$visibility = get_option('visibility_'.$term->slug,'anyone');
			
				update_term_meta( $term->term_id, 'visibility', $visibility );
			}
		}
		
		return $visibility;
	}
	
	public function get_type_output($term){
		
		if( !$output = get_term_meta( $term->term_id, 'output', true ) ){
			
			$output = 'inline-css';
		}
		
		return $output;
	}
	
	public function get_storage_name($storage_slug){
		
		$storage_types = $this->get_storage_types();
		
		if( isset($storage_types[$storage_slug]) ){
			
			return $storage_types[$storage_slug];
		}
		
		return false;
	}
	
	public function get_type_storage($term){
		
		if( !$storage = get_term_meta( $term->term_id, 'default_storage', true ) ){
			
			$storage = 'user-layer';
		}
		/*
		elseif( !post_type_exists($storage) ){
			
			return false; // some child plugins are still not registered
		}
		*/
		
		return $storage;
	}
	
	public function get_thumbnail_url($post,$size='medium_large',$alt_url=false){
		
		$post_id = 0;
		
		if( is_numeric($post) ){
		
			$post_id = intval($post);
			
			$post = get_post($post_id);
		}
		elseif( is_object($post) && !empty($post->ID) ){
			
			$post_id = $post->ID;
		}
		
		if( !empty($post) && $post_id > 0 ){
			
			if( $image_id = get_post_thumbnail_id( $post_id ) ){
				
				if ($src = wp_get_attachment_image_src($image_id,$size)){
					
					return $src[0];
				}
			}
			
			if( $post->post_type != 'cb-default-layer' ){
				
				$defaultLayerId = $this->get_default_id($post_id);
			
				if( $defaultLayerId > 0 ){
					
					return $this->get_thumbnail_url($defaultLayerId,$size);
				}
			}
		}
		
		if( !empty($alt_url) ){
			
			return $alt_url;
		}

		return $this->parent->assets_url . 'images/default_item.png';
	}
	
	public function get_preview_image_url($post,$size='post-thumbnail',$alt_url=false){
		
		$post_id = is_numeric($post) ? $post : ( !empty($post->ID) ? $post->ID : 0 );
		
		if( !empty($post_id) ){
			
			// preview screenshot
			
			if( $att_id = get_post_meta($post_id,'ltple_screenshot_att_id',true) ){
				
				if( $src = wp_get_attachment_image_src( $att_id,$size ) ){
					
					return $src[0];
				}
			}
			
			// featured image
			
			if( $url = get_the_post_thumbnail_url($post_id,$size)){

				return $url;
			}
		}
		
		// alternative image
		
		if( !empty($alt_url) ){
			
			return $alt_url;
		}
		
		return false;
	}
	
	public function get_preview_modal($layer){
		
		if( !empty($layer->urls['view']) ){
			
			$preview_url = $layer->urls['view'];
			
			$start_url = apply_filters('ltple_start_url',$this->parent->urls->edit . '?uri='.$layer->ID,$layer);

			$visibility = $this->get_layer_visibility($layer);
			
			$show_preview = ( $visibility == 'anyone' || $visibility == 'registered' || ( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $layer ) === true )) ? true : false;

			$modal_id='modal_'.md5($preview_url);

			$content='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
				
				$content.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
					
					$content.='<div class="modal-content">'.PHP_EOL;
					
						$content.='<div class="modal-header">'.PHP_EOL;
							
							$content.='<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
						
							$content.='<button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
							
						$content.='</div>'.PHP_EOL;
						
						if( $show_preview === true ){
							
							$content.= '<iframe data-src="'.$preview_url.'" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 100px);overflow: hidden;"></iframe>';											
						}
						else{
							
							$content.= '<div class="modal-image-wrapper" style="width:100%;position:relative;bottom:0;border:0;height:calc( 100vh - 100px);overflow:auto;background:#555;text-align:center;">';
								
								if( $image = $this->get_preview_image_url($layer->ID,'full') ){

									$content.= '<img loading="lazy" src="' . $image . '">';
								}
								elseif( $image = get_the_post_thumbnail($layer->ID,'full') ){
									
									$content.= $image;
								}
								elseif( $image = $this->get_thumbnail_url($layer,'full',$this->parent->assets_url . 'images/default_item.png') ){
									
									$content.= '<img loading="lazy" src="' . $image . '">';
								}
							
							$content.= '</div>';
						}

						$content.='<div class="modal-footer">'.PHP_EOL;
						
							// get actions

							if( $this->parent->user->loggedin ){

								$actions ='<a class="btn btn-sm btn-success" href="'. $start_url .'" target="_self" title="Start editing this template">Start</a>';
							}
							else{
								
								$actions ='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
								
									$actions.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
							
								$actions.='</button>'.PHP_EOL;								
							}
							
							$content.= apply_filters('ltple_layer_preview_actions',$actions,$layer,$show_preview);
						
						$content.='</div>'.PHP_EOL;
					  
					$content.='</div>'.PHP_EOL;
					
				$content.='</div>'.PHP_EOL;
				
			$content.='</div>'.PHP_EOL;
			
			return array(
				
				'id' 		=> $modal_id,
				'content' 	=> $content,
			);
		}
		
		return false;
	}
			
	public function init_layer_backend(){
		
		add_filter('cb-default-layer_custom_fields', array( $this, 'get_default_layer_fields' ),9999);
		
		add_filter('default-element_custom_fields', array( $this, 'get_default_layer_fields' ),9999);
			
		if( $this->storageTypes = $this->get_storage_types() ){
				
			foreach( $this->storageTypes as $storage => $name ){	
				
				add_filter( $storage . '_custom_fields', array( $this, 'get_user_layer_fields' ));
			}
		}
	}
	
	public function set_uri(){
		
		if( is_admin() ){
			
			if( !empty($_REQUEST['post']) && intval($_REQUEST['post']) > 0 ){
				
				$this->uri = intval($_REQUEST['post']);
			}				
		}
		else{
			
			if( isset($_GET['uri']) ){
				
				$this->uri = intval($_GET['uri']);
			}
			elseif( $post_id = url_to_postid($this->parent->urls->current) ){
				
				$front_id = intval(get_option('page_on_front'));
				
				if( $front_id == $post_id ){
					
					if( parse_url($this->parent->urls->current,PHP_URL_PATH) == '/' ){
						
						$this->uri = $front_id;
					}
					else{
						
						// unknown path (wedocs...)
					}
				}
				else{
				
					$this->uri = apply_filters('ltple_layer_set_uri',$post_id);
				}
			}
		}
	}
	
	public function init_layer(){

		// set layer
		
		$this->set_uri();
		
		if( $this->uri > 0 ){

			//set layer data
			
			$this->set_layer($this->uri);			
		}

		if( $local_types = $this->get_local_types() ){
			
			foreach( $local_types as $post_type ){
				
				add_filter( $post_type . '_custom_fields', array( $this, 'get_user_layer_fields' ));
			}
		}
		
		//Add Custom API Endpoints
		
		add_action('rest_api_init', function(){
			
			register_rest_route( 'ltple-list/v1', '/user-layer/', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_user_layer_rows'),
				'permission_callback' => '__return_true',
			));

			register_rest_route( 'ltple-list/v1', '/user-psd/', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_user_psd_rows'),
				'permission_callback' => '__return_true',
			));				
		});
	}
	
	public function parse_layer_status($post_status){
		
		$layer_status = 'Draft';
		
		if( $post_status == 'publish' ){
			
			$layer_status = 'Public';
		}
		elseif( $post_status == 'private' ){
			
			$layer_status = 'Private';
		}
		
		return $layer_status;
	}
	
	public function get_action_buttons($post,$layer_type,$target='_self'){
		
		$action = '';
		
		if( $layer = LTPLE_Editor::instance()->get_layer($post->ID) ){
			
			$action  .= '<a target="'.$target.'" href="' . $layer->urls['settings'] . '" class="btn btn-sm btn-success" style="margin:1px;">Edit</a>';
			
			if( $this->is_html_output($layer_type->output) ){
			
				$action .= '<a target="_blank" href="' . $layer->urls['view'] . '" class="btn btn-sm" style="background-color:rgb(189, 120, 61);margin:1px;" target="_blank">View</a>';
			}
			
			$action .= '<button data-toggle="dialog" data-target="#quickRemoveTpl' . $post->ID . '" class="btn btn-sm btn-danger" style="margin:1px;">Delete</button>';
	 
			$action .= '<div style="display:none;text-align:center;" id="quickRemoveTpl' . $post->ID . '" title="Remove Project #' . $post->ID . '">';
				
				$action .=  '<div class="alert alert-danger">Are you sure you want to delete this project?</div>';						

				$action .=  '<a data-toggle="action" data-refresh="self" style="margin:10px;" class="btn btn-xs btn-danger" href="' . $this->parent->urls->edit . '?uri=' . $post->ID . '&postAction=delete&confirmed">Delete permanently</a>';
				
			$action .= '</div>';
		}
		
		return $action;
	}	
	
	public function get_user_layer_rows($request) {
		
		$layer_rows = [];
		
		if( $posts = get_posts(array(
		
			'post_type' 		=> 'user-layer',
			'post_status' 		=> array('publish','draft'),
			'author' 			=> $this->parent->user->ID,
			'posts_per_page'	=> -1,
			
		))){

			foreach( $posts as $i => $post ){
				
				$layer_type = $this->get_layer_type($post);			
				
				$alt_url = $this->get_preview_image_url($post,'thumbnail',$this->parent->assets_url . 'images/default_item.png');
				
				$row = [];
				$row['preview'] 	= '<div style="height:100px;width:100px;background:url(' . $this->get_thumbnail_url($post,'thumbnail',$alt_url) . ');background-size:cover;background-repeat:no-repeat;background-position:center center;width:100%;display:inline-block;"></div>';
				$row['name'] 		= ucfirst($post->post_title);
				//$row['status'] 	= $this->parse_layer_status($post->post_status);
				$row['type'] 		= $layer_type->name;
				$row['action'] 		= $this->get_action_buttons($post,$layer_type);
				
				$layer_rows[] = $row;
			}
		}
		
		return $layer_rows;
	}
	
	public function get_user_storage_types($user_id){
		
		$user_types = array();
		
		if( $count = $this->count_layers_by_storage() ){
			
			if( $types = $this->get_storage_types() ){
				
				foreach( $types as $slug => $name ){
					
					if( !empty($count[$slug]) && !$this->is_layer_part($slug) ){
						
						$user_types[$slug] = $name;
					}
				}
			}
		}
		
		return $user_types;
	}
	
	public function get_user_psd_rows($request) {
		
		$psd_rows = [];
		
		if( $posts = get_posts(array(
		
			'post_type' 		=> 'user-psd',
			'post_status' 		=> array('publish','draft'),
			'author' 			=> $this->parent->user->ID,
			'posts_per_page'	=> -1,
			
		))){

			foreach( $posts as $i => $post ){
				
				$layer_type = $this->get_layer_type($post);
				
				$alt_url = $this->get_preview_image_url($post,'thumbnail',$this->parent->assets_url . 'images/default_item.png');
				
				$row = [];
				
				$row['preview'] 	= '<div style="height:100px;width:100px;background:url(' . $this->get_thumbnail_url($post,'thumbnail',$alt_url) . ');background-size:cover;background-repeat:no-repeat;background-position:center center;width:100%;display:inline-block;"></div>';
				$row['name'] 		= ucfirst($post->post_title);
				//$row['status'] 	= $this->parse_layer_status($post->post_status);
				$row['type'] 		= $layer_type->name;
				$row['action'] 		= $this->get_action_buttons($post,$layer_type);
				
				$psd_rows[] = $row;
			}
		}
		
		return $psd_rows;
	}
	
	public function get_default_id($id){
		
		if( !isset($this->default_ids[$id]) ){
			
			if( !$default_id = intval(get_post_meta( $id, 'defaultLayerId', true )) ){
				
				//$default_id = $id;
			}
			
			$this->default_ids[$id] = $default_id;
		}
		
		return $this->default_ids[$id];
	}
	
	public function save_default_id( $layer, $default_id = null ){
		
		if( is_numeric($layer) ){
			
			$layer = get_post($layer);
		}
		
		if( !empty($layer->ID) ){
			
			if( is_null($default_id) ){
				
				$default_id = $this->get_default_id($layer->ID);
			}
			
			if( !empty($default_id) ){
				
				update_post_meta($layer->ID, 'defaultLayerId', $default_id);
			}
		}
	}
	
	public function get_layer($layer){
		
		// TODO throw errors
		
		return LTPLE_Editor::instance()->get_layer($layer);
	}
	
	public function set_layer( $layer = NULL, $echo = true ){
		
		if( $layer = LTPLE_Editor::instance()->get_layer($layer) ){
			
			if( $layer->post_status == 'publish' || $layer->post_status == 'draft' || $layer->post_status == 'inherit' || $layer->post_status == 'pending' ){
				
				$this->layerEcho = $echo;
				
				$this->is_default 	= $this->is_default($layer);
				
				$this->is_local 	= $this->is_local($layer);
				
				$this->is_storage 	= $this->is_storage($layer);
				
				$this->is_media 	= $this->is_media($layer);
				
				if( $this->is_default || $this->is_storage || $this->is_local || $this->is_media ){
					
					$this->id 			= $layer->ID;
					$this->type 		= $layer->post_type;
					$this->slug 		= $layer->post_name;
					$this->title 		= $layer->post_title;
					$this->author 		= intval($layer->post_author);

					if( $this->is_default ){
						
						$this->defaultId = $this->id;
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );						
					}
					elseif( $this->is_storage ){
					
						$this->defaultId = $this->get_default_id($this->id);
					}
					elseif( $this->is_local ){
						
						$this->defaultId = $this->get_default_id($layer->ID);
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );							
					}
					else{
						
						$this->defaultId = $this->id;
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );
					}
					
					// get default layer type
					
					$this->defaultLayerType = $this->get_layer_type($this->defaultId);
					
					//get layer output

					$this->layerOutput = !empty($this->defaultLayerType->output) ? $this->defaultLayerType->output : $layer->output;
					
					//get layer storage
					
					$this->layerStorage = !empty($this->defaultLayerType->storage) ? $this->defaultLayerType->storage : $layer->storage;
					
					if( $this->layerOutput == 'image' ){
						
						// get layer image template
						
						$this->layerImageTpl = array();

						$attachments = $this->get_layer_attachments($this->id,$this->layerStorage);
						
						if( empty($attachments) && $this->id != $this->defaultId ){
							
							$attachments = $this->get_layer_attachments($this->defaultId,$this->layerStorage);
						}
						
						if( !empty($attachments) ){

							$this->layerImageTpl = reset($attachments);
						}
					}
					else{
						
						// get theme id
						
						if( $theme = $this->parent->profile->get_current_theme() ){
							
							$this->themeId = $theme->ID;
						}
						
						// get layer Content
						
						$this->layerContent = get_post_meta( $this->id, 'layerContent', true );
						
						if( $this->layerContent == '' && $this->id != $this->defaultId ){
							
							$this->layerContent = get_post_meta( $this->defaultId, 'layerContent', true );
						}
						
						// get layer json
						
						$this->layerJson = get_post_meta( $this->id, 'layerJson', true );
						
						if( $this->layerJson == '' && $this->id != $this->defaultId ){
							
							$this->layerJson = get_post_meta( $this->defaultId, 'layerJson', true );
						}
						
						// get default css

						$this->defaultCss = apply_filters('ltple_parse_css_variables',get_post_meta( $this->defaultId, 'layerCss', true ));
						
						// get layer css
						
						$this->layerCss = apply_filters('ltple_parse_css_variables',get_post_meta( $this->id, 'layerCss', true ));
						
						if( empty($this->layerCss) && $this->id != $this->defaultId ){
							
							if( $this->layerOutput != 'hosted-page' ){
							
								$this->layerCss = $this->defaultCss;
							}
						}
		
						// get default js

						$this->defaultJs = $this->get_layer_js($this->defaultId);
						
						// get layer js
						
						$this->layerJs = $this->get_layer_js($this->id);
						
						if( $this->layerJs == '' && $this->id != $this->defaultId ){
							
							$this->layerJs = $this->defaultJs;
						}
						
						// get default elements

						$this->defaultElements = get_post_meta( $this->defaultId, 'layerElements', true );
						
						// get layer meta
						
						$this->layerMeta = get_post_meta( $this->id, 'layerMeta', true );
						
						if( $this->layerMeta == '' && $this->id != $this->defaultId ){
							
							$this->layerMeta = get_post_meta( $this->defaultId, 'layerMeta', true );
						}

						if(!empty($this->layerMeta)){
							
							$this->layerMeta = json_decode($this->layerMeta,true);
						}								
											
						// get layer Margin
						
						$this->layerMargin 	 = get_post_meta( $this->defaultId, 'layerMargin', true );
						
						if( empty($this->layerMargin) ){

							$this->layerMargin = '0px auto';
						}
						
						// get layer Min Width

						$this->layerMinWidth = get_post_meta( $this->defaultId, 'layerMinWidth', true );
						
						if( empty($this->layerMinWidth) ){
							
							$this->layerMinWidth = '1000px';
						}									
						
						//get page def
						
						$this->pageDef = get_post_meta( $this->defaultId, 'pageDef', true );
						
						//get default static path
						
						$this->defaultStaticPath = $this->get_static_path($this->defaultId,$this->defaultId);
							
						//get default static css path
						
						$this->defaultStaticCssPath = $this->get_static_asset_path($this->id,'css','default_style');
							
						//get default static js path
						
						$this->defaultStaticJsPath = $this->get_static_asset_path($this->id,'js','default_script');

						//get default static dir url
						
						$this->defaultStaticDirUrl = $this->get_static_dir_url($this->defaultId,$this->layerOutput);					
						
						//get default static url
						
						$this->defaultStaticUrl = $this->get_static_url($this->defaultId,$this->defaultId,$this->layerOutput);
						
						//get default static css url
						
						$this->defaultStaticCssUrl = ( file_exists($this->defaultStaticCssPath) ? $this->get_static_asset_url($this->id,'css','default_style') : '' );

						//get default static js url
						
						$this->defaultStaticJsUrl = ( file_exists($this->defaultStaticJsPath) ? $this->get_static_asset_url($this->id,'js','default_script') : '' );					
						
						//get default static dir
						
						$this->defaultStaticDir = $this->get_static_dir($this->defaultId);

						//get layer static path
						
						$this->layerStaticPath = $this->get_static_path($this->id,$this->defaultId);
							
						//get layer static css path
						
						$this->layerStaticCssPath = $this->get_static_asset_path($this->id,'css','custom_style');
							
						//get layer static js path
						
						$this->layerStaticJsPath = $this->get_static_asset_path($this->id,'js','custom_script');
						
						//get layer static url
						
						$this->layerStaticUrl = $this->get_static_url($this->id,$this->defaultId);
						
						//get layer static css url
						
						$this->layerStaticCssUrl = ( file_exists($this->layerStaticCssPath) ? $this->get_static_asset_url($this->id,'css','custom_style') : '' );

						//get layer static js url
						
						$this->layerStaticJsUrl = ( file_exists($this->layerStaticJsPath) ? $this->get_static_asset_url($this->id,'js','custom_script') : '' );						

						//get layer static dir
						
						$this->layerStaticDir = $this->get_static_dir($this->id);
	
						//get layer form
						
						$this->layerForm = get_post_meta( $this->defaultId, 'layerForm', true );
						
						//get css libraries
						
						$this->layerCssLibraries = $this->get_libraries(array($this->themeId,$this->defaultId),'css');
						
						//get js libraries
						
						$this->layerJsLibraries = $this->get_libraries(array($this->themeId,$this->defaultId),'js');							
						
						//get font libraries
						
						$this->layerFontLibraries = $this->get_libraries(array($this->themeId,$this->defaultId),'font');																			
						
						//get element libraries
						
						$this->layerHtmlLibraries = $this->get_libraries(array($this->themeId,$this->defaultId),'element');
					}
				}
			}
		}
	}
	
	public function get_libraries($layer_id,$type){
		
		$libraries = array();
		
		if( is_array($layer_id) ){
			
			foreach( $layer_id as $id ){
				
				if( is_numeric($id) ){
					
					$libraries = array_merge($libraries,$this->get_libraries($id,$type));
				}
			}
		}
		elseif( is_numeric($layer_id) && $layer_id > 0 ){
			
			if( $this->has_libraries($layer_id,$type) ){
				
				$terms = wp_get_post_terms( $layer_id, $type . '-library', array( 
					
					'orderby' => 'term_id',
				));	
				
				if( !is_wp_error( $terms ) && !empty($terms) ){
					
					foreach( $terms as $term ){
						
						$libraries[$term->term_id] = $term;
						
						if( $type == 'element' ){
								
							if( $term->parent == 0 ){
								
								$children = get_term_children($term->term_id,$term->taxonomy);
								
								if( !empty($children) ){
								
									$children = get_terms( array(
										
										'taxonomy' 		=> $term->taxonomy,
										'hide_empty' 	=> false,
										'include' 		=> $children,
									
									));
									
									foreach( $children as $child ){
										
										$libraries[$child->term_id] = $child;
									}
								}
							}
						}
						elseif( $term->parent > 0 ){

							$ancestors = get_ancestors($term->term_id,$term->taxonomy);
							
							if( !empty($ancestors) ){
							
								$ancestors = get_terms( array(
									
									'taxonomy' 		=> $term->taxonomy,
									'hide_empty' 	=> false,
									'include' 		=> $ancestors,
								
								));
								
								foreach( $ancestors as $ancestor ){
									
									$libraries[$ancestor->term_id] = $ancestor;
								}
							}
						}
					}
				}
				
				if( !empty($libraries) && ( $type == 'js' || $type == 'css' ) ){
					
					// order libraries

					$ordered 	= array();
					$level 	= 0;
					$items 	= $libraries;				
					
					while( !empty($items) && $level < 10 ){
						
						foreach( $items as $i => $item ){
							
							if( !isset($libraries[$i]) ){
								
								unset($items[$i]);
							}
							elseif( $item->parent == 0 || isset($ordered[$item->parent]) ){
								
								$ordered[$item->term_id] = $item;
								unset($items[$i]);
							}
						}
						
						++$level;
					}
					
					$libraries = $ordered;
					
					// add arguments
					
					if( $type == 'css' ){
						
						foreach( $libraries as $library ){

							$library->url 		= $this->get_css_parsed_url($library);
							$library->prefix 	= 'style-' . $library->term_id;
						}
					}
				}
			}
		}
		
		return $libraries;
	}

	public function has_libraries($layer_id,$type){
		
		return true;
		
		if( $layer_type = $this->get_layer_type($layer_id) ){
			
			if( in_array($layer_type->storage,array(
			
				'user-theme',
				//'user-page',
			
			))){
				
				return true;
			}
		}
		
		return false;
	}
	
	public function extract_css_urls( $str ){
		
		$urls = array( );
	 
		$url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
		
		$urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
		
		$pattern         = '/(' .
			 '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
			'|(@import\s*'      . $urlfunc_pattern . ')'      .
			'|('                . $urlfunc_pattern . ')'      .  ')/iu';
		
		if ( !preg_match_all( $pattern, $str, $matches ) )
			return $urls;
	 
		foreach ( $matches[11] as $match )
			if ( !empty($match) )
				$urls[] = 
					preg_replace( '/\\\\(.)/u', '\\1', $match );
	 
		return $urls;
	}

	public function parse_css_content($content,$prepend,$source='',$charset='utf-8'){
		
		// protect unicode numbers
		
		$content = str_replace('\\','\\\\',$content);
		
		// filter content
		
		$content = apply_filters('ltple_parse_css_variables',$content);
		
		// parse content
		
		include_once($this->parent->vendor . '/autoload.php');
		
		$cssSettings = Sabberworm\CSS\Settings::create();
		
		$cssSettings->withDefaultCharset($charset);
		
		$cssSettings->withMultibyteSupport(false); // use mb_* functions
		
		$cssParser = new Sabberworm\CSS\Parser($content,$cssSettings);
		
		$css = $cssParser->parse();
		
		// remove rules
		
		/*
		foreach( $css->getAllRuleSets() as $rule ) {
			
			$rule->removeRule('cursor');
		}
		*/
		
		foreach( $css->getAllValues() as $value ) {
			
			if( !empty($source) ){
				
				// replace relative path to absolute
			
				if( method_exists($value,'__toString') ) {
					
					$str = $value->__toString();
					
					if( strpos($str,'url(') !== false ){

						$urls = $this->extract_css_urls($str);
						
						if( !empty($urls) ){
						
							foreach( $urls as $url){
								
								$abs_url = $this->parent->get_absolute_url($url, $source);
								
								$newUrl = new \Sabberworm\CSS\Value\CSSString($abs_url);
								
								$value->setURL($newUrl);							
							}
						}
					}
				}
			}
		}
				
		// prepend selectors
		
		foreach( $css->getAllDeclarationBlocks() as $block ) {
			
			//dump($block);
			
			foreach( $block->getSelectors() as $selector ) {
				
				$name = $selector->getSelector();
				
				$valid = true;
				
				/*
				
				// filter selectors
				
				$filters = '.glyphicon-';
				
				$filters = explode(' ',$filters);
				
				if( !empty($filters) ){
				
					foreach( $filters as $filter ){
					
						if( strpos($name,$filter) !== false ){
							
							$valid = false;
							break;
						}
					}
				}
				*/
				
				if( $valid ){
					
					$separator = ' ';
					
					$selector->setSelector( $prepend . $separator . $selector->getSelector() );
				}
				else{
					
					// remove block
					
					$css->removeDeclarationBlockBySelector($block, true);
				}
			}
		}
		
		//$content = $css->render(Sabberworm\CSS\OutputFormat::createPretty());
		
		//$content = $css->render(Sabberworm\CSS\OutputFormat::createCompact());
		
		$content = $css->render();

		// restore unicode numbers
		
		$content = str_replace('\\\\','\\',$content);

		// correct minor bugs
		
		$content = str_replace(
			
			array(
				
				'vm in;'
			),
			array(
			
				'vmin;'
			),
			
		$content);
		
		// normalize white spaces

		$content = preg_replace('/[\t\r\n]+/S', '', $content);
		
		return $content;
	}
	
	public function parse_font_content($content,$font_url=null){
		
		if( !empty($font_url) ){
			
			$url = parse_url($font_url);
			
			$content = str_replace( array(
				
				'url(../',
				'url (../',
			
			),'url('.$url['scheme'].'://'.$url['host'].dirname($url['path'],2).'/',$content);
		
			$content = str_replace( array(
				
				"url('../",
				"url ('../",
			
			),"url('".$url['scheme'].'://'.$url['host'].dirname($url['path'],2).'/',$content);
		
		}
		
		return $content;
	}
	
	public function get_layer_js($layer_id){
		
		return apply_filters('ltple_layer_js',get_post_meta( $layer_id, 'layerJs', true ),$layer_id,$this->is_default($layer_id));
	}
	
	public function parse_hosted_content($layerOutput=null){
		
		// get layer content
		
		$layerHead 			= '';
		$layerContent 		= '';
		
		$headStyles = array();
		$headLinks = array();
		
		if( !empty($this->defaultStaticPath) && file_exists($this->defaultStaticPath) ){
			
			$output = file_get_contents($this->defaultStaticPath);

			// strip html comments
			
			$output = preg_replace('/<!--(.*)-->/Uis', '', $output);
			
			// parse dom elements
			
			libxml_use_internal_errors( true );
			
			$dom= new DOMDocument();
			$dom->loadHTML('<?xml encoding="UTF-8">' . $output); 

			$xpath = new DOMXPath($dom);
			
			// remove nodes
			
			$nodes = $xpath->query('//meta|//title|//base');
			
			foreach ($nodes as $node) {
				
				$node->parentNode->removeChild($node);
			}

			// remove duplicate styles
			
			$nodes = $xpath->query('//style');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->nodeValue;
				
				if( !empty($nodeValue) ){
				
					if( !in_array($nodeValue,$headStyles) ){
					
						$headStyles[] = $nodeValue;
					}
					else{
					
						$node->parentNode->removeChild($node);
					}
				}
			}		
			
			// remove duplicate links
			
			$nodes = $xpath->query('//link');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->getAttribute('href');
				
				if( !empty($nodeValue) ){
					
					$link = $this->sanitize_url($nodeValue,$this->defaultStaticDirUrl);
				
					if( !in_array($link,$headLinks) ){
						
						if( $link != $nodeValue ){
							
							//normalize link
							
							$node->setAttribute('href',$link);
						}
					
						$headLinks[] = $link;
					}
					else{
					
						$node->parentNode->removeChild($node);
					}
				}
			}
			
			// parse relative image urls
			
			$nodes = $xpath->query('//img');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->getAttribute('src');
				
				if( !empty($nodeValue) ){
					
					//normalize link
					
					$link =$this->sanitize_url($nodeValue,$this->defaultStaticDirUrl);

					$node->setAttribute('src',$link);
				}
			}

			// get head
			
			$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );
			$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
			
			// get body
			
			if( !empty($this->layerContent) ){
			
				$layerContent = $this->layerContent;
			}
			else{
				
				$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
				$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
			}
		}
		else{

			$layerContent = !empty($this->layerContent) ? LTPLE_Editor::sanitize_content($this->layerContent) : '';
		}
		
		// parse content elements
		
		libxml_use_internal_errors( true );
		
		$dom= new DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $layerContent); 

		$xpath = new DOMXPath($dom);

		// remove pagespeed_url_hash
		
		$links = [];
		
		$nodes = $xpath->query('//img');
		
		foreach ($nodes as $node) {
			
			$node->removeAttribute('pagespeed_url_hash');
		}			
		
		$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
		$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);

		//get style-sheet
		
		$defaultCss 	= '';
		$layerCss 		= '';
		$defaultJs 		= '';
		$layerJs 		= '';
		$layerMeta 		= '';

		if( isset($_POST['importCss']) ){

			$layerCss = stripcslashes($_POST['importCss']);
		}
		elseif( empty($_POST) ){
			
			$defaultCss = !empty($this->defaultCss) ? $this->parse_css_content($this->defaultCss, '.layer-' . $this->defaultId) : '';
			
			$layerCss = !empty($this->layerCss) ? $this->layerCss : '';
		
			$defaultJs 	= !empty($this->defaultJs) 	? $this->defaultJs 	: '';

			$layerJs 	= !empty($this->layerJs) 	? $this->layerJs 	: '';
			
			$layerMeta 	= !empty($this->layerMeta) 	? $this->layerMeta 	: '';
		}
		
		$defaultCss = sanitize_text_field($defaultCss);
		$layerCss 	= sanitize_text_field($layerCss);
		
		$layerContent = str_replace('<?xml encoding="UTF-8">','',$layerContent);
		
		// get google fonts
		
		$googleFonts = [];
		$fontsLibraries = [];
		
		if( !empty($layerCss) ){
			
			$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
			$fonts = preg_match($regex, $layerCss,$match);
			
			if(isset($match[1])){
				
				$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
			}
		}
		
		// get font libraries
		
		if( !empty($this->layerFontLibraries) ){
			
			foreach($this->layerFontLibraries as $term){
				
				$font_url = $this->get_meta($term,'font_url');
					
				if( !empty($font_url) ){
					
					$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
					$fonts = preg_match($regex, $font_url,$match);

					if(isset($match[1])){
						
						$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
					}
					elseif( $font_url = $this->get_font_parsed_url($term) ){
						
						if( pathinfo(parse_url($font_url,PHP_URL_PATH),PATHINFO_EXTENSION) == 'css' ){
							
							$type = 'css';
						}
						else{
							
							$type = 'web';
						}
						
						$fontsLibraries[$type][$font_url] = $this->get_font_family($term);
					}		
				}
			}
		}
			
		// get head content

		$head = '';
		
	
		// font library
		
		if( !empty($googleFonts) ){
		
			$head .= '<link href="//fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet" />' . PHP_EOL;
		}
		
		if( !empty($fontsLibraries['web']) ){
			
			$head .= '<style id="LiveTplEditorFonts">' . PHP_EOL;
			
			foreach( $fontsLibraries['web'] as $font_url => $font_family ){
		
				if( !in_array($font_url,$headLinks) ){
					
					$head .= '@font-face { font-family: ' . $font_family . '; src: url("' . $font_url . '"); }' . PHP_EOL;
				
					$headLinks[] = $font_url;
				}
			}
			
			$head .= '</style>' . PHP_EOL;
		}
		
		if( !empty($fontsLibraries['css']) ){
			
			foreach( $fontsLibraries['css'] as $font_url => $font_family ){
		
				if( !in_array($font_url,$headLinks) ){
					
					$head .=  '<link rel="stylesheet" id="'.sanitize_title($font_family).'" href="'.$font_url.'" type="text/css" media="all" />' . PHP_EOL;
				
					$headLinks[] = $font_url;
				}
			}
		}
		
		if( !empty($this->layerCssLibraries) ){
			
			foreach($this->layerCssLibraries as $library){
				
				$head .= '<link href="' . $library->url . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
			}
		}
		
		if( !empty($layerHead) ){
			
			$head .= $layerHead;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$url =$this->sanitize_url( $url );
				
				if( !empty($url) && !in_array($url,$headLinks) ){
				
					$head .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />' . PHP_EOL;
			
					$headLinks[] = $url;
				}
			}
		}			
		
		// output css files
		
		if( !empty($this->defaultStaticCssUrl) ){
			
			$this->defaultStaticCssUrl =$this->sanitize_url( $this->defaultStaticCssUrl );
			
			if( !empty($this->defaultStaticCssUrl) && !in_array($this->defaultStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $this->defaultStaticCssUrl . '" rel="stylesheet" />' . PHP_EOL;
			
				$headLinks[] = $this->defaultStaticCssUrl;
			}
		}
		
		if($this->type == 'user-layer' && $layerCss != $defaultCss ){
			
			$this->layerStaticCssUrl =$this->sanitize_url( $this->layerStaticCssUrl );
			
			if( !empty($this->layerStaticCssUrl) && !in_array($this->layerStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $this->layerStaticCssUrl . '" rel="stylesheet" />' . PHP_EOL;
			
				$headLinks[] = $this->layerStaticCssUrl;
			}
		}
		
		// add meta
		
		if( !$this->is_local($this->id) ){ 

			// output default title
			
			$title = ucfirst($this->parent->layer->title);
			
			$head .= '<title>'.$title.'</title>'.PHP_EOL;
			$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
			$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
			$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;					

			// output default meta tags
			
			$ggl_webmaster_id = get_option( $this->parent->_base . 'embedded_ggl_webmaster_id' );
			
			if( !empty($ggl_webmaster_id) ){
			
				$head .= '<meta name="google-site-verification" content="'.$ggl_webmaster_id.'" />'.PHP_EOL;
			}
			
			/*
			
			//TODO $post doesnt exist
			
			$author_name = get_the_author_meta('display_name', $post->post_author );
			$author_mail = get_the_author_meta('user_email', $post->post_author );
			
			if( empty($this->layerSettings['meta_author']) ){
				
				$head .= '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$head .= '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$head .= '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
				$head .= '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;					
			}
			*/
			
			$locale = get_locale();
			
			if( empty($this->layerSettings['meta_language']) ){
				
				$head .= '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
			}
			
			$robots = 'index,follow';
			
			if( empty($this->layerSettings['meta_robots']) ){
				
				$head .= '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
			}
			/*
			$revised = $post->post_date;
			
			if( empty($this->layerSettings['meta_revised']) ){
			
				$head .= '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
			}
			*/
			
			$content = ucfirst($this->parent->layer->title);
			
			if( empty($this->layerSettings['meta_description']) ){
				
				$head .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
				$head .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
			}
			
			$head .= '<meta name="classification" content="Business" />' . PHP_EOL;
			//$head .= '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
			
			$service_name = get_bloginfo( 'name' );
			
			$head .= '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
			$head .= '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
			
			$head .= '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
			
			$head .= '<meta name="rating" content="General" />' . PHP_EOL;
			$head .= '<meta name="directory" content="submission" />' . PHP_EOL;
			$head .= '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
			$head .= '<meta name="distribution" content="Global" />' . PHP_EOL;
			$head .= '<meta name="target" content="all" />' . PHP_EOL;
			$head .= '<meta name="medium" content="blog" />' . PHP_EOL;
			$head .= '<meta property="og:type" content="article" />' . PHP_EOL;
			$head .= '<meta name="twitter:card" content="summary" />' . PHP_EOL;
			
			/*
			$head .= '<meta name="geo.position" content="latitude; longitude" />' . PHP_EOL;
			$head .= '<meta name="geo.placename" content="Place Name" />' . PHP_EOL;
			$head .= '<meta name="geo.region" content="Country Subdivision Code" />' . PHP_EOL;
			*/

			/*
			$ggl_analytics_id = get_option( $this->parent->_base . 'embedded_ggl_analytics_id' );
							
			if( !empty($ggl_analytics_id) ){
			
				?>
				<script> 
				
					<!-- Google Analytics Code -->
				
					(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

					ga('create', '<?php echo $ggl_analytics_id; ?>', 'auto');
					ga('send', 'pageview');
					
					<!-- End Google Analytics Code -->
					
				</script>

				<?php					
			}
			*/	
		}
		
		if( $favicon = get_site_icon_url() ){

			$head .= '<link rel="icon" href="'.$favicon.'" sizes="32x32"/>'.PHP_EOL;
			$head .= '<link rel="icon" href="'.$favicon.'" sizes="192x192"/>'.PHP_EOL;
			$head .= '<link rel="apple-touch-icon-precomposed" href="'.$favicon.'"/>'.PHP_EOL;
			$head .= '<meta name="msapplication-TileImage" content="'.$favicon.'"/>'.PHP_EOL;						
		}
			
		$this->layerHeadContent = $head;
		
		// get body content
		
		$body = '';
		
		//include style-sheets
		
		if( $defaultCss!='' ){
			
			$body .= '<style id="LiveTplEditorDefaultStyleSheet">'.PHP_EOL;
				
				$body .= $defaultCss .PHP_EOL;
			
			$body .= '</style>'.PHP_EOL;
		}
		
		$body .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
			if( $layerCss!='' ){

				$body .= $layerCss .PHP_EOL;
			}
			
		$body .= '</style>'.PHP_EOL;		
		
		//include layer
		
		$layer_template = get_page_template_slug( $this->id );
		
		if( $this->in_editor() ){
			
			// layer content
						
			$body .= '<ltple-layer class="editable" style="width:100%;' . ( !empty($this->layerMargin) ? 'margin:'.$this->layerMargin.';' : '' ) . '">';
							
				$body .= $layerContent;
			
			$body .= '</ltple-layer>' .PHP_EOL;
		}
		else{
			
			$body .= $layerContent;
		}
		
		if( !empty($this->layerJsLibraries) ){
			
			foreach($this->layerJsLibraries as $term){
				
				$js_skip = 'off';
				
				if( $this->is_local && $layerOutput != 'hosted-page' ){
				
					$js_skip = $this->get_meta( $term, 'js_skip_local' ) != 'on' ? 'off' : 'on' ;
				}
			
				if( $js_skip != 'on' ){
					
					$js_url = $this->get_js_parsed_url( $term, 'js_url' );
					
					if( !empty($js_url) ){
						
						$body .= '<script src="'.$js_url.'"></script>' .PHP_EOL;
					}
				}
			}
		}
		
		if( !empty($layerMeta['script']) ){
			
			foreach($layerMeta['script'] as $url){
				
				$body .= '<script src="'.$url.'"></script>' .PHP_EOL;
			}
		}

		//include layer script
		
		$body .='<script id="LiveTplEditorScript">' .PHP_EOL;
			
			if( $layerJs != '' ){
				
				$body .= $layerJs .PHP_EOL;				
			}
			
		$body .='</script>' .PHP_EOL;
		
		/*
		if( $this->type == 'user-layer' && !empty($layerJs) ){

			$body .= '<script src="' . $this->layerStaticJsUrl . '"></script>' .PHP_EOL;
		}
		elseif( !empty($defaultJs) ){
			
			$body .= '<script src="' . $this->defaultStaticJsUrl . '"></script>' .PHP_EOL;
		}
		*/
		
		$this->layerBodyContent = $body;
	}

	public function in_editor($editor='ltple'){
		
		if( $editor == 'gutenberg' ){

			if( function_exists('get_current_screen') ){

				$current_screen = get_current_screen();
				
				if( method_exists( $current_screen, 'is_block_editor' ) &&
					
					$current_screen->is_block_editor()
				){
					
					return true;
				}
			}
		}
		else{
			
			if( !empty($_GET['preview']) && $_GET['preview'] == 'ltple' )
				
				// layer url
				
				return true;
				
			if( !empty($_GET['uri']) )
							
				// inline content
				
				return true;
				
			if( is_admin() && !empty($_GET['action']) && $_GET['action'] == 'ltple' )
				
				// admin editor
				
				return true;
		}
			
		return false;
	}
	
	public function get_layer_attachments($post_id,$storage='user-psd'){
		
		if( $storage === 'attachment' ){
			
			$attachments = array(
			
				get_post($post_id)
			);
		}
		else{
		
			$attachments = get_posts( array(
							
				'post_parent' 		=> $post_id,
				'post_type' 		=> 'attachment',
				'post_mime_type' 	=> array('application/zip','image/vnd.adobe.photoshop'),
				'orderby' 			=> 'date',
				'order' 			=> 'DESC'
			));
		}

		return $attachments;
	}
		
	public function get_layer_description($layer_id,$strip=true){
		
		$description = get_post_meta($layer_id,'layerDescription',true);
		
		if( $strip === true ){
		
			$description = strip_tags($description);
		}	

		return $description;		
	}
		
	public function get_options($taxonomy,$term,$price_currency='$'){
		
		if(is_array($term)){
			
			$term_id 	= $term['term_id'];
			$term_slug 	= $term['slug'];
		}
		else{
			
			$term_id 	= $term->term_id;
			$term_slug 	= $term->slug;
		}
	
		if(!$price_amount = $this->get_plan_amount($term_id,'price')){
			
			$price_amount = 0;
		} 
		
		if(!$price_period = $this->get_plan_period($term_id,'price')){
			
			$price_period = 'month';
		}
		
		$storage = array();
		
		if( $taxonomy == 'layer-range' ){
			
			if( $range = $this->get_range_type($term_id) ){
					
				$storage_amount = $this->get_plan_amount($term_id,'storage');

				$storage[$range->name] = $storage_amount;
			}		
		}
		elseif( $taxonomy == 'account-option' ){
			
			$account_storages = get_term_meta($term_id,'account_storages',true);
			
			if( !empty($account_storages) && is_array($account_storages) ){
				
				foreach( $account_storages as $type_id => $storage_amount ){
					
					$storage_amount = intval($storage_amount);
					
					if( $storage_amount !== 0 ){
					
						if( $type = get_term($type_id) ){
							
							$storage[$type->name] = $storage_amount;
						}
					}
				}
			}
		}

		$options=[];
		
		$options['price_currency']	= $price_currency;
		$options['price_amount']	= $price_amount;
		$options['price_period']	= $price_period;
		$options['storage']			= $storage;
		
		// add addon options
		
		if( $taxonomy == 'account-option' ){
			
			if( $this->parent->settings->is_enabled('bandwidth') ){
				
				$options['bandwidth_amount'] = $this->get_plan_amount($term_id,'bandwidth');
			}
			
			$options = apply_filters('ltple_account_options',$options,$term_id);
		}
		
		return $options;
	}
	
	public function get_plan_amount($term_id,$type){
		
		if( is_object($term_id) ){
			
			$term_id = $term_id->term_id;
		}
		
		$amount = 0;
		
		if( !empty($term_id) ){
			
			$meta = get_term_meta($term_id,$type . '_amount',true);
			
			if( is_numeric($meta) ){
				
				$amount = $meta;
			}
			else{
				
				// data migration from option to meta
				
				$term = get_term($term_id);
				
				$amount = get_option( $type . '_amount_' . $term->slug,$amount);
				
				$this->update_plan_amount($term_id,$type,$amount);
			}			
		}
		
		return intval($amount);
	}
	
	public function update_plan_amount($term_id,$type,$amount){
		
		update_term_meta($term_id,$type . '_amount',$amount);
	}
	
	public function get_plan_period($term_id,$type){
		
		if( is_object($term_id) ){
			
			$term_id = $term_id->term_id;
		}
		
		$period = 'month';
		
		if( !empty($term_id) ){
			
			if( $meta = get_term_meta($term_id,$type . '_period',true)){
				
				$period = $meta;
			}
			else{
				
				// data migration from option to meta
				
				$term = get_term($term_id);
				
				$period = get_option( $type . '_period_' . $term->slug,$period);
				
				$this->update_plan_period($term_id,$type,$period);
			}			
		}
		
		return $period;
	}
	
	public function update_plan_period($term_id,$type,$period){
		
		return update_term_meta($term_id,$type . '_period',$period);
	}
	
	public static function is_absolute_path($file){
		
		return strspn($file, '/\\', 0, 1)
			|| (strlen($file) > 3 && ctype_alpha($file[0])
				&& substr($file, 1, 1) === ':'
				&& strspn($file, '/\\', 2, 1)
			)
			|| null !== parse_url($file, PHP_URL_SCHEME)
		;
	}	
	
	public static function sanitize_url( $url, $dirUrl = '' ){
		
		if( !empty($url) ){
		
			if( !empty($dirUrl) && !self::is_absolute_path($url) ){
				
				$url = $dirUrl . $url;
			}
			
			if( is_ssl() ){
				
				$url = str_replace( 'http://', 'https://', $url);
			}
			else{
				
				$url = str_replace( 'https://', 'http://', $url);
			}
		}
		
		return $url;
	}

	public function add_edit_layer_fields($term){
		
		//output our additional fields

		if( $term->taxonomy == 'layer-type' ){

			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Storage Unit </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'default_storage',
						'id'			=> 'default_storage',
						'label'			=> '',
						'type'			=> 'select',
						'options'		=> $this->get_storage_types(),
						'inline'		=> false,
						'description'	=> 'Default Post Type used to save the project',
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Editor Output</label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'output',
						'id'			=> 'output',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $this->get_layer_editors(),
						'inline'		=> false,
						'default'		=> 'inline-css',
						'description'	=> 'The Inputs and Type of Editor dependends on the selected Output',
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';			
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Gallery Section </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$options = array( '-1' => 'none' );
					
					$sections = get_terms( array(
							
						'taxonomy' 		=> 'gallery-section',
						'orderby' 		=> 'name',
						'order' 		=> 'ASC',
						'hide_empty'	=> false, 
					));
					
					if( !empty($sections) ){
							
						foreach( $sections as $section ){
							
							$options[$section->term_id] = $section->name;
						}
					} 
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'gallery_section',
						'id'			=> 'gallery_section',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $options,
						'inline'		=> false,
						'description'	=> '',
						
					), $term );				
					
				echo'</td>';	
				
			echo'</tr>';

			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Gallery Visibility </label>';
				
				echo'</th>';
				 
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'visibility_'.$term->slug,
						'id'			=> 'visibility_'.$term->slug,
						'label'			=> "",
						'type'			=> 'radio',
						'options'		=> array(
							
							'anyone'		=> 'Anyone',
							'admin'			=> 'Admin',
							'none'			=> 'None',
						),
						'inline'		=> false,
						'data'			=> $this->get_type_visibility($term),
						'description'	=> ''
						
					), false );
					
				echo'</td>';	
				
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Addon Range </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$options = array( '-1' => 'none' );
					
					$ranges = get_terms( array(
							
						'taxonomy' 		=> 'layer-range',
						'orderby' 		=> 'name',
						'order' 		=> 'ASC',
						'hide_empty'	=> false, 
					));
					
					if( !empty($ranges) ){
							
						foreach( $ranges as $range ){
							
							$options[$range->term_id] = $range->name;
						}
					} 
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'addon_range',
						'id'			=> 'addon_range',
						'type'			=> 'select',
						'options'		=> $options,
						'inline'		=> false,
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Installation</label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'installation',
						'id'			=> 'installation',
						'label'			=> "",
						'type'			=> 'text_editor',
						'description'	=> 'Describe the installation process',
						'placeholder'	=> '',
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';
		}
		else{
			
			if( $term->taxonomy == 'layer-range' ){
				
				// short name
				
				echo'<tr class="form-field">';
				
					echo'<th valign="top" scope="row">';
						
						echo'<label for="category-text">Shortname</label>';
					
					echo'</th>';
					
					echo'<td>';
						
						$this->parent->admin->display_field( array(			
							
							'name'			=> 'shortname',
							'id'			=> 'shortname',
							'label'			=> '',
							'type'			=> 'text',
							'default'		=> $term->name,
							'description'	=> '',
							'placeholder'	=> '',
							
						), $term );
						
					echo'</td>';	
					
				echo'</tr>';
			}
			
			// layer plan
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Price </label>';
				
				echo'</th>';		
			
				echo'<td>';
					
					//price
					
					$price = [];

					if( !empty($term->slug) ){
					
						$price['price_amount'] = $this->get_plan_amount($term->term_id,'price'); 
						$price['price_period'] = $this->get_plan_period($term->term_id,'price'); 
					}	
					
					echo'<div class="form-field" style="margin-bottom:15px;">';
						
						echo $this->parent->plan->get_layer_price_fields($term->taxonomy,$price);
						
					echo'</div>';
					
				echo'</td>';
			
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Storage </label>';
				
				echo'</th>';
				
				echo'<td>';
				
					if( $term->taxonomy == 'layer-range' ){

						// range storage amount
						
						$storage = array(
							
							'storage_amount'	=> $this->get_plan_amount($term->term_id,'storage'),
							'storage_unit'		=> get_term_meta($term->term_id,'range_type',true),
						);
						
						// range type
						
						echo'<div class="form-field" style="margin-bottom:15px;">';
							
							echo $this->parent->plan->get_layer_storage_fields($term->taxonomy,$storage);						
							
						echo'</div>';					
					
						//addon layer fields
						
						do_action('ltple_layer_plan_fields', $term->taxonomy, $term->slug);
					}
					elseif( $term->taxonomy == 'account-option' ){

						// layer storage
						
						$storages = get_term_meta($term->term_id,'account_storages',true);

						echo'<div class="form-field" style="margin-bottom:15px;">';
							
							echo $this->parent->plan->get_account_storage_fields($term->taxonomy,$storages);
							
						echo'</div>';
					}
					
				echo'</td>';
				
			echo'</tr>';
			
			if( $term->taxonomy == 'account-option' ){
				
				echo'<tr class="form-field">';
				
					echo'<th valign="top" scope="row">';
						
						echo'<label for="category-text">Limits </label>';
					
					echo'</th>';
					
					echo'<td>';
					
						if( $this->parent->settings->is_enabled('bandwidth') ){

							$bandwidth_amount = 0;
						
							if( !empty($term->term_id) ){
								
								$bandwidth_amount = $this->get_plan_amount($term->term_id,'bandwidth');
							}
							
							echo'<div class="form-field" style="margin-bottom:15px;">';
								
								echo'<label for="'.$term->taxonomy.'-bandwidth-amount">Bandwidth (GB)</label>';

								echo'<div class="input-group">';
								
									echo'<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">+</span>';
									
									echo'<input type="number" step="1" min="0" max="10000" placeholder="0" name="'.$term->taxonomy.'-bandwidth-amount" id="'.$term->taxonomy.'-bandwidth-amount" style="width:80px;" value="'.$bandwidth_amount.'"/>';					
									
								echo'</div>';
								
								echo'<p class="description">Additional monthly bandwidth usage limit in GB </p>';

							echo'</div>';	
						}
						
						//addon account fields
						
						do_action('ltple_account_plan_fields', $term->taxonomy, $term->term_id);

					echo'</td>';
					
				echo'</tr>';
			}
		}
	}
	
	public function get_css_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Remote Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'css_url',
					'name'				=> 'css_url',
					'placeholder'		=> 'http://',
					'description'		=> '',
					
				), $term );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">CSS Content </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'				=> 'code_editor',
					'code'				=> 'css',
					'id'				=> 'css_content',
					'name'				=> 'css_content',
					'placeholder'		=> '.style{display:block;}',
					'description'		=> '<i>without ' . htmlentities('<style></style>') . '</i>',
					'htmlentities'		=> false,
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
		
		$parse = $this->get_meta( $term, 'css_parse' ) != 'on' ? 'off' : 'on' ;
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Parse Content</label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'			=> 'switch',
					'id'			=> 'css_parse',
					'name'			=> 'css_parse',
					'data'			=> $parse,
					'description'	=> 'Prepend unique class name to CSS selectors',
					
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Source </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'		=> 'text',
					'id'		=> 'css_source',
					'name'		=> 'css_source',
					'data'		=> $this->get_css_parsed_url($term),
					'disabled'	=> true,
					
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function get_css_parsed_url($term){
		
		$attach_id 	= intval($this->get_meta( $term, 'css_attachment' ));		

		if( is_numeric($attach_id) ){
			
			$url = wp_get_attachment_url($attach_id);
			
			if(!empty($url)){
						
				$md5 = $this->get_meta( $term, 'css_md5' );
		
				return $url . '?' . $md5;
			}
		}
		
		return false;
	}
	
	public function get_js_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Remote Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'js_url',
					'name'				=> 'js_url',
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), $term );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">JS Content </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'code_editor',
					'code'				=> 'javascript',
					'id'				=> 'js_content',
					'name'				=> 'js_content',
					'placeholder'		=> 'javascript',
					'description'		=> '<i>without '.htmlentities('<script></script>').'</i>',
					'stripcslashes'		=> false,
					'data'				=> $this->get_meta($term,'js_content'),
					
				), $term );
					
			echo'</td>';
			
		echo'</tr>';
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Skip local pages</label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'			=> 'switch',
					'id'			=> 'js_skip_local',
					'name'			=> 'js_skip_local',
					'description'	=> 'Skip the library in local pages to avoid conflict with the current theme',
					
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Source </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'		=> 'text',
					'id'		=> 'js_source',
					'name'		=> 'js_source',
					'data'		=> $this->get_js_parsed_url($term),
					'disabled'	=> true,
					
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function get_js_parsed_url($term){
		
		$attach_id 	= intval($this->get_meta( $term, 'js_attachment' ));		

		if( is_numeric($attach_id) ){
			
			$url = wp_get_attachment_url($attach_id);
			
			if(!empty($url)){
						
				$md5 = $this->get_meta( $term, 'js_md5' );
		
				return $url . '?' . $md5;
			}
		}
		
		return false;
	}
	
	public function get_font_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Family </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'font_family',
					'name'				=> 'font_family',
					'placeholder'		=> 'Open Sans',
					'default'			=> $term->name,
					'description'		=> 'Font family name used in the CSS rules'
					
				), $term );					
				
			echo'</td>';
			
		echo'</tr>';
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'font_url',
					'name'				=> 'font_url',
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), $term );					
				
			echo'</td>';
			
		echo'</tr>';	
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Source </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'		=> 'text',
					'id'		=> 'font_source',
					'name'		=> 'font_source',
					'data'		=> $this->get_font_parsed_url($term),
					'disabled'	=> true,
					
				), $term );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function get_font_family($term){
		
		if( !$font_family = $this->get_meta( $term, 'font_family' )){

			$font_family = $term->name;
		}
		
		return $font_family;
	}
	
	public function get_font_parsed_url($term){
		
		$attach_id 	= intval($this->get_meta( $term, 'font_attachment' ));		
		
		if( is_numeric($attach_id) ){
			
			$url = wp_get_attachment_url($attach_id);
			
			if(!empty($url)){
						
				$md5 = $this->get_meta( $term, 'font_md5' );
		
				return $url . '?' . $md5;
			}
		}
		
		return false;
	}
	public function filter_default_user_layer_content($html,$layer){
		
		if( empty($html) ){
			
			if( $default_id = $this->get_default_id($layer->ID) ){
			
				$html = get_post_meta($default_id,'layerContent',true);
			}
		}
		
		return $html;
	}
	
	public function filter_default_user_layer_style($style,$layer){
		
		if( empty($style) ){
			
			if( $default_id = $this->get_default_id($layer->ID) ){
			
				$style = get_post_meta($default_id,'layerCss',true);
			}
		}
		
		return $style;
	}
	
	public function set_default_layer_columns($columns){
		
		$columns = array(
					
			'cb' 					=> '<input type="checkbox">',
			'title' 				=> 'Title',
			'author' 				=> 'Author',
			'taxonomy-layer-type' 	=> 'Gallery',
			'taxonomy-layer-range' 	=> 'Range',
			'output' 				=> 'Output',
			'thumb' 				=> 'Preview', // must remain last for mobile view
		);

		return $columns;
	} 

	public function set_user_layer_columns($columns){
		
		$columns = 	array_slice($columns, 0, 4, true) +
					array("output" => "Output") +
					array_slice($columns, 4, count($columns)-3, true);
		
		return $columns;
	}
	
	public function add_layer_type_column_content($column_name, $post_id){
		
		if( $column_name === 'thumb' ){
			
			$url = $this->get_preview_image_url($post_id,'thumbnail',$this->parent->assets_url . 'images/default_item.png');

			echo '<div style="height:100px;margin:5px 0;overflow:auto;">';

				echo '<a class="preview-' . $post_id . '" target="_blank" href="'.$url.'">';
				
					echo '<img loading="lazy" style="width:150px;" src="'.$url.'">';
				
				echo '</a>';
			
			echo '</div>';
		}
		elseif($column_name == 'output') {
			
			$layer_type = $this->get_layer_type($post_id);
			
			if( !empty($layer_type->name) ){

				$editors = $this->get_layer_editors();
			
				echo '<span class="label label-primary" style="margin-right:5px;">' . $editors[$layer_type->output] . '</span>';
			}
			else{
				
				// clean up corrupted projects
				
				//wp_delete_post( $post_id, true );
			}
		}
	}

	public function set_layer_type_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		$this->columns['output'] 		= 'Output';
		$this->columns['section'] 		= 'Section';
		$this->columns['visibility'] 	= 'Visibility';
		
		do_action('ltple_layer_type_columns');

		return $this->columns;
	}	
	
	public function set_layer_range_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		$this->columns['storage'] 		= 'Storage';
		$this->columns['price'] 		= 'Price';
	
		do_action('ltple_layer_range_columns');
	
		return $this->columns;
	}
	
	public function set_account_option_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		$this->columns['price'] 		= 'Price';
		$this->columns['storages'] 		= 'Storages';
		$this->columns['usage'] 		= 'Limits';
		
		return $this->columns;
	}
	
	public function add_layer_tax_column_content($content, $column_name, $term_id){
		
		if( $term = get_term($term_id) ){
			
			if($column_name === 'output') {
				
				$editors = $this->get_layer_editors();
				
				if(!$output = get_term_meta($term->term_id,'output',true)){
					
					$output = 'inline-css';
				}

				$content .='<span class="label label-primary">'.$editors[$output].'</span>';
			}
			elseif($column_name === 'section') {

				if( $section_id = get_term_meta($term->term_id,'gallery_section',true)){
					
					$sections = $this->get_gallery_sections();
					
					if( !empty($sections[$section_id]) ){
						
						$content .='<span class="label label-info">' . $sections[$section_id]->name . '</span>';
					}
				}
			}
			elseif($column_name === 'visibility') {
				
				$visibility = $this->get_type_visibility($term);
				
				if( $visibility == 'admin' ){
					
					$content .='<span class="label label-warning">'.$visibility.'</span>';
				}
				elseif( $visibility == 'none' ){
					
					$content .='<span class="label label-danger">'.$visibility.'</span>';
				}
				else{
					
					$content .='<span class="label label-success">'.$visibility.'</span>';
				}
				
			}
			elseif($column_name === 'price') {
				
				if(!$price_amount = $this->get_plan_amount($term,'price')){
					
					$price_amount = 0;
				} 
				
				if(!$price_period = $this->get_plan_period($term->term_id,'price')){
					
					$price_period = 'month';
				} 	
				
				$content .=$price_amount.'$'.' / '.$price_period;
			}
			elseif($column_name === 'storage') {

				// get storage amount
				
				$storage_amount = $this->get_plan_amount($term,'storage');			
				
				// get range type
				
				$type = '';
				
				if( $type_id = get_term_meta($term->term_id,'range_type',true)){
					
					if( $type = get_term($type_id) ){
						
						$type = ' <span class="label label-info">' . $type->name . '</span>';
					}
				}				
				
				if( !empty($type) ){
					
					if( $storage_amount == 1 ){
						
						$content .='<span class="label label-primary">+' . $storage_amount . '</span>' . $type;
					}
					elseif($storage_amount > 0){
						
						$content .='<span class="label label-primary">+' . $storage_amount . '</span>' .  $type;
					}
					else{
						
						$content .= '<span class="label label-primary">' . $storage_amount . '</span>' .  $type;
					}
				}
			}
			elseif($column_name === 'storages') {
				
				$storages = get_term_meta($term->term_id,'account_storages',true);			
				
				if( is_array($storages) && !empty($storages) ){
					
					foreach( $storages as $type_id => $storage_amount ){
						
						if( $storage_amount != 0 ){
						
							// get range type
							
							if( $type = get_term($type_id) ){
								
								$type = ' <span class="label label-info">' . $type->name . '</span>';
							
								if( $storage_amount == 1 ){
									
									$content .='<span class="label label-primary">+' . $storage_amount . '</span>' . $type;
								}
								elseif($storage_amount > 0){
									
									$content .='<span class="label label-primary">+' . $storage_amount . '</span>' .  $type;
								}
								else{
									
									$content .= '<span class="label label-primary">' . $storage_amount . '</span>' .  $type;
								}

								$content .= '<br>';							
							}
						}
					}
				}
			}
			elseif($column_name === 'usage') {
				
				if( $this->parent->settings->is_enabled('bandwidth') ){
					
					// display bandwidth amount
					
					if(!$bandwidth_amount = $this->parent->layer->get_plan_amount($term->term_id,'bandwidth')){
						
						$bandwidth_amount = 0;
					}
					
					$content .= '+' . $bandwidth_amount . ' GB' . '</br>';
				}
				
				$content .= apply_filters('ltple_layer_usage_column','',$term);
			}
		}

		return $content;
	}
	
	public function add_manage_layer_filters($post_type,$which){
		
		if( $post_type == 'cb-default-layer' ){
			
			$options = array( 'Default' => array(
			
				0 	=> 'All Types',
				-1 	=> 'Unknown',
			));
	
			if( $types = $this->get_layer_types() ){
				
				$storages = $this->get_storage_types();
				
				foreach( $types as $type ){

					if( isset($storages[$type->storage]) ){
					
						$storage = $storages[$type->storage];
					
						$options[$storage][$type->term_id] = $type->name;
					}
				}
				
				$layer_type = isset($_REQUEST['layer_type']) ? intval($_REQUEST['layer_type']) : key($types);
				
				echo $this->parent->admin->display_field( array(
					
					'id'		=> 'layer_type',
					'type'		=> 'select',
					'options'	=> $options,
					'data'		=> $layer_type,
					
				), false, false ); 					
			}
		}
		
		return $post_type;
	}

	public function pre_get_layers($query) {

		if( $query->is_admin ) {
			
			if( $query->get('post_type') == 'cb-default-layer' ){
				
				if( !isset($_REQUEST['orderby']) ){
				
					$query->set('orderby', 'id');
					$query->set('order', 'DESC');
				}
				
				if( $query->is_main_query() ){
				
					$types = $this->get_layer_types();
				
					$type_id = isset($_REQUEST['layer_type']) ? intval($_REQUEST['layer_type']) : key($types);
					
					if( $type_id > 0 ){

						$query->set('tax_query', array(
						
							array(
							
								'taxonomy' => 'layer-type',
								'terms'    => array($type_id),
								'operator' => 'IN'
							)
						));
					}
					elseif( $type_id < 0 ){
						
						$query->set('tax_query', array(
						
							array(
							
								'taxonomy' => 'layer-type',
								'operator' => 'NOT EXISTS'
							)
						));
					}
				}
			}
		}
		  
		return $query;
	}	
	
	public function save_layer_taxonomy_fields($term_id){
			
		if( $this->parent->user->is_admin ){
			
			//collect all term related data for this new taxonomy
			
			if( $term = get_term($term_id) ){
							
				//save our custom fields as wp-options
				
				if( isset($_POST[$term->taxonomy .'-price-amount']) && is_numeric($_POST[$term->taxonomy .'-price-amount']) ){

					$this->update_plan_amount($term->term_id,'price',round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));
				}
				
				if( isset($_POST[$term->taxonomy .'-price-period']) ){

					$periods = $this->parent->plan->get_price_periods();
					$period = sanitize_text_field($_POST[$term->taxonomy . '-price-period']);
					
					if(isset($periods[$period])){
						
						$this->update_plan_period($term->term_id,'price',$period);
					}
				}
				
				if(isset($_POST[$term->taxonomy .'-storage-amount'])&&is_numeric($_POST[$term->taxonomy .'-storage-amount'])){

					$this->update_plan_amount($term->term_id,'storage',round(intval(sanitize_text_field($_POST[$term->taxonomy . '-storage-amount'])),0));
				}
				
				if(isset($_POST[$term->taxonomy .'-bandwidth-amount'])&&is_numeric($_POST[$term->taxonomy .'-bandwidth-amount'])){
					
					$this->update_plan_amount($term->term_id,'bandwidth',round(intval(sanitize_text_field($_POST[$term->taxonomy . '-bandwidth-amount'])),0));
				}
				
				if(isset($_POST['output'])){

					update_term_meta( $term->term_id, 'output', $_POST['output']);			
				}
				
				if(isset($_POST['installation'])){

					update_term_meta( $term->term_id, 'installation', $_POST['installation']);			
				}
				
				if(isset($_POST['shortname'])){

					update_term_meta( $term->term_id, 'shortname', $_POST['shortname']);			
				}

				if(isset($_POST['default_storage'])){

					update_term_meta( $term->term_id, 'default_storage', $_POST['default_storage']);			
				}
				
				if(isset($_POST['visibility_'.$term->slug])){

					update_term_meta($term->term_id, 'visibility', $_POST['visibility_'.$term->slug],false);			
				}
				
				if(isset($_POST['gallery_section'])){

					update_term_meta( $term->term_id, 'gallery_section', $_POST['gallery_section']);			
				}				
				
				if(isset($_POST['addon_range'])){

					update_term_meta( $term->term_id, 'addon_range', $_POST['addon_range']);			
				}
				
				if(isset($_POST['range_type'])){

					update_term_meta( $term->term_id, 'range_type', $_POST['range_type']);			
					
					
				}
				
				if(isset($_POST['account_storages'])){

					update_term_meta( $term->term_id, 'account_storages', $_POST['account_storages']);			
				}
				
				do_action('ltple_save_layer_fields',$term);
			}
		}
	}
	
	public function save_library_fields($term_id){

		if( $this->parent->user->can_edit ){
			
			//collect all term related data for this new taxonomy
			
			$term = get_term($term_id);

			//save our custom fields as wp-options
			
			if( $term->taxonomy == 'css-library' ){
				
				if( isset($_POST['css_url']) && isset($_POST['css_content']) ){
					
					$css_url = sanitize_url($_POST['css_url']);
					
					$css_parse 	= isset($_POST['css_parse']) && $_POST['css_parse'] == 'on' ? 'on' : 'off';
				
					$css_version = '1.0.7';
					
					$styleName = $css_parse == 'on' ? 'style-' . $term->term_id : '';
					
					$styleClass = !empty($styleName) ? '.' . $styleName : '';
					
					$css_content_input = $_POST['css_content']; // sanitize input
					
					if( $css_parse == 'on' ){
						
						$css_content = $this->parse_css_content($css_content_input, $styleClass);
					}
					else{
						
						$css_content = $css_content_input;
					}
					
					$attach_id = get_term_meta($term->term_id, 'css_attachment', true);
					
					$css_md5 = get_term_meta($term->term_id, 'css_md5', true);
										
					$md5 = md5($css_url.$css_content.$styleName.$attach_id.$css_version);

					if( $css_md5 != $md5 ){
						
						$content = '';
						$mime = 'text/css';
						
						if( !empty($css_url) ){
							
							$response = wp_remote_get($css_url);

							if ( is_array( $response ) ) {
								
								$body = $response['body'];
								
								if( $content_type = wp_remote_retrieve_header($response,'content-type') ){
									
									if( strpos($content_type,';') !== false ){
									
										$mime = strtok($content_type,';');
									}
									else{
										
										$mime = $content_type;
									}
								}
								
								if( !empty($body) && !empty($mime) ){
									
									if( $css_parse == 'on' ){
									
										$content .= $this->parse_css_content($body, $styleClass, $css_url);
									}
									else{
										
										$content .= $body . PHP_EOL;
									}
								}
							}
						}
						
						$content .= $css_content;
						
						if( !empty($mime) && !empty($content) ){
							
							// remove current attachment
							
							$css_attachment = get_post($attach_id);
							
							if(!empty($css_attachment)){
								
								wp_delete_attachment( $css_attachment->ID, true );
							}				
						
							// add style to media
							
							if ( !function_exists('media_handle_upload') ) {
								
								require_once(ABSPATH . "wp-admin" . '/includes/image.php');
								require_once(ABSPATH . "wp-admin" . '/includes/file.php');
								require_once(ABSPATH . "wp-admin" . '/includes/media.php');
							}

							// create archive
							
							$ext = 'css';
							
							$tmp = wp_tempnam($term->slug) . '.' . $ext;
							
							file_put_contents($tmp,$content);
							
							$file_array = array(
							
								'name' 		=> $term->slug . '.' . $ext,
								'type' 		=> $mime,
								'tmp_name' 	=> $tmp,
							);
							
							$post_data = array(
							
								'post_title' 		=> $term->slug,
								'post_mime_type' 	=> $mime,
							);

							if(!defined('ALLOW_UNFILTERED_UPLOADS')) define('ALLOW_UNFILTERED_UPLOADS', true);
							
							$attach_id = media_handle_sideload( $file_array, null, null, $post_data );
							
							if( is_numeric($attach_id) ){
								
								update_post_meta($attach_id,'ltple_upload_dest','editor');
								
								update_term_meta($term->term_id,'css_attachment',$attach_id);
							
								update_term_meta($term->term_id,'css_url',$css_url);			

								update_term_meta($term->term_id,'css_content',$css_content_input);			
							
								update_term_meta($term->term_id,'css_parse', $css_parse);
								
								update_term_meta($term->term_id,'css_md5',$md5);								
							}
						}
					}
				}
			}
			elseif( $term->taxonomy == 'js-library' ){
				
				$js_url = sanitize_url($_POST['js_url']);

				$skip = isset($_POST['js_skip_local']) && $_POST['js_skip_local'] == 'on' ? 'on' : 'off';
				
				$js_version = '1.0.7';
				
				$js_content = isset($_POST['js_content']) ? stripslashes($_POST['js_content']) : '';
				
				$attach_id = get_term_meta($term->term_id, 'js_attachment', true);
				
				$js_md5 = get_term_meta($term->term_id, 'js_md5', true);
									
				$md5 = md5($js_url.$js_content.$attach_id.$js_version);
				
				if( $js_md5 != $md5 ){
					
					$content = '';
					$mime = 'text/javascript';

					if( !empty($js_url) ){
						
						$response = wp_remote_get($js_url);
						
						if ( is_array( $response ) ) {
							
							$body = $response['body'];
							
							if( !empty($body) && !empty($mime) ){
								
								$content .= $body; // TODO parse JS content
							}
						}
					}
					
					if( !empty($js_content) ){
					
						$content .= PHP_EOL . $js_content;
					}
					
					if( !empty($content) ){
						
						// remove current attachment
						
						$js_attachment = get_post($attach_id);
						
						if(!empty($js_attachment)){
							
							wp_delete_attachment( $js_attachment->ID, true );
						}				
					
						// add style to media
						
						if ( !function_exists('media_handle_upload') ) {
							
							require_once(ABSPATH . "wp-admin" . '/includes/image.php');
							require_once(ABSPATH . "wp-admin" . '/includes/file.php');
							require_once(ABSPATH . "wp-admin" . '/includes/media.php');
						}

						// create archive
						
						$ext = 'js';
						
						$tmp = wp_tempnam($term->slug) . '.' . $ext;
						
						file_put_contents($tmp,$content);
						
						$file_array = array(
						
							'name' 		=> $term->slug . '.' . $ext,
							'type' 		=> $mime,
							'tmp_name' 	=> $tmp,
						);
						
						$post_data = array(
						
							'post_title' 		=> $term->slug,
							'post_mime_type' 	=> $mime,
						);

						if(!defined('ALLOW_UNFILTERED_UPLOADS')) define('ALLOW_UNFILTERED_UPLOADS', true);
						
						$attach_id = media_handle_sideload( $file_array, null, null, $post_data );
						
						if( is_numeric($attach_id) ){
							
							update_post_meta($attach_id,'ltple_upload_dest','editor');
							
							update_term_meta($term->term_id,'js_attachment',$attach_id);
						
							update_term_meta($term->term_id,'js_url',$js_url);			

							update_term_meta($term->term_id,'js_content',base64_encode($js_content));			
						
							update_term_meta($term->term_id,'js_skip_local',$skip);

							update_term_meta($term->term_id,'js_md5',$md5);								
						}
					}
				}
			}
			elseif( $term->taxonomy == 'font-library' ){
					
				if( isset($_POST['font_family']) ){
					
					$font_family = sanitize_text_field($_POST['font_family']);
					
					update_term_meta($term->term_id,'font_family',$font_family);
				}
					
				if( isset($_POST['font_url']) ){
					
					$font_url = sanitize_url($_POST['font_url']);
					
					$font_version = '1.0.7';
				
					$attach_id = get_term_meta($term->term_id, 'font_attachment', true);
					
					$font_attachment = get_post($attach_id);
							
					$font_md5 = get_term_meta($term->term_id, 'font_md5', true);
									
					$md5 = md5($font_url.$attach_id.$font_version);
					
					if( $font_md5 != $md5 || empty($font_attachment) ){
						
						$fonts = array(
							
							'text/css'		=> 'css',
							
							'font/otf'		=> 'otf',
							'font/ttf'		=> 'ttf',
							'font/woff'		=> 'woff',
							'font/woff2'	=> 'woff2',
							
							'application/font-otf' 	=> 'otf',
							'application/font-ttf' 	=> 'ttf',
							'application/font-woff' => 'woff',
							'application/font-woff2' => 'woff2',
							
							'application/vnd.ms-fontobject'	=> 'eot',
						);	
						
						$content = '';
						$ext = '';
						
						if( !empty($font_url) ){
							
							$response = wp_remote_get($font_url);
							
							if ( is_array( $response ) ) {
								
								$body = $response['body'];

								$mime = wp_remote_retrieve_header($response,'content-type');
								
								$mime = strtok($mime,';');
								
								if( !empty($body) && !empty($mime) && isset($fonts[$mime]) ){
									
									$ext = $fonts[$mime];
									
									$content .= $this->parse_font_content($body,$font_url);
								}
							}
						}
						
						if( !empty($content) && !empty($ext) ){
							
							// remove current attachment
							
							if( !empty($font_attachment) ){
								
								wp_delete_attachment( $font_attachment->ID, true );
							}				
						
							// add style to media
							
							if ( !function_exists('media_handle_upload') ) {
								
								require_once(ABSPATH . "wp-admin" . '/includes/image.php');
								require_once(ABSPATH . "wp-admin" . '/includes/file.php');
								require_once(ABSPATH . "wp-admin" . '/includes/media.php');
							}

							// create archive
							
							$tmp = wp_tempnam($term->slug) . '.' . $ext;
							
							file_put_contents($tmp,$content);
							
							$file_array = array(
							
								'name' 		=> $term->slug . '.' . $ext,
								'type' 		=> $mime,
								'tmp_name' 	=> $tmp,
							);
							
							$post_data = array(
							
								'post_title' 		=> $term->slug,
								'post_mime_type' 	=> $mime,
							);

							if(!defined('ALLOW_UNFILTERED_UPLOADS')) define('ALLOW_UNFILTERED_UPLOADS', true);
							
							$attach_id = media_handle_sideload( $file_array, null, null, $post_data );
							
							if( is_numeric($attach_id) ){
								
								update_term_meta($term->term_id,'font_attachment',$attach_id);					
								
								update_term_meta($term->term_id,'font_url',$font_url);			
				
								update_term_meta($term->term_id,'font_md5',$md5);								
							}
						}
					}
				}
			}
		}
	}
	
	public function get_static_dir_url($postId,$output){
		
		$static_url = '';
		
		if( $output == 'hosted-page' ){
			
			$static_url = ( defined('LTPLE_LAYER_URL') ? LTPLE_LAYER_URL : $this->parent->urls->home . '/t/') . $postId . '/';	
		}
	
		return $static_url;
	}	
	
	public function get_static_url($postId,$defaultId,$output=''){
		
		$layerStaticUrl = get_post_meta( $defaultId, 'layerStaticUrl', true );
		
		if( empty($layerStaticUrl) ){
			
			$layerStaticUrl = 'index.html';
		}
		
		$static_url = $this->sanitize_url( $this->get_static_dir_url($postId,$output) . $layerStaticUrl );				
	
		return $static_url;
	}
	
	public function get_static_asset_url($postId, $type = 'css', $filename = 'style'){
		
		$static_url = ( defined('LTPLE_LAYER_URL') ? LTPLE_LAYER_URL : $this->parent->urls->home . '/t/') . $postId . '/assets/'.$type.'/' . $filename . '.' . $type;
		
		return $static_url;
	}
	
	public function get_static_dir($postId,$empty=false){
		
		$static_dir = ( defined('LTPLE_LAYER_DIR') ? LTPLE_LAYER_DIR : ABSPATH . 't/'). $postId;

		return $static_dir;
	}
	
	public function get_static_asset_dir($postId, $type = 'css'){
		
		$static_dir = ( defined('LTPLE_LAYER_DIR') ? LTPLE_LAYER_DIR : ABSPATH . 't/') . $postId . '/assets/' . $type;
		
		return $static_dir;
	}	
	
	public function get_static_path( $postId, $defaultId ){
		
		$static_path = '';
		
		$layerStaticUrl = get_post_meta( $defaultId, 'layerStaticUrl', true );
		
		if( empty($layerStaticUrl) ){
			
			$layerStaticUrl = 'index.html';
		}
	
		return $this->get_static_dir( $postId ) . '/' . $layerStaticUrl;
	}
	
	public function get_static_asset_path( $postId, $type = 'css', $filename = 'style' ){
		
		$static_path = $this->get_static_asset_dir( $postId, $type ) . '/' . $filename . '.' . $type;
	
		return $static_path;
	}
	
	public function upload_image_template($source = 'php://input', $ext = 'zip'){
		
		if( $this->parent->user->loggedin && !empty($this->id) ){
			
			if ( !function_exists('media_handle_upload') ) {
				
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			}
					
			// get file name
					
			$filename = 'image_template_' . $this->id ;
			
			// get psd content
			
			$tmp = wp_tempnam($source);
			
			$fi = fopen($source, 'rb');
			
			$p = JSON_decode(fread($fi, 2000)); // skip the first 2000 characters

			$fo = fopen($tmp,'wb');  

			while( $row = fread($fi,50000) ){
				
				fwrite($fo,$row);
			}
			
			fclose($fi);  
			fclose($fo);
			
			if( $ext == 'zip' ){
			
				// create archive
				
				$tmpa = wp_tempnam($tmp);
				
				$zip = new ZipArchive;
				
				if( $zip->open($tmpa, ZipArchive::CREATE ) === TRUE){
					
					// Add random.txt file to zip and rename it to newfile.txt
					
					$zip->addFile($tmp, $filename . '.psd');

					// All files are added, so close the zip file.
					
					$zip->close();
					
					@unlink($tmp);
					
					$tmp = $tmpa;
				}
			}			

			$file_array = array(
			
				'name' 		=> $filename . '.' . $ext,
				'tmp_name' 	=> $tmp,
			);
			
			$post_data = array(
			
				'post_title' => $filename,
			);
			
			$attach_id = media_handle_sideload( $file_array, $this->id, null, $post_data );
			
			@unlink($tmp);
			
			if( is_numeric($attach_id) ) {
				
				$this->delete_layer_attachments($this->id,2);
				
				return true;
			}
		}
		
		return false;
	}
	
	public function set_default_layer_type($object_id, $terms, $tt_ids, $taxonomy){
		
		if( $taxonomy == 'layer-range' ){
			
			foreach( $terms as $range_id ){
				
				if( $range_type = $this->get_range_type($range_id) ){
				
					// update range type
					
					wp_set_object_terms( $object_id, $range_type->term_id, 'layer-type', false );
					
					break;
				}
			}
		}
		
		return $object_id;
	}
	
	public function upload_static_contents($post_id){
		
		if( is_admin() ){
			
			if( !current_user_can('edit_pages', $post_id) ){
				
				return $post_id;
			}
			
			if( !empty($_POST['layerStaticTpl_nonce']) ){
				
				//security verification
				
				if( !wp_verify_nonce($_POST['layerStaticTpl_nonce'], $this->parent->file) ) {
				  
					return $post_id;
				}

				// upload archive
				
				if( !empty( $_FILES['layerStaticTpl']['name'] ) ){
					
					// Setup the array of supported file types. In this case, it's just PDF.
					
					$supported_types = array('application/zip','application/tar');			
				
					// Get the file type of the upload
					
					$arr_file_type = wp_check_filetype(basename($_FILES['layerStaticTpl']['name']));
					
					$uploaded_type = $arr_file_type['type'];		
				
					// Check if the type is supported. If not, throw an error.
					
					if( in_array($uploaded_type,$supported_types) ){

						$zip = new ZipArchive;
						
						if( $res = $zip->open($_FILES['layerStaticTpl']['tmp_name']) ) {
							
							$zip->extractTo( $this->get_static_dir($post_id,true) . '/' );
							
							$zip->close();
							
							return $post_id;
						}
						else {
							
							wp_die("Error extracting the archive...");
						}
					}
					else{
						
						wp_die("The file type that you've uploaded is not an archive (zip, tar)");
					}
				}	
			}
			elseif( !empty($_POST['layerImageTpl_nonce']) ){
				
				//security verification
				
				if( !wp_verify_nonce($_POST['layerImageTpl_nonce'], $this->parent->file) ) {
				  
					//return $post_id;
				}
				
				// upload image template
				
				if( !empty( $_FILES['layerImageTpl']['name'] ) ){
					
					// Setup the array of supported file types. In this case, it's just PDF.
					
					$supported_types = array('image/vnd.adobe.photoshop','image/x-xcf');			
				
					// Get the file type of the upload
					
					$arr_file = wp_check_filetype(basename($_FILES['layerImageTpl']['name']));
					
					// Check if the type is supported. If not, throw an error.
					
					if( in_array($arr_file['type'],$supported_types) ){
						
						$file = 'layerImageTpl';
									
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK ) {
							
							if( intval($_FILES[$file]['error']) != 4 ){
								
								echo "upload error : " . $_FILES[$file]['error'];
								exit;
							}
						}
						else{
						
							//require the needed files
							
							require_once(ABSPATH . "wp-admin" . '/includes/image.php');
							require_once(ABSPATH . "wp-admin" . '/includes/file.php');
							require_once(ABSPATH . "wp-admin" . '/includes/media.php');
													
							// get file name
					
							$filename = 'image_template_' . $post_id;
								
							// create archive
							
							$ext = 'zip';
							
							$tmp = $_FILES[$file]['tmp_name'];
							
							$tmpa = wp_tempnam($tmp);
							
							$zip = new ZipArchive;
							
							if( $zip->open($tmpa, ZipArchive::CREATE ) === TRUE){
								
								// Add random.txt file to zip and rename it to newfile.txt
								
								$zip->addFile($tmp, $filename . '.psd');

								// All files are added, so close the zip file.
								
								$zip->close();
								
								//@unlink($tmp);
								
								$tmp = $tmpa;
								
								$file_array = array(
								
									'name' 		=> $filename . '.' . $ext,
									'tmp_name' 	=> $tmp,
								);
								
								$post_data = array(
								
									'post_title' => $filename,
								);
								
								$attach_id = media_handle_sideload( $file_array, $post_id, null, $post_data );
								
								if( is_numeric($attach_id) ){
									
									return $this->delete_layer_attachments($post_id,2);
								}
								elseif( !empty($attach_id['error']) ){
								
									wp_die($attach_id['error']);
								}
								else{
										
									wp_die('Error uploading template...');									
								}	
							}
						}						
					}
					else{
						
						wp_die("The file type that you've uploaded is not an image template (psd, xfc, sketch)");
					}
				}
			}
		}
	}
	
	public function delete_layer_attachments($post_id,$keep_last = 1){
		
		$attachments = $this->get_layer_attachments($post_id);
		
		if( count($attachments) > $keep_last ){
			
			$i = 1;
			
			foreach( $attachments as $attachment ){
				
				if( $i > $keep_last ){
					
					wp_delete_attachment( $attachment->ID, true );
				}
				
				++$i;
			}
		}
		
		return $post_id;
	}
	
	public function download_static_contents($post_id){
		
		// get the path
		
		$rootPath = $this->get_static_dir($post_id);
		
		// remove previous archive
		
		if( file_exists($rootPath . '/template.zip') ){
		
			unlink($rootPath . '/template.zip');
		}
		
		// get the archive
		
		$zip = new ZipArchive();
		$zip->open( $rootPath . '/template.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach( $files as $name => $file ){
			
			// Skip directories (they would be added automatically)
			
			if ( !$file->isDir() ){
				
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		
		// output the archive	
		
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="template.zip"');
		header('Content-Length: ' . filesize($archive));
		
		echo file_get_contents( $rootPath . '/template.zip' );	
		
		// remove current archive
		
		unlink( $rootPath . '/template.zip' );		
		
		exit;
	}
	
	public function delete_static_contents($post_id){
		
		if( $post = get_post($post_id) ){
			
			if( $post->post_type == 'cb-default-layer' || $post->post_type == 'user-layer' ){
				
				$layer_type = $this->get_layer_type($post);

				if( $layer_type->output == 'image' ){
					
					$this->delete_layer_attachments($post->ID,0);
				}
			}
		}
		
		return true;
	}

	public function render_output($layer){
		
		$content = '';
		
		if( !empty($layer->output) ){
			
			$output = $layer->output;
			
			$this->parse_hosted_content($output);
			
			ob_start();
			
			if( file_exists( $this->parent->views . '/layers/' . $output  . '.php' ) ){
				
				include_once( $this->parent->views . '/layers/' . $output  . '.php' );
			}
			else{
				
				$layer = apply_filters('ltple_' . $output . '_layer','');
			}
			
			do_action( 'ltple_layer_loaded', $layer );
			
			$content = ob_get_clean();
		}
		
		return $content;
	}
	
	public function filter_local_layer_head(){
		
		if( $this->parent->profile->id > 0 ){
			
			$post = $this->parent->profile->get_profile_post();
		}
		else{
			
			$post = get_post();
		}
		
		if( !isset($_REQUEST['uri']) && $this->is_local($post) && !empty($this->layerOutput) && $this->layerOutput == 'hosted-page' ){
			
			if( !empty($this->layerHeadContent) )
			
				echo $this->layerHeadContent;
		}
	}
	
	public function filter_local_layer_content($content,$layer){
		
		if( $layer->output == 'hosted-page' ){
			
			if( $layer->area == 'backend' || $layer->output == 'hosted-page' ){
				
				$content = '<div class="' . $this->get_layer_classes($layer->ID) . '">' . do_shortcode($content) . '</div>';
			}
			elseif( $layer->area == 'frontend' && $this->layerEcho === true ){
				
				$content = $this->layerBodyContent;
			}
		}
		
		return $content;
	}
	
	public function get_layer_classes($layer_id){
		
		$classes = array();
		
		if( $defaultId = $this->get_default_id($layer_id) ){
		
			$classes[] = 'layer-' . $defaultId;
		}
		
		if( $defaultId != $layer_id ){
			
			$classes[] = 'layer-' . $layer_id;
		}
		
		// TODO get libraries by layer id instead of globaly
		
		if( !empty($this->layerCssLibraries) ){
			
			foreach( $this->layerCssLibraries as $library ){
				
				$classes[] = $library->prefix;
			}
		}
		
		return !empty($classes) ? implode(' ',$classes) : '';
	}
	
	public function get_preview_layer_link($url,$post=null){
		
		if( is_null($post) ){
			
			if( is_admin() && !empty($_GET['post']) ){
				
				$post = get_post($_GET['post']);
			}
			else{
				
				global $post;
			}
		}
		
		if( !empty($post) && $post->post_type == 'cb-default-layer' ){
			
			if( $post->post_status == 'publish' ){
			
				$url = $this->parent->urls->home . '/preview/' . $post->post_name . '/';
			}
			else{
				
				$url = add_query_arg(array(
					
					'post_type' => $post->post_type,
					'p' 		=> $post->ID,
					'preview' 	=> 'true',
					'_'			=> time(),
					
				),get_home_url());
			}
		}
		
		return $url;
	}
	
	public function add_local_layer_scripts( $layer ){
		
		if( !isset($_GET['uri']) ){
			
			if( $layer->area == 'backend' || $layer->output == 'hosted-page' ){
				
				if( !empty($this->layerFontLibraries) ){
					
					$fontsLibraries = array();
					
					foreach($this->layerFontLibraries as $term){
						
						$font_url = $this->get_font_parsed_url($term);
						
						if( !empty($font_url) ){
							
							wp_register_style( $this->parent->_token . '-font-' . $term->slug, $font_url, array(), null); // null to enqueue multiple fonts
							wp_enqueue_style( $this->parent->_token . '-font-' . $term->slug );
						}
					}
				}
				
				if( !empty($this->layerCssLibraries) ){
					
					foreach($this->layerCssLibraries as $library){
						
						wp_register_style( $this->parent->_token . '-' . $library->prefix, $library->url, array());
						wp_enqueue_style( $this->parent->_token . '-' . $library->prefix );
					}
				}
				
				if( !empty($this->defaultCss) ){
					
					$defaultCss = $this->parse_css_content($this->defaultCss, '.layer-' . $this->defaultId);
					
					wp_register_style( $this->parent->_token . '-layer-default-css', false, array());
					wp_enqueue_style( $this->parent->_token . '-layer-default-css' );
				
					wp_add_inline_style( $this->parent->_token . '-layer-default-css',$defaultCss);
				}

				if( !empty($this->layerCss) ){

					add_action('wp_head',function(){
						
						// css editor
						
						echo '<style id="LiveTplEditorStyleSheet">'.$this->layerCss.'</style>'.PHP_EOL;
					});
				}
				
				// TODO JS scripts
				
			}
		}
		
		return $layer;
	}
	
	public function output_static_layer( $output ){

		if( !empty($output) ){
			
			if( isset($_GET['filetree']) && $this->is_hosted_output($this->layerOutput) ){
				
				echo'<!DOCTYPE html>';

				echo'<head>';

					echo'<meta charset="utf-8">';
					echo'<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
					echo'<meta name="viewport" content="width=device-width, initial-scale=1">';
					
					echo'<title></title>';
					
					echo'<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">';
					echo'<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet" type="text/css">';
					echo'<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">';
					echo'<link href="' . $this->parent->assets_url . 'css/filetree.css" rel="stylesheet" type="text/css">';
					
					echo'<style>';
					echo'body { background-color:#182f42; color:#fff; font-family:\'Quicksand\';}';
					echo'</style>';
					
				echo'</head>';

				echo'<body>';
					
					echo'<div class="filetree">';
					
						echo $this->get_filetree( $this->layerStaticDir );

					echo'</div>';
					
					echo'<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>';
					echo'<script src="' . $this->parent->assets_url . 'js/filetree.js"></script>';

				echo'</body>';
				
				exit;
			}
			else{
				
				echo $output;
			}
		}
	}

	public function get_filetree( $dir, $main = true ){   
		
		$filetree = '';
		
		if($main){

			$filetree .= '<ul class="main-tree">';
			
				//$dirname = basename($dir);
				
				$dirname = 'Template';
			
				$filetree .= '<li class="tree-title">' . $dirname . '</li>';
			
				$filetree .= $this->get_filetree($dir,false);
			
			$filetree .= '</ul>';
		}		
		else{
			
			$files = array_map('basename', glob( $dir . '/*' ));
			
			if( !empty($files) ){

				foreach( $files as $file ) {
					
					if( is_dir( $dir . '/' . $file ) ) {
						
						$filetree .= '<ul class="tree">';
					
							$filetree .= '<li class="tree-title">' . $file . '</li>';
					
							$filetree .= $this->get_filetree( $dir . '/' . $file, false );
					
						$filetree .= '</ul>';
					} 
					else{
						
						$filetree .= '<li class="tree-item">' . $file . '</li>';
					}
				}
			}
		}
		
		return $filetree;
	}
}

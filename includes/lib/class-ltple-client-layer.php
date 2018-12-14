<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer extends LTPLE_Client_Object { 
	
	public $parent;
	public $id				= -1;
	public $defaultId		= -1;
	public $uri				= '';
	public $key				= ''; // gives the server proxy access to the layer
	public $slug			= '';
	public $title			= '';
	public $type			= '';
	public $form			= '';
	public $embedded		= '';
	public $outputs			= '';
	public $types			= array(); 
	public $ranges			= array(); 
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
			'rewrite' 				=> array('slug'=>'default-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'excerpt', 'thumbnail' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		)); 

		$this->parent->register_post_type( 'user-layer', __( 'User Templates', 'live-template-editor-client' ), __( 'User Template', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-layer',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'user-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_taxonomy( 'layer-type', __( 'Template Type', 'live-template-editor-client' ), __( 'Template Type', 'live-template-editor-client' ),  array('user-plan','cb-default-layer'), array(
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
		
		$this->parent->register_taxonomy( 'layer-range', __( 'Template Range', 'live-template-editor-client' ), __( 'Template Range', 'live-template-editor-client' ), array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> true,
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
		
		$this->parent->register_taxonomy( 'account-option', __( 'Template Options', 'live-template-editor-client' ), __( 'Account Option', 'live-template-editor-client' ),  array('user-plan'), array(
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
		
		$this->parent->register_taxonomy( 'css-library', __( 'CSS Library', 'live-template-editor-client' ), __( 'CSS Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
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
		
		$this->parent->register_taxonomy( 'js-library', __( 'JS Library', 'live-template-editor-client' ), __( 'JS Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
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
		
		$this->parent->register_taxonomy( 'font-library', __( 'Font Library', 'live-template-editor-client' ), __( 'Font Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
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
		
		add_action( 'add_meta_boxes', function(){

			global $post;
			
			if( $post->post_type == 'cb-default-layer' ){
				
				$layer_type = $this->get_layer_type($post);
				
				if( empty($_REQUEST['post']) ){
					
					// remove all metaboxes except submit button
					
					global $wp_meta_boxes;
					
					$submitbox = $wp_meta_boxes[$post->post_type]['side']['core']['submitdiv'];

					$wp_meta_boxes[$post->post_type]['side']['core'] 	= array( 'submitdiv' => $submitbox );
					$wp_meta_boxes[$post->post_type]['side']['low'] 	= array();
					$wp_meta_boxes[$post->post_type]['normal'] 			= array();
					$wp_meta_boxes[$post->post_type]['advanced'] 		= array();
				}

				$this->parent->admin->add_meta_box (
				
					'tagsdiv-layer-type',
					__( 'Template Type', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				$this->parent->admin->add_meta_box ( 
				
					'layer-rangediv',
					__( 'Template Range', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				if( !empty($_REQUEST['post']) ){
					
					$this->parent->admin->add_meta_box (
						
						'layer-user',
						__( 'Template User ID', 'live-template-editor-client' ), 
						array($post->post_type),
						'side'
					);				
			
					$this->parent->admin->add_meta_box (
						
						'layer-content',
						__( 'Template HTML', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);
					
					$this->parent->admin->add_meta_box (
						
						'layer-visibility',
						__( 'Template Visibility', 'live-template-editor-client' ), 
						array($post->post_type),
						'side'
					);				
					
					$this->parent->admin->add_meta_box (
						
						'layer-elements',
						__( 'Template Elements', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);		
					
					if( $layer_type->output != 'inline-css' ){
						
						$this->parent->admin->add_meta_box (
							
							'layer-css',
							__( 'Template CSS', 'live-template-editor-client' ), 
							array($post->post_type),
							'advanced'
						);
					}				
					
					if( $layer_type->output == 'hosted-page' || $layer_type->output == 'downloadable' ){
					
						$this->parent->admin->add_meta_box (
							
							'layer-js',
							__( 'Template Javascript', 'live-template-editor-client' ), 
							array($post->post_type),
							'advanced'
						);
						
						$this->parent->admin->add_meta_box (
							
							'layer-static-url',
							__( 'Template Static Content', 'live-template-editor-client' ), 
							array($post->post_type),
							'advanced'
						);
						
						$this->parent->admin->add_meta_box (
							
							'layer-meta',
							__( 'Template Meta Data', 'live-template-editor-client' ), 
							array($post->post_type),
							'advanced'
						);
					}
					else{
						
						$this->parent->admin->add_meta_box (
							
							'layer-margin',
							__( 'Template Margin', 'live-template-editor-client' ), 
							array($post->post_type),
							'side'
						);

						remove_meta_box( 'css-librarydiv', $post->post_type, 'side' );
						remove_meta_box( 'js-librarydiv', $post->post_type, 'side' );
						remove_meta_box( 'font-librarydiv', $post->post_type, 'side' );					
					}
					
					if( $layer_type->output == 'inline-css' || $layer_type->output == 'external-css' ){
					
						$this->parent->admin->add_meta_box (
							
							'layer-form',
							__( 'Template Form', 'live-template-editor-client' ), 
							array($post->post_type),
							'side'
						);
					}
					
					/*
					$this->parent->admin->add_meta_box (
						
						'layer-options',
						__( 'Template Options', 'live-template-editor-client' ), 
						array($post->post_type),
						'side'
					);
					*/
				}
			}
			elseif( $post->post_type == 'user-layer' || ( !empty($this->parent->settings->options->postTypes) && in_array( $post->post_type, $this->parent->settings->options->postTypes ) ) ){
				
				$this->parent->admin->add_meta_box (
				
					'default_layer_id',
					__( 'Default Layer', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				if( !empty($_REQUEST['post']) ){
					
					if( in_array( $post->post_type, $this->parent->settings->options->postTypes ) ){
					
						// get default layer id
						
						$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true));
						
						if( $post->layer_id == 0 ){
							
							return;
						}
					}
					
					$this->parent->admin->add_meta_box (
						
						'layer-content',
						__( 'Template HTML', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					); 				
					
					$this->parent->admin->add_meta_box (
						
						'layer-css',
						__( 'Template CSS', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);
					
					$this->parent->admin->add_meta_box (
						
						'layer-js',
						__( 'Template Javascript', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);
				}
			}
		});		

		add_action('account-option_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('account-option_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );	
	
		add_filter('manage_edit-account-option_columns', array( $this, 'set_account_option_columns' ) );
		add_filter('manage_account-option_custom_column', array( $this, 'add_layer_column_content' ),10,3);			
	
		add_action('create_account-option', array( $this, 'save_layer_fields' ) );
		add_action('edit_account-option', array( $this, 'save_layer_fields' ) );	

		add_action('layer-type_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-type_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		add_filter('manage_edit-layer-type_columns', array( $this, 'set_layer_type_columns' ) );
		add_filter('manage_layer-type_custom_column', array( $this, 'add_layer_column_content' ),10,3);		
		
		add_action('create_layer-type', array( $this, 'save_layer_fields' ) );
		add_action('edit_layer-type', array( $this, 'save_layer_fields' ) );	

		add_action('layer-range_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-range_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		add_filter('manage_edit-layer-range_columns', array( $this, 'set_layer_range_columns' ) );
		add_filter('manage_layer-range_custom_column', array( $this, 'add_layer_column_content' ),10,3);
	
		add_action('create_layer-range', array( $this, 'save_layer_fields' ) );
		add_action('edit_layer-range', array( $this, 'save_layer_fields' ) );			

		add_action('css-library_edit_form_fields', array( $this, 'get_css_library_fields' ) );	
		add_action('create_css-library', array( $this, 'save_library_fields' ) );
		add_action('edit_css-library', array( $this, 'save_library_fields' ) );	

		add_action('js-library_edit_form_fields', array( $this, 'get_js_library_fields' ) );	
		add_action('create_js-library', array( $this, 'save_library_fields' ) );
		add_action('edit_js-library', array( $this, 'save_library_fields' ) );	
		
		add_action('font-library_edit_form_fields', array( $this, 'get_font_library_fields' ) );		
		add_action('create_font-library', array( $this, 'save_library_fields' ) );
		add_action('edit_font-library', array( $this, 'save_library_fields' ) );			
		
		add_filter('init', array( $this, 'init_layer' ));
		
		add_filter('admin_init', array( $this, 'init_layer_backend' ));
		
		add_action('wp_loaded', array($this,'get_layer_types'));
		add_action('wp_loaded', array($this,'get_layer_ranges'));
		add_action('wp_loaded', array($this,'get_account_options'));
		add_action('wp_loaded', array($this,'get_js_libraries'));
		add_action('wp_loaded', array($this,'get_css_libraries'));
		add_action('wp_loaded', array($this,'get_font_libraries'));
		//add_action('wp_loaded', array($this,'get_default_layers'));
		
		add_action( 'save_post', array($this,'upload_static_contents'), 10, 3 );
		
		add_action( 'before_delete_post', array($this,'delete_static_contents'), 10, 3 );
	
		add_action( 'ltple_layer_loaded', array($this,'output_static_layer') );
	}
	
	public function get_layer_outputs(){
		
		if( empty($this->outputs) ){
		
			$this->outputs = array(
					
				'inline-css'	=>'HTML',
				'external-css'	=>'HTML + CSS',
				'hosted-page'	=>'Hosted',
				'downloadable'	=>'Downloadable',
				'canvas'		=>'Canvas'
			);
			
			do_action('ltple_layer_outputs');
		}
		
		return $this->outputs;
	}
	
	public function get_layer_types(){

		$this->types = $this->get_terms( 'layer-type', array(
				
			'emails'  			=> 'Emails',
			'memes'  			=> 'Memes',
			'pricing-tables'	=> 'Pricing Tables',
			'sandbox' => array(
			
				'name' 		=> 'Sandbox',
				'options' 	=> array(
				
					'visibility'	=> 'admin',
				),
			),
		));		
	}
	
	public function get_layer_ranges(){
		
		$this->ranges = $this->get_terms( 'layer-range',array(
				
			'demo' => array(
			
				'name' 		=> 'Demo',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
		));
	}
	
	public function get_account_options(){

		$this->accountOptions = $this->get_terms( 'account-option', array(

			'1-template-storage' => array(
			
				'name' 		=> '+1 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 1,
					'storage_unit' 	=> 'templates',
				),
			),
			'5-template-storage' => array(
			
				'name' 		=> '+5 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 5,
					'storage_unit' 	=> 'templates',
				),
			),
			'10-template-storage' => array(
			
				'name' 		=> '+10 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 10,
					'storage_unit' 	=> 'templates',
				),
			),	
			'tailored-page-fee' => array(
			
				'name' 		=> 'Tailored Page Fee',
				'options' 	=> array(
				
					'price_amount'	=> 200,
					'price_period' 	=> 'once',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
			'tailored-multi-page-fee' => array(
			
				'name' 		=> 'Tailored Multi-Page Fee',
				'options' 	=> array(
				
					'price_amount'	=> 400,
					'price_period' 	=> 'once',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),			
			'seo-basic' => array(
			
				'name' 		=> 'SEO Basic',
				'options' 	=> array(
				
					'price_amount'	=> 100,
					'price_period' 	=> 'month',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
		));
	}
	
	
	public function get_css_libraries(){
		
		$this->cssLibraries = $this->get_terms( 'css-library', array(
			
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
							'css_content' => '<style>.card .card-image{height:auto;}</style>',
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
			'elementor-1-2-3' => array(
			
				'name' 		=> 'Elementor 1.2.3',
				'options' 	=> array(
				
					'css_url'	 => '',
					'css_content' =>''
						. '<link href="' . plugins_url('elementor/assets/css/animations.min.css?ver=1.0.1') . '" rel="stylesheet" type="text/css"/>'
						. '<link href="' . plugins_url('elementor/assets/css/frontend.min.css?ver=1.0.1') . '" rel="stylesheet" type="text/css"/>'
						. '<style>.elementor-widget-heading .elementor-heading-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-image .widget-image-caption{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-text-editor{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-button .elementor-button{font-family:Roboto,sans-serif;font-weight:500;background-color:#61ce70}.elementor-widget-divider .elementor-divider-separator{border-top-color:#7a7a7a}.elementor-widget-image-box .elementor-image-box-content .elementor-image-box-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-image-box .elementor-image-box-content .elementor-image-box-description{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-icon.elementor-view-stacked .elementor-icon{background-color:#6ec1e4}.elementor-widget-icon.elementor-view-framed .elementor-icon,.elementor-widget-icon.elementor-view-default .elementor-icon{color:#6ec1e4;border-color:#6ec1e4}.elementor-widget-icon-box.elementor-view-stacked .elementor-icon{background-color:#6ec1e4}.elementor-widget-icon-box.elementor-view-framed .elementor-icon,.elementor-widget-icon-box.elementor-view-default .elementor-icon{color:#6ec1e4;border-color:#6ec1e4}.elementor-widget-icon-box .elementor-icon-box-content .elementor-icon-box-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-icon-box .elementor-icon-box-content .elementor-icon-box-description{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-image-gallery .gallery-item .gallery-caption{font-family:Roboto,sans-serif;font-weight:500}.elementor-widget-image-carousel .elementor-image-carousel-caption{font-family:Roboto,sans-serif;font-weight:500}.elementor-widget-icon-list .elementor-icon-list-icon i{color:#6ec1e4}.elementor-widget-icon-list .elementor-icon-list-text{color:#54595f;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-counter .elementor-counter-number-wrapper{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-counter .elementor-counter-title{color:#54595f;font-family:Roboto\ Slab,sans-serif;font-weight:400}.elementor-widget-progress .elementor-progress-wrapper .elementor-progress-bar{background-color:#6ec1e4}.elementor-widget-progress .elementor-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-testimonial .elementor-testimonial-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-testimonial .elementor-testimonial-name{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-testimonial .elementor-testimonial-job{color:#54595f;font-family:Roboto\ Slab,sans-serif;font-weight:400}.elementor-widget-tabs .elementor-tab-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-tabs .elementor-tab-title.active{color:#61ce70}.elementor-widget-tabs .elementor-tab-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-accordion .elementor-accordion .elementor-accordion-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-accordion .elementor-accordion .elementor-accordion-title.active{color:#61ce70}.elementor-widget-accordion .elementor-accordion .elementor-accordion-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-toggle .elementor-toggle .elementor-toggle-title{color:#6ec1e4;font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-toggle .elementor-toggle .elementor-toggle-title.active{color:#61ce70}.elementor-widget-toggle .elementor-toggle .elementor-toggle-content{color:#7a7a7a;font-family:Roboto,sans-serif;font-weight:400}.elementor-widget-alert .elementor-alert-title{font-family:Roboto,sans-serif;font-weight:600}.elementor-widget-alert .elementor-alert-description{font-family:Roboto,sans-serif;font-weight:400}</style>'		
					,
				),
			),			
			'animate-3-5-2' => array(
			
				'name' 		=> 'Animate 3.5.2',
				'options' 	=> array(
				
					'css_url'	  => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css',
					'css_content' => '',
				),
			),
			'slick-1-6-0' => array(
			
				'name' 		=> 'Slick 1.6.0',
				'options' 	=> array(
				
					'css_url'	  => 'http://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css',
					'css_content' => '<style>.slick-slide{height:auto !important;}</style>',
				),
			),
		));
	}
	
	public function get_js_libraries(){

		$this->jsLibraries = $this->get_terms( 'js-library', array(
			'jquery-3-1-1' => array(
			
				'name' 		=> 'Jquery 3.1.1',
				'options' 	=> array(
				
					'js_url'	 => 'https://code.jquery.com/jquery-3.1.1.min.js',
					'js_content' => '',
				),
				'children'	=> array(
				
					'jquery-ui-1-12-1' => array(
					
						'name' 		=> 'Jquery UI 1.12.1',
						'options' 	=> array(

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
						'options' 	=> array(

							'js_url'		=> 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
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
					'slick-1-6-0' => array(
					
						'name' 		=> 'Slick 1.6.0',
						'options' 	=> array(
						
							'js_url'		=> plugins_url('elementor/assets/lib/slick/slick.min.js?ver=1.6.0'),
							'js_content'	=> '',
						),
					),					
					'elementor-1-2-3' => array(
					
						'name' 		=> 'Elementor 1.2.3',
						'options' 	=> array(
						
							'js_url'		=> '',
							'js_content'	=> '
								<script>//<![CDATA[
									var elementorFrontendConfig={"isEditMode":"","stretchedSectionContainer":"","is_rtl":""};
								//]]></script>'
								. '<script src="' . plugins_url('elementor/assets/lib/waypoints/waypoints.min.js?ver=4.0.2') . '"></script>' . PHP_EOL
								. '<script src="' . plugins_url('elementor/assets/lib/jquery-numerator/jquery-numerator.min.js?ver=0.2.1') . '"></script>' . PHP_EOL
								. '<script src="' . plugins_url('elementor/assets/js/frontend.min.js?ver=1.2.3') . '"></script>' . PHP_EOL								
								. '<script>
								;(function($){
									$(document).ready(function(){
										//$(\'.slick-slider\').slick("unslick");
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
				'options' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Material+Icons',
				),
			),
			'roboto' => array(
			
				'name' 		=> 'Roboto',
				'options' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700',
				),
			),
		));
	}
	
	public function get_layer_type($post){
		
		$term = null;
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}

		if( isset($post->post_type) ){
			
			if( $post->post_type == 'user-layer' ){
					
				// get default layer id
				
				$default_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
				
				$post = get_post($default_id);
			}
			
			if( $post->post_type == 'cb-default-layer' ){
				
				$terms = wp_get_post_terms($post->ID,'layer-type');
				
				if( !empty($terms[0]) ){
					
					$term = $terms[0];

					if( !$term->output = get_term_meta( $term->term_id, 'output', true ) ){
						
						$term->output = 'inline-css';
					}
				}				
			}
		}
		
		if( !isset($term->output) ){
			
			$term = new stdClass();
			
			$term->output = '';
		}
		
		return $term;
	}
	
	public function init_layer_backend(){
		
		add_filter('show_user_profile', array( $this, 'get_user_layers' ),2,10 );
		add_action('edit_user_profile', array( $this, 'get_user_layers' ) );
		
		add_filter('cb-default-layer_custom_fields', array( $this, 'get_default_layer_fields' ));
		
		add_filter('user-layer_custom_fields', array( $this, 'get_user_layer_fields' ));

		if( !empty($this->parent->settings->options->postTypes) ){
		
			foreach( $this->parent->settings->options->postTypes as $post_type ){
				
				add_filter( $post_type . '_custom_fields', array( $this, 'get_user_layer_fields' ));
			}
		}
	}
		
	public function get_embedded_url(){
		
		$embedded_url = '';
		
		if(isset($_GET['le'])){
			
			$embedded_url = sanitize_text_field($_GET['le']);
		}
		elseif(isset($_POST['postEmbedded'])){
			
			$embedded_url = sanitize_text_field($_POST['postEmbedded']);
		}

		return 	$embedded_url;
	}
	
	public function get_embedded_layer($embedded_url){
		
		$embedded = parse_url($embedded_url);

		parse_str($embedded['query'],$query);

		$embedded = array_merge($embedded,$query);

		foreach($embedded as $i => $e){
			
			if(is_numeric($e)){
				
				$embedded[$i]=intval($e);
			}
		}			
		
		// get url
		
		$embedded['url'] = $embedded_url;
		
		// get title
		
		$embedded_title = '';
		
		if(!empty($_GET['title'])){
			
			$embedded_title = sanitize_text_field($_GET['title']);
		}
		
		$embedded['title'] = $embedded_title;	

		return $embedded;
	}
	
	public function set_uri(){
		
		if( is_admin() ){
			
			if( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'ltple' && !empty($_REQUEST['post']) && intval($_REQUEST['post']) > 0 ){
				
				$this->uri = intval($_REQUEST['post']);
			}				
		}
		else{
			
			if( isset($_GET['uri']) ){
				
				$this->uri = intval($_GET['uri']);
			}	
			elseif( strpos($this->parent->urls->current, $this->parent->urls->editor) === false ){
				
				$this->uri = url_to_postid($this->parent->urls->current);
			}
		}
	}
	
	public function init_layer(){

		$this->url = ( defined('LTPLE_LAYER_URL') ? LTPLE_LAYER_URL : $this->parent->urls->home . '/t/');

		$this->dir = ( defined('LTPLE_LAYER_DIR') ? LTPLE_LAYER_DIR : ABSPATH . 't/');	

		// get layer key
	
		if(isset($_GET['lk'])){
			
			$this->key = sanitize_text_field($_GET['lk']);
		}

		// get embedded layer
		
		$embedded_url = $this->get_embedded_url();
		
		if( !empty($embedded_url) ){
			
			$this->embedded = $this->get_embedded_layer( $embedded_url );
		}				
		
		// set layer
		
		$this->set_uri();

		if( $this->uri > 0 ){

			//set layer data
			
			$this->set_layer($this->uri);
		}				
	}
	
	public function set_layer($uri){
	
		if( $q = get_post($uri) ){
			
			if( $q->post_status == 'publish' || $q->post_status == 'draft' ){

				if( $q->post_type == 'cb-default-layer' || $q->post_type == 'user-layer' || in_array( $q->post_type, $this->parent->settings->options->postTypes ) ){
				
					$this->id 		= $q->ID;
					$this->type 	= $q->post_type;
					$this->slug 	= $q->post_name;
					$this->title 	= $q->post_title;
		
					if( $this->type == 'user-layer' ){
					
						$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
					}
					elseif( in_array( $q->post_type, $this->parent->settings->options->postTypes ) ){
						
						$this->defaultId = intval(get_post_meta( $q->ID, 'defaultLayerId', true));
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );							
					}
					else{
						
						$this->defaultId = $this->id;
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );
					}
					
					// get layer Content
					
					$this->layerContent = get_post_meta( $this->id, 'layerContent', true );

					if( $this->layerContent == '' && $this->id != $this->defaultId ){
						
						$this->layerContent = get_post_meta( $this->defaultId, 'layerContent', true );
					}
					
					// get layer css

					$this->layerCss = get_post_meta( $this->id, 'layerCss', true );
					
					if( $this->layerCss == '' && $this->id != $this->defaultId ){
						
						$this->layerCss = get_post_meta( $this->defaultId, 'layerCss', true );
					}
					
					// get default css

					$this->defaultCss = get_post_meta( $this->defaultId, 'layerCss', true );

					// get layer js
					
					$this->layerJs = get_post_meta( $this->id, 'layerJs', true );
					
					if( $this->layerJs == '' && $this->id != $this->defaultId ){
						
						$this->layerJs = get_post_meta( $this->defaultId, 'layerJs', true );
					}
					
					// get default js

					$this->defaultJs = get_post_meta( $this->defaultId, 'layerJs', true );
					
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

						$this->layerMargin = '-120px 0px -20px 0px';
					}
					
					// get layer Min Width

					$this->layerMinWidth = get_post_meta( $this->defaultId, 'layerMinWidth', true );
					
					if( empty($this->layerMinWidth) ){
						
						$this->layerMinWidth = '1000px';
					}									
					
					//get page def
					
					$this->pageDef = get_post_meta( $this->defaultId, 'pageDef', true );
					
					// get default layer type
					
					$this->defaultLayerType = $this->get_layer_type($this->defaultId);
								
					//get default static path
					
					$this->defaultStaticPath = $this->get_static_path($this->defaultId,$this->defaultId);
						
					//get default static css path
					
					$this->defaultStaticCssPath = $this->get_static_asset_path($this->id,'css','default_style');
						
					//get default static js path
					
					$this->defaultStaticJsPath = $this->get_static_asset_path($this->id,'js','default_script');

					//get default static url
					
					$this->defaultStaticUrl = $this->get_static_url($this->defaultId,$this->defaultId);
					
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
								
					//get layer output
					
					$this->layerOutput = $this->defaultLayerType->output;
					
					//get layer options
					
					$this->layerOptions = get_post_meta( $this->defaultId, 'layerOptions', true );
					
					//get layer settings
					
					$this->layerSettings = get_post_meta( $this->id, 'layerSettings', true );

					//get layer embedded
					
					$this->layerEmbedded = get_post_meta( $this->id, 'layerEmbedded', true );	
					
					//get layer form
					
					$this->layerForm = get_post_meta( $this->defaultId, 'layerForm', true );
					
					//get css libraries

					$this->layerCssLibraries = wp_get_post_terms( $this->defaultId, 'css-library', array( 'orderby' => 'term_id' ) );

					//get js libraries
					
					$this->layerJsLibraries = wp_get_post_terms( $this->defaultId, 'js-library', array( 'orderby' => 'term_id' ) );								
					
					//get font libraries
					
					$this->layerFontLibraries = wp_get_post_terms( $this->defaultId, 'font-library', array( 'orderby' => 'term_id' ) );																			
					
					//get element libraries
					
					$this->layerHtmlLibraries = wp_get_post_terms( $this->defaultId, 'element-library', array( 'orderby' => 'term_id' ) );								
					
					//get layer image proxy

					$this->layerImgProxy = $this->parent->request->proto . $_SERVER['HTTP_HOST'].'/image-proxy.php?'.time().'&url=';
				}
			}
		}
	}
	
	public function get_default_layer_fields(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get layer types
		
		$layer_types=[];
		
		foreach($this->types as $term){
			
			$layer_types[$term->slug]=$term->name;
		}
		
		//get current layer type
		
		$terms = wp_get_post_terms( $post_id, 'layer-type' );
		
		$default_layer_type='';

		if(isset($terms[0]->slug)){
			
			$default_layer_type=$terms[0]->slug;
		}
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"tagsdiv-layer-type"),
				
				'id'=>"new-tag-layer-type",
				'name'=>'tax_input[layer-type]',
				'label'=>"",
				'type'=>'select',
				'options'=>$layer_types,
				'selected'=>$default_layer_type,
				'description'=>''
		);
		
		//get current layer range
		
		$terms = wp_get_post_terms( $post_id, 'layer-range' );

		$default_layer_range='';

		if(isset($terms[0]->term_id)){
			
			$default_layer_range=$terms[0]->term_id;
		}

		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-rangediv"),
				
				'type'		=> 'dropdown_categories',
				'id'		=> 'layer-range',
				'name'		=> 'tax_input[layer-range][]',
				'label'		=> '',
				'taxonomy'	=> 'layer-range',
				'selected'	=> $default_layer_range,
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"metabox_1"),
				'id'=>"pageDef",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"JSON object",
				'description'=>'
				
					<table class="widefat fixed striped" cellspacing="0">
						<thead>
						
							<tr>
							
								<th>option</th>
								<th>description</th>
								<th>default</th>
								<th>possible values</th>
								
							</tr>
							
						</thead>
						<tbody>
						
							<tr>
							
								<td><strong>name</strong></td>
								<td>ID of the element</td>
								<td>null</td>
								<td>String</td>
								
							</tr>
							<tr>
							
								<td><strong>iconClass</strong></td>
								<td>Class of the icon before the element name</td>
								<td>glyphicon glyphicon-plus</td>
								<td>String</td>
								
							</tr>							
							<tr>
							
								<td><strong>props</strong></td>
								<td>List of editable CSS propertises</td>
								<td>null</td>
								<td>Array</td>
							</tr>
							
							<tr>
								<td><strong>labels</strong></td>
								<td>Labels of the editable CSS propertises</td>
								<td>null</td>
								<td>Array</td>
							</tr>
							
							<tr>
							
								<td><strong>editorsConfig</strong></td>
								<td>Configuration of some editable CSS propertise surch as background-image or image source</td>
								<td>null</td>
								<td>Object{"prop":{"urls":Object}}</td>
								
							</tr>
							
							<tr>
							
								<td><strong>draggable</strong></td>
								<td>Is the element draggable inside the preview</td>
								<td>false</td>
								<td>String</td>
								
							</tr>
							
							<tr>
							
								<td><strong>contenteditable</strong></td>
								<td>Is the element content editable</td>
								<td>true</td>
								<td>String</td>
								
							</tr>
							
						</tbody>
						
					</table>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-content" ),
				'id'			=> "layerContent",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "HTML content",
				//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
		);		
		
		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-css" ),
				'id'			=> "layerCss",
				'label'			=> "",
				'type'			=> 'textarea',
				'stripcslashes'	=> false,
				'placeholder'	=> "Internal CSS style sheet",
				'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-js" ),
				'id'			=> "layerJs",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "Additional Javascript",
				'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-elements" ),
				'id'			=> "layerElements",
				'name'			=> "layerElements",
				'type'			=> 'element',
				'description'	=> '',
				
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-meta" ),
				'id'			=> "layerMeta",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "JSON",
				'description'	=> '<i>Additional Meta Data</i>'
		);

		$fields[]=array(
		
			"metabox" =>
			
				array( 'name' => "layer-static-url" ),
				'id'			=> "layerStaticTpl",
				'type'			=> 'file',
				'label'			=> '<b>Upload Archive</b>',
				'accept'		=> '.zip,.tar',
				'script'		=> 'jQuery(document).ready(function($){$(\'form#post\').attr(\'enctype\',\'multipart/form-data\');});',
				'placeholder'	=> "archive.zip",
				'style'			=> "padding:5px;margin: 15px 0 5px 0;",
				'description'	=> "Upload a static template (zip,tar)",
		);		
		
		$post_id = 0;
		
		if( !empty($_GET['post']) ){
			
			$post_id = intval($_GET['post']);
		}
		elseif( !empty($_POST['post_ID']) ){
			
			$post_id = intval($_POST['post_ID']);
		}
		
		if( $post_id > 0 ){
			
			$fields[]=array(
			
				"metabox" =>
				
					array( 'name' => "layer-static-url" ),
					'id'			=> "layerStaticUrl",
					'label'			=> '<b>Template Static Url</b>',
					'type'			=> 'slug',
					'style'			=> "margin: 15px 0 5px 0;",
					'base'			=> $this->url . '<b>' . $post_id . '</b>/',
					'slash'			=> false,
					'placeholder'	=> "template1/index.html"
			);		
		}

		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-visibility"),
				'id'			=> "layerVisibility",
				'label'			=> "",
				'type'			=> 'radio',
				'options'		=> array(
				
					'subscriber'	=> 'Subscriber',
					'registered'	=> 'Registered',
					'anyone'		=> 'Anyone',
				),
				'inline'		=> false,
				'description'	=> ''
		); 
		
		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-form"),
				'id'			=> "layerForm",
				'label'			=> "",
				'type'			=> 'radio',
				'options'		=> array(
				
					'none'		=> 'None',
					'importer'	=> 'Importer',
					//'scraper'	=> 'Scraper',
				),
				'inline'		=> false,
				'description'	=> ''
		);

		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-options"),
				'id'		=>"layerOptions",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'line-break'	=> 'Line break (Enter)',
					'wrap-text'		=> 'Auto wrap text',
				
				),
				'checked'	=>array('margin-top'),
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-margin"),
				'id'		=>"layerMargin",
				'label'		=>"",
				'type'		=>'margin',
				'placeholder'=>'0px',
				'default'	=>'-120px 0px -20px 0px',
				'description'=>''
		);
		
		$fields[]=array( 
		
			"metabox" =>
			
				array('name'	=> "layer-user"),
				'id'			=> "layerUserId",
				'label'			=> "",
				'type'			=> 'number',
				'default'		=> 0,
				'description'	=> ''
		); 
		
		return $fields;
	}
	
	public function get_user_layer_fields(){
				
		$fields=[];
	
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-content"),
				'id'			=> "layerContent",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "HTML content",
				//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
		);
	
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-css"),
				'id'			=> "layerCss",
				'label'			=> "",
				'type'			=> 'textarea',
				'stripcslashes'	=> false,
				'placeholder'	=> "Internal CSS style sheet",
				'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-js"),
				'id'			=> "layerJs",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "Additional Javascript",
				'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
		);

		$fields[]=array(
			"metabox" =>
			
				array('name'=>"default_layer_id"),
				'id'			=> "defaultLayerId",
				'label'			=> "Default Template ID",
				'type'			=> 'edit_layer',
				'placeholder'	=> "",
				'description'	=> '',
				'disabled'		=> true,
		);

		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-embedded"),
				'id'			=> "layerEmbedded",
				'label'			=> "",
				'type'			=> 'text',
				'placeholder'	=> "http://",
				'disabled'		=> true,
		);			
		
		return $fields;
	}
	
	public function get_user_layers( $user, $context='admin-dashboard' ) {

		echo '<div class="postbox">';
			
			echo '<h3 style="margin:10px;">' . __( 'Saved Projects', 'live-template-editor-client' ) . '</h3>';
		
			echo '<table class="widefat fixed striped" style="border:none;">';
				
				if( $layers = get_posts(array(
				
					'author'      => $user->ID,
					'post_type'   => 'user-layer',
					'post_status' => 'publish',
					'numberposts' => -1
					
				))){

					foreach( $layers as $layer ){

						echo '<tr>';
						
							echo '<td style="width:300px;">';
								
								echo $layer->post_title;
							
							echo '</td>';
							
							echo '<td>';
								
								echo'<a class="btn btn-sm btn-default" href="' . get_edit_post_link( $layer->ID ) . '" target="_blank">Edit backend</a>';
								echo ' | ';
								echo'<a class="btn btn-sm btn-default" href="' . $this->parent->urls->editor . '?uri=' . $layer->ID . '" target="_blank">Edit frontend</a>';
								echo ' | ';
								echo'<a class="btn btn-sm btn-default" href="' . get_post_permalink( $layer->ID ) . '" target="_blank">Preview</a>';
								
							echo '</td>';
						
						echo '</tr>';
					}
				}
				else{
					
					echo '<tr>';
					
						echo '<td style="width:300px;">';
							
							echo 'None';
						
						echo '</td>';
						
						echo '<td>';
							
							echo'';
							
						echo '</td>';
					
					echo '</tr>';					
				}
				
			echo '</table>';
			
		echo '</div>';
	}
		
	public function get_options($taxonomy,$term,$price_currency='$'){
		
		if(is_array($term)){
			
			$term_slug = $term['slug'];
		}
		else{
		
			$term_slug = $term->slug;
		}
	
		if(!$price_amount = get_option('price_amount_' . $term_slug)){
			
			$price_amount = 0;
		} 
		
		if(!$price_period = get_option('price_period_' . $term_slug)){
			
			$price_period = 'month';
		}
		
		if(!$storage_amount = get_option('storage_amount_' . $term_slug)){
			
			$storage_amount = 0;
		}
		
		if(!$storage_unit = get_option('storage_unit_' . $term_slug)){
			
			$storage_unit = 'templates';
		}
		
		if(!$form = get_option('meta_' . $term_slug)){
			
			$form = [];
		}

		$options=[];
		$options['price_currency']	= $price_currency;
		$options['price_amount']	= $price_amount;
		$options['price_period']	= $price_period;
		$options['storage_amount']	= $storage_amount;
		$options['storage_unit']	= $storage_unit;
		$options['form']			= $form;
		
		// add addon options
		
		$this->options  = array();
		
		do_action('ltple_layer_options',$term_slug);
		
		$options = array_merge($options,$this->options);
		
		return $options;
	}
	
	public function show_layer(){
		
		$data = [];
		
		if( !empty($_GET['url']) ){
			
			$url = parse_url(urldecode(urldecode($_GET['url'])));
			
			if(!empty($url['host'])){
			
				$domain = get_page_by_title($url['host'], OBJECT, 'user-domain');
			
				if(!empty($domain)){
					
					$urls = get_post_meta($domain->ID,'domainUrls',true);
					
					foreach($urls as $layerId => $domainPath ){
						
						if( $url['path'] == '/'.$domainPath ){
							
							$post = get_post($layerId);
							
							if( !empty($post) ){
								
								if(!isset($this->defaultId)){
									
									$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
								}

								include($this->parent->views . '/layer.php');
								
								exit;
							}							
						}
					}
				}
			}
		}
		elseif( !empty($_GET['uid']) ){
			
			$layerId = intval($_GET['uid']);
			 
			if( $layerId > 0 ){
				
				$post = get_post($layerId);
				
				if( !empty($post) && $post->post_type == 'user-layer' ){
					
					if( $post->post_status == 'publish' ){
						
						if(!isset($this->defaultId)){
							
							$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
						}						

						include($this->parent->views . '/layer.php');
					}
					elseif( $post->post_status == 'draft' ){
						
						echo 'This page is in draft mode...';
					}
					else{
						
						echo 'This page has been removed...';
					}
					
					exit;
				}
			}
		}
	}
	
	public static function sanitize_url($url){
		
		if( is_ssl() ){
			
			$url = str_replace( 'http://', 'https://', $url);
		}
		else{
			
			$url = str_replace( 'https://', 'http://', $url);
		}
		
		return $url;
	}
	
	public static function sanitize_content($str,$is_hosted=false){
		
		$str = stripslashes($str);
		
		//$str = str_replace(array('&quot;'),array(htmlentities('&quot;')),$str);
		
		$str = str_replace(array('cursor: pointer;','data-element_type="video.default"'),'',$str);
		
		$str = str_replace(array('<body','</body>','src=" ','href=" '),array('<div id="main"','</div>','src="','href="'),$str);
		
		//$str = html_entity_decode(stripslashes($str));
		
		//$str = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $str);
		
		$str = preg_replace( array(
		
				//'/<iframe(.*?)<\/iframe>/is',
				'/<title(.*?)<\/title>/is',
				'/<!doctype(.*?)>/is',
				'/<link(.*?)>/is',
				//'/<body(.*?)>/is',
				//'/<\/body>/is',
				//'/<head(.*?)>/is',
				//'/<\/head>/is',				
				'/<html(.*?)>/is',
				'/<\/html>/is'
			), 
			'', $str
		);		
		
		if( !$is_hosted ){
		
			$str = preg_replace( array(
			
					'/<pre(.*?)<\/pre>/is',
					'/<frame(.*?)<\/frame>/is',
					'/<frameset(.*?)<\/frameset>/is',
					'/<object(.*?)<\/object>/is',
					'/<script(.*?)<\/script>/is',
					'/<style(.*?)<\/style>/is',
					'/<embed(.*?)<\/embed>/is',
					'/<applet(.*?)<\/applet>/is',
					'/<meta(.*?)>/is',
					'/onload="(.*?)"/is',
					'/onunload="(.*?)"/is',
				), 
				'', $str
			);
		}
		
		return $str;
	}
	
	public function add_layer_fields( $taxonomy, $term_slug = '' ){
		
		//collect our saved term field information
		
		$price = $storage = [];

		if( !empty($term_slug) ){
		
			$price['price_amount'] = get_option('price_amount_' . $term_slug); 
			$price['price_period'] = get_option('price_period_' . $term_slug); 

			$storage['storage_amount'] 	= get_option('storage_amount_' . $term_slug);
			$storage['storage_unit'] 	= get_option('storage_unit_' . $term_slug);
		}	
		
		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-price-amount">Price</label>';

			echo $this->parent->plan->get_layer_price_fields($taxonomy,$price);
			
		echo'</div>';
		
		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-storage-amount">Storage</label>';

			echo $this->parent->plan->get_layer_storage_fields($taxonomy,$storage);
			
		echo'</div>';
		
		do_action('ltple_layer_plan_fields', $taxonomy, $term_slug);
	}
	
	public function add_edit_layer_fields($term){
		
		//output our additional fields

		if( $term->taxonomy == 'layer-type' ){
		
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Type </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'output',
						'id'			=> 'output',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $this->get_layer_outputs(),
						'inline'		=> false,
						'default'		=> 'inline-css',
						'description'	=> '',
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';		
		
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Visibility </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'visibility_'.$term->slug,
						'id'			=> 'visibility_'.$term->slug,
						'label'			=> "",
						'type'			=> 'radio',
						'options'		=> array(
							
							'admin'			=> 'Admin',
							'anyone'		=> 'Anyone',
							'none'			=> 'None',
						),
						'inline'		=> false,
						'default'		=> 'anyone',
						'description'	=> ''
						
					), false );
					
				echo'</td>';	
				
			echo'</tr>';
		}
		
		// layer plan attributes
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Plan </label>';
			
			echo'</th>';		
		
			echo'<td>';
				
				$this->add_layer_fields($term->taxonomy,$term->slug);
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Meta </label>';
			
			echo'</th>';
			
				echo'<td>';
					
					$this->parent->admin->display_field(array(
					
						'type'				=> 'form',
						'id'				=> 'meta_'.$term->slug,
						'name'				=> $term->taxonomy . '-meta',
						'array' 			=> [],
						'description'		=> ''
						
					), false );
					
				echo'</td>';	
			
		echo'</tr>';
	}
	
	public function get_css_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'css_url_'.$term->slug,
					'name'				=> 'css_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> '',
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Content </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'				=> 'textarea',
					'id'				=> 'css_content_'.$term->slug,
					'name'				=> 'css_content_'.$term->slug,
					'placeholder'		=> htmlentities('<style></style>'),
					'description'		=> '<i>with ' . htmlentities('<style></style>') . ' or ' . htmlentities('<link></link>') . '</i>'
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	
	public function get_js_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'js_url_'.$term->slug,
					'name'				=> 'js_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Content </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'				=> 'textarea',
					'id'				=> 'js_content_'.$term->slug,
					'name'				=> 'js_content_'.$term->slug,
					'placeholder'		=> htmlentities('<script></script>'),
					'description'		=> '<i>with '.htmlentities('<script></script>').'</i>'
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function get_font_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'font_url_'.$term->slug,
					'name'				=> 'font_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';
	}	
	
	public function set_layer_type_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		$this->columns['output'] 		= 'Type';
		$this->columns['visibility'] 	= 'Visibility';
		//$this->columns['slug'] 		= 'Slug';
		$this->columns['description'] 	= 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
		
		do_action('ltple_layer_type_columns');

		return $this->columns;
	}	
	
	public function set_layer_range_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		//$this->columns['slug'] 		= 'Slug';
		$this->columns['description'] 	= 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
	
		do_action('ltple_layer_range_columns');
	
		return $this->columns;
	}
	
	public function set_account_option_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		//$this->columns['slug'] 		= 'Slug';
		$this->columns['description'] 	= 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
		
		do_action('ltple_layer_option_columns');

		return $this->columns;
	}
		
	public function add_layer_column_content($content, $column_name, $term_id){
		
		$this->column = $content;
		
		if( $term = get_term($term_id) ){
			
			if($column_name === 'output') {
				
				$outputs = $this->get_layer_outputs();
				
				if(!$output = get_term_meta($term->term_id,'output',true)){
					
					$output = 'inline-css';
				}

				$this->column .='<span class="label label-primary">'.$outputs[$output].'</span>';
			}
			elseif($column_name === 'visibility') {
				
				if(!$visibility = get_option('visibility_' . $term->slug)){
					
					$visibility = 'anyone';
				}
				
				if( $visibility == 'admin' ){
					
					$this->column .='<span class="label label-warning">'.$visibility.'</span>';
				}
				elseif( $visibility == 'none' ){
					
					$this->column .='<span class="label label-danger">'.$visibility.'</span>';
				}
				else{
					
					$this->column .='<span class="label label-success">'.$visibility.'</span>';
				}
				
			}
			elseif($column_name === 'price') {
				
				if(!$price_amount = get_option('price_amount_' . $term->slug)){
					
					$price_amount = 0;
				} 
				
				if(!$price_period = get_option('price_period_' . $term->slug)){
					
					$price_period = 'month';
				} 	
				
				$this->column .=$price_amount.'$'.' / '.$price_period;
			}
			elseif($column_name === 'storage') {
				
				if(!$storage_amount = get_option('storage_amount_' . $term->slug)){
					
					$storage_amount = 0;
				}
				
				if(!$storage_unit = get_option('storage_unit_' . $term->slug)){
					
					$storage_unit = 'templates';
				} 
				
				if( $storage_unit == 'templates' ){
					
					if( $storage_amount == 1 ){
						
						$this->column .='+'.$storage_amount.' project';
					}
					elseif($storage_amount > 0){
						
						$this->column .='+'.$storage_amount.' projects';
					}
					else{
						
						$this->column .= $storage_amount.' projects';
					}
				}
				elseif($storage_amount > 0){
					
					$this->column .='+'.$storage_amount.' '.$storage_unit;
				}
				else{
					
					$this->column .= $storage_amount.' '.$storage_unit;
				}
				
			}
			elseif($column_name === 'users') {
				
				$users=0;
				
				$this->column .=$users;
			}
			
			do_action('ltple_layer_column_content',$column_name,$term);
		}

		return $this->column;
	}
	
	public function save_layer_fields($term_id){

		if( $this->parent->user->is_admin ){
			
			//collect all term related data for this new taxonomy
			
			if( $term = get_term($term_id) ){
							
				//save our custom fields as wp-options
				
				if( isset($_POST[$term->taxonomy .'-price-amount']) && is_numeric($_POST[$term->taxonomy .'-price-amount']) ){

					update_option('price_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));			
				}
				
				if( isset($_POST[$term->taxonomy .'-price-period']) ){

					$periods = $this->parent->plan->get_price_periods();
					$period = sanitize_text_field($_POST[$term->taxonomy . '-price-period']);
					
					if(isset($periods[$period])){
						
						update_option('price_period_' . $term->slug, $period);	
					}
				}
				
				if(isset($_POST[$term->taxonomy .'-storage-amount'])&&is_numeric($_POST[$term->taxonomy .'-storage-amount'])){

					update_option('storage_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-storage-amount'])),0));			
				}
				
				if(isset($_POST[$term->taxonomy .'-storage-unit'])){

					$storage_units = $this->parent->plan->get_storage_units();
					$storage_unit = sanitize_text_field($_POST[$term->taxonomy . '-storage-unit']);
					
					if(isset($periods[$period])){			
					
						update_option('storage_unit_' . $term->slug, $storage_unit);			
					}
				}

				if(isset($_POST['output'])){

					update_term_meta( $term->term_id, 'output', $_POST['output']);			
				}			
				
				if(isset($_POST['visibility_'.$term->slug])){

					update_option('visibility_'.$term->slug, $_POST['visibility_'.$term->slug]);			
				}
				
				if(isset($_POST[$term->taxonomy . '-meta'])){

					update_option('meta_'.$term->slug, $_POST[$term->taxonomy . '-meta']);			
				}
				
				do_action('ltple_save_layer_fields',$term);
			}
		}
	}
	
	public function save_library_fields($term_id){

		if( $this->parent->user->is_editor ){
			
			//collect all term related data for this new taxonomy
			$term = get_term($term_id);

			//save our custom fields as wp-options

			if(isset($_POST['css_url_'.$term->slug])){

				update_option('css_url_'.$term->slug, $_POST['css_url_'.$term->slug]);			
			}
			
			if(isset($_POST['css_content_'.$term->slug])){

				update_option('css_content_'.$term->slug, $_POST['css_content_'.$term->slug]);			
			}
			
			if(isset($_POST['js_url_'.$term->slug])){

				update_option('js_url_'.$term->slug, $_POST['js_url_'.$term->slug]);			
			}
			
			if(isset($_POST['js_content_'.$term->slug])){

				update_option('js_content_'.$term->slug, $_POST['js_content_'.$term->slug]);			
			}
			
			if(isset($_POST['font_url_'.$term->slug])){

				update_option('font_url_'.$term->slug, $_POST['font_url_'.$term->slug]);			
			}
		}
	}
	
	public function get_static_url($postId,$defaultId){
		
		$layerStaticUrl = get_post_meta( $defaultId, 'layerStaticUrl', true );
		
		if( empty($layerStaticUrl) ){
			
			$layerStaticUrl = 'index.html';
		}
		
		$static_url = $this->url . $postId . '/' . $layerStaticUrl;				
	
		return $static_url;
	}
	
	public function get_static_asset_url($postId, $type = 'css', $filename = 'style'){
		
		$static_url = $this->url . $postId . '/assets/'.$type.'/' . $filename . '.' . $type;
		
		return $static_url;
	}
	
	public function get_static_dir($postId,$empty=false){
		
		$static_dir = $this->dir . $postId;
		
		if( !is_dir($static_dir) ){
			
			mkdir($static_dir,0755,true);
		}
		elseif( $empty === true ){
			
			$this->delete_static_contents( $postId );
			
			mkdir($static_dir,0755,true);
		}
	
		return $static_dir;
	}
	
	public function get_static_asset_dir($postId, $type = 'css'){
		
		$static_dir = $this->dir . $postId . '/assets/' . $type;
		
		if( !is_dir($static_dir) ){
			
			mkdir($static_dir,0755,true);
		}		
		
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
	
	public function upload_static_contents($post_id){
		
		if( is_admin() && !empty($_POST['layerStaticTpl_nonce']) ){
			
			//security verification
			
			if( !wp_verify_nonce($_POST['layerStaticTpl_nonce'], $this->parent->file) ) {
			  
				return $post_id;
			}

			if( !current_user_can('edit_page', $post_id) ){
				
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
		
		$dir = $this->dir . $post_id . '/';
		
		if ( is_dir( $dir ) ){
			
			$it = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
			
			$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
			
			foreach ( $files as $file ) {
				
				if ( $file->isDir() ) {
					
					rmdir( $file->getRealPath() );
				}
				else {
					
					unlink( $file->getRealPath() );
				}
			}
			rmdir( $dir );
		}

		return true;
	}
	
	public function copy_dir($src,$dst) { 
	
		if( !is_dir($dst) ){
	
			$mkdir = mkdir($dst,0755,true);
		}
		
		$dir = opendir($src);
		
		while(false !== ( $file = readdir($dir)) ) { 
		
			if (( $file != '.' ) && ( $file != '..' )) { 
			
				if ( is_dir($src . '/' . $file) ) { 
				
					$this->copy_dir($src . '/' . $file,$dst . '/' . $file); 
				} 
				else{
					
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		
		closedir($dir); 
		
		return true;
	} 
	
	public function copy_static_contents($defaultLayerId,$post_id){
		
		$src = $this->get_static_dir($defaultLayerId);
		$dst = $this->get_static_dir($post_id,true);

		return $this->copy_dir($src,$dst);
	}
	
	public function output_static_layer( $args = array() ){
		
		if( isset($args[0]) ){
		
			$output = $args[0];

			if( !empty($output) ){
				
				if( isset($_GET['filetree']) && ( $this->layerOutput == 'hosted-page' || $this->layerOutput == 'downloadable' ) ){
					
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
						echo'.container { margin:150px auto; max-width:640px;}';
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
				elseif(( $this->type == 'user-layer' || $this->type == 'cb-default-layer' ) && $this->layerOutput == 'downloadable' ){
				 
					// sanitize content
					
					$output = str_replace(array('<?php'),'',$output);
					
					// remove absolute image path
					
					$output = str_replace($this->parent->image->url,'assets/images/',$output);
					
					// store static output
					
					//if( $this->type == 'user-layer' || !file_exists($this->layerStaticPath) ){ 
					
						file_put_contents($this->layerStaticPath,$output);
					//}
					
					// store static css
					
					if( !empty( $this->defaultCss ) ){
					
						file_put_contents($this->defaultStaticCssPath,$this->defaultCss);
					}
					
					if( $this->type == 'user-layer' && $this->layerCss != $this->defaultCss ){
						
						file_put_contents($this->layerStaticCssPath,$this->layerCss);
					}					
					
					// store static js
					
					if( !empty( $this->defaultJs) ){
					
						file_put_contents($this->defaultStaticJsPath,$this->defaultJs);
					}
					
					if( $this->type == 'user-layer' && $this->layerJs != $this->defaultJs ){
						
						file_put_contents($this->layerStaticJsPath,$this->layerJs);
					}
					
					// output content
					
					if( isset($_GET['preview']) ){
						
						echo '<!DOCTYPE html>';
						
						echo '<head>';
						
							echo '<title>';
							
								echo 'Preview - ' . $this->title;
								
							echo '</title>';
						
						echo '</head>';
						
						echo '<body>';
						
							echo '<iframe src="' . $this->layerStaticUrl . '" style="position:fixed;top:0px;left:0px;bottom:0px;right:0px;width:100%;height:100%;border:none;margin:0;padding:0;overflow:hidden;z-index:999999;" />';
							
						echo '</body>';
					}
					else{
						
						wp_redirect($this->layerStaticUrl);exit;
						
						// add base
						
						$content  = '<head>' . PHP_EOL;
						$content .= '<base href="' . dirname($this->layerStaticUrl) . '/">';
						
						$output = str_replace('<head>',$content,$output);
						
						echo $output;
					}
				}
				else{
					
					echo $output;
				}
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

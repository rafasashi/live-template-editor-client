<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer extends LTPLE_Client_Object {
	
	public $parent;
	public $id			= -1;
	public $defaultId	= -1;
	public $uri			= '';
	public $key			= ''; // gives the server proxy access to the layer
	public $slug		= '';
	public $title		= '';
	public $type		= '';
	public $form		= '';
	public $outputMode	= '';
	public $embedded	= '';
	public $types		= '';
	public $ranges		= '';
	public $options		= '';
	
	/**
	 * Constructor function
	 */
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		
		$this->parent->register_post_type( 'cb-default-layer', __( 'Default Designs', 'live-template-editor-client' ), __( 'Default Design', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'cb-default-layer',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> array('slug'=>'default-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_post_type( 'user-layer', __( 'User Designs', 'live-template-editor-client' ), __( 'User Design', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-layer',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'user-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_taxonomy( 'layer-type', __( 'Layer Type', 'live-template-editor-client' ), __( 'Layer Type', 'live-template-editor-client' ),  array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'layer-range', __( 'Layer Range', 'live-template-editor-client' ), __( 'Layer Range', 'live-template-editor-client' ), array('user-plan','cb-default-layer'), array(
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
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'account-option', __( 'Account Options', 'live-template-editor-client' ), __( 'Account Option', 'live-template-editor-client' ),  array('user-plan'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> true,
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
			'show_in_nav_menus' 	=> true,
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
			'show_in_nav_menus' 	=> true,
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
			
				$this->parent->admin->add_meta_box (
					
					'metabox_1',
					__( 'Layer configuration', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-css',
					__( 'Layer CSS', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-js',
					__( 'Layer Javascript', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				); 
				
				$this->parent->admin->add_meta_box (
					
					'layer-meta',
					__( 'Layer Meta Data', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-static-url',
					__( 'Layer Static Url', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-output',
					__( 'Layer Output', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-visibility',
					__( 'Layer Visibility', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-form',
					__( 'Layer Form', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);		

				$this->parent->admin->add_meta_box (
					
					'layer-options',
					__( 'Layer Options', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-margin',
					__( 'Layer Margin', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
			
				$this->parent->admin->add_meta_box (
				
					'tagsdiv-layer-type',
					__( 'Layer Type', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
				
				$this->parent->admin->add_meta_box ( 
				
					'layer-rangediv',
					__( 'Layer Range', 'live-template-editor-client' ), 
					array($post->post_type),
					'side'
				);
			}
			elseif( $post->post_type == 'user-layer' || ( !empty($this->parent->settings->options->postTypes) && in_array( $post->post_type, $this->parent->settings->options->postTypes ) ) ){

				$this->parent->admin->add_meta_box (
				
					'default_layer_id',
					__( 'Default Layer', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);				
				
				if( in_array( $post->post_type, $this->parent->settings->options->postTypes ) ){
				
					// get default layer id
					
					$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true));
					
					if( $post->layer_id == 0 ){
						
						return;
					}
				}
				
				$this->parent->admin->add_meta_box (
					
					'layer-css',
					__( 'Layer CSS', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				$this->parent->admin->add_meta_box (
					
					'layer-js',
					__( 'Layer Javascript', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
			}
		});		

		add_action('account-option_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('account-option_edit_form_fields', array( $this, 'get_layer_type_fields' ) );	
	
		add_filter('manage_edit-account-option_columns', array( $this, 'set_account_columns' ) );
		add_filter('manage_account-option_custom_column', array( $this, 'add_account_column_content' ),10,3);			
	
		add_action('create_account-option', array( $this, 'save_layer_fields' ) );
		add_action('edit_account-option', array( $this, 'save_layer_fields' ) );	

		add_action('layer-type_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-type_edit_form_fields', array( $this, 'get_layer_type_fields' ) );
	
		add_filter('manage_edit-layer-type_columns', array( $this, 'set_layer_type_columns' ) );
		add_filter('manage_layer-type_custom_column', array( $this, 'add_layer_column_content' ),10,3);		
		
		add_action('create_layer-type', array( $this, 'save_layer_fields' ) );
		add_action('edit_layer-type', array( $this, 'save_layer_fields' ) );	

		add_action('layer-range_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-range_edit_form_fields', array( $this, 'get_layer_type_fields' ) );
	
		add_filter('manage_edit-layer-range_columns', array( $this, 'set_layer_range_columns' ) );
		add_filter('manage_layer-range_custom_column', array( $this, 'add_layer_column_content' ),10,3);
	
		add_action('create_layer-range', array( $this, 'save_layer_fields' ) );
		add_action('edit_layer-range', array( $this, 'save_layer_fields' ) );			

		//add_action('css-library_add_form_fields', array( $this, 'get_new_library_fields' ) );
		add_action('css-library_edit_form_fields', array( $this, 'get_css_library_fields' ) );	
		
		add_action('create_css-library', array( $this, 'save_library_fields' ) );
		add_action('edit_css-library', array( $this, 'save_library_fields' ) );	

		//add_action('js-library_add_form_fields', array( $this, 'get_new_library_fields' ) );
		add_action('js-library_edit_form_fields', array( $this, 'get_js_library_fields' ) );	
				
		add_action('create_js-library', array( $this, 'save_library_fields' ) );
		add_action('edit_js-library', array( $this, 'save_library_fields' ) );	

		add_filter('init', array( $this, 'init_layer' ));
		
		add_action('wp_loaded', array($this,'get_layer_types'));
		add_action('wp_loaded', array($this,'get_layer_ranges'));
		add_action('wp_loaded', array($this,'get_account_options'));
		add_action('wp_loaded', array($this,'get_js_libraries'));
		add_action('wp_loaded', array($this,'get_css_libraries'));
		//add_action('wp_loaded', array($this,'get_default_layers'));
	}
	
	public function get_layer_types(){

		$this->types = $this->get_terms( 'layer-type', array(
				
			'emails'  			=> 'Emails',
			'memes'  			=> 'Memes',
			'pricing-tables'	=> 'Pricing Tables',
			'hosted'  			=> 'Hosted',
			'sandbox' => array(
			
				'name' 		=> 'Sandbox',
				'options' 	=> array(
				
					'visibility'	=> 'admin',
				),
			),			
			'tailored' => array(
			
				'name' 		=> 'Tailored',
				'options' 	=> array(
				
					'visibility'	=> 'admin',
				),
			),
		));		
	}
	
	public function get_layer_ranges(){

		$this->ranges = $this->get_terms( 'layer-range', array(
				
			'demo'  => 'Demo',
			'single-page' => array(
			
				'name' 		=> 'Single Page',
				'options' 	=> array(
				
					'price_amount'	=> 10,
					'price_period' 	=> 'month',
					'storage_amount'=> 1,
					'storage_unit' 	=> 'templates',
				),
			),
			'multi-page' => array(
			
				'name' 		=> 'Multi-Page',
				'options' 	=> array(
				
					'price_amount'	=> 15,
					'price_period' 	=> 'month',
					'storage_amount'=> 1,
					'storage_unit' 	=> 'templates',
				),
			),
			'tailored-single-page' => array(
			
				'name' 		=> 'Tailored Single Page',
				'options' 	=> array(
				
					'price_amount'	=> 10,
					'price_period' 	=> 'month',
					'storage_amount'=> 1,
					'storage_unit' 	=> 'templates',
				),
			),
			'tailored-multi-page' => array(
			
				'name' 		=> 'Tailored Multi-Page',
				'options' 	=> array(
				
					'price_amount'	=> 15,
					'price_period' 	=> 'month',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
		));
	}
	
	public function get_account_options(){

		$this->options = $this->get_terms( 'account-option', array(

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
	
	public function init_layer(){

		if( is_admin() ) {
			
			add_filter('cb-default-layer_custom_fields', array( $this, 'get_default_layer_fields' ));
			
			add_filter('user-layer_custom_fields', array( $this, 'get_user_layer_fields' ));

			if( !empty($this->parent->settings->options->postTypes) ){
			
				foreach( $this->parent->settings->options->postTypes as $post_type ){
					
					add_filter( $post_type . '_custom_fields', array( $this, 'get_user_layer_fields' ));
				}
			}
		}
		else{
				
			if(isset($_GET['lk'])){
				
				$this->key = sanitize_text_field($_GET['lk']);
			}

			// get embedded layer
			
			$embedded_url = '';
			
			if(isset($_GET['le'])){
				
				$embedded_url = sanitize_text_field($_GET['le']);
			}
			elseif(isset($_POST['postEmbedded'])){
				
				$embedded_url = sanitize_text_field($_POST['postEmbedded']);
			}
			
			if( !empty($embedded_url) ){
				
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
				
				// set embedded info
				
				$this->embedded = $embedded;
			}				
			
			if(isset($_GET['uri'])){
				
				$this->uri = intval($_GET['uri']);

				if( $this->uri > 0 ){

					if( $q = get_post($this->uri) ){
						
						if( $q->post_status == 'trash' && $q->post_type == 'user-layer' ){
							
							// get default template instead
							
							$this->defaultId = intval(get_post_meta( $q->ID, 'defaultLayerId', true ));
							
							if( $this->defaultId > 0 ){
								
								$q = get_post($this->defaultId);
							}
						}
						
						if( $q->post_type == 'cb-default-layer' || $q->post_type == 'user-layer' || in_array( $q->post_type, $this->parent->settings->options->postTypes ) ){
						
							$this->id 		= $q->ID;
							$this->type 	= $q->post_type;
							$this->slug 	= $q->post_name;
							$this->title 	= $q->post_title;
							
							if( $this->type == 'user-layer' ){
							
								$this->content 	 = $q->post_content;
								$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
							}
							else{
								
								$this->defaultId = $this->id;
								$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );
							}

							// get output mode
							
							$this->outputMode 	= get_post_meta( $this->defaultId, 'layerOutput', true );
							
							// recalled in layer template...
							//$this->margin 		= get_post_meta( $this->defaultId, 'layerMargin', true );
							//$this->options 		= get_post_meta( $this->defaultId, 'layerOptions', true );
						}
					}
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
			
				array('name'=>"layer-css"),
				'id'=>"layerCss",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Internal CSS style sheet",
				'description'=>'<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-js"),
				'id'=>"layerJs",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Additional Javascript",
				'description'=>'<i>without '.htmlentities('<script></script>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-meta"),
				'id'=>"layerMeta",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"JSON",
				'description'=>'<i>Additional Meta Data</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-static-url"),
				'id'=>"layerStaticUrl",
				'label'=>"",
				'type'=>'text',
				'placeholder'=>"http://"
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-output"),
				'id'		=>"layerOutput",
				'label'		=>"",
				'type'		=>'select',
				'options'	=> array(
				
					'inline-css'	=>'Inline Style',
					'external-css'	=>'Style Sheet',
					'hosted-page'	=>'Hosted Page',
					//'self-hosted'	=>'Self Hosted',
					'canvas'		=>'Canvas'
				),
				'selected'	=>'inline-css',
				'description'=>''
		);
		
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
		
		return $fields;
	}
	
	public function get_user_layer_fields(){
				
		$fields=[];
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-css"),
				'id'=>"layerCss",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Internal CSS style sheet",
				'description'=>'<i>without '.htmlentities('<style></style>').'</i>'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-js"),
				'id'=>"layerJs",
				'label'=>"",
				'type'=>'textarea',
				'placeholder'=>"Additional Javascript",
				'description'=>'<i>without '.htmlentities('<script></script>').'</i>'
		);

		$fields[]=array(
			"metabox" =>
			
				array('name'=>"default_layer_id"),
				'id'=>"defaultLayerId",
				'label'=>"Default Layer ID",
				'type'=>'edit_layer',
				'placeholder'=>"",
				'description'=>''
		);		
		
		return $fields;
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
								
								if(!isset($post->layer_id)){
									
									$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
								}

								include($this->parent->views . $this->parent->_dev .'/layer.php');
								
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
						
						if(!isset($post->layer_id)){
							
							$post->layer_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
						}						

						include($this->parent->views . $this->parent->_dev .'/layer.php');
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
	
	public static function sanitize_content($str){
		
		$str = stripslashes($str);
		
		//$str = str_replace(array('&quot;'),array(htmlentities('&quot;')),$str);
		
		$str = str_replace(array('cursor: pointer;','data-element_type="video.default"'),'',$str);
		
		$str = str_replace(array('<body','</body>','src=" ','href=" '),array('<div id="main"','</div>','src="','href="'),$str);
		
		//$str = html_entity_decode(stripslashes($str));
		
		//$str = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $str);
		
		$str = preg_replace( array(
		
				//'/<iframe(.*?)<\/iframe>/is',
				'/<title(.*?)<\/title>/is',
				'/<pre(.*?)<\/pre>/is',
				'/<frame(.*?)<\/frame>/is',
				'/<frameset(.*?)<\/frameset>/is',
				'/<object(.*?)<\/object>/is',
				'/<script(.*?)<\/script>/is',
				'/<style(.*?)<\/style>/is',
				'/<embed(.*?)<\/embed>/is',
				'/<applet(.*?)<\/applet>/is',
				'/<meta(.*?)>/is',
				'/<!doctype(.*?)>/is',
				'/<link(.*?)>/is',
				//'/<body(.*?)>/is',
				//'/<\/body>/is',
				//'/<head(.*?)>/is',
				//'/<\/head>/is',
				'/onload="(.*?)"/is',
				'/onunload="(.*?)"/is',
				'/<html(.*?)>/is',
				'/<\/html>/is'
			), 
			'', $str
		);
		
		return $str;
	}
	
	public function add_layer_fields($taxonomy_name){
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-price-amount">Price</label>';

			echo $this->parent->plan->get_layer_price_fields($taxonomy_name,[]);
			
		echo'</div>';
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-storage-amount">Storage</label>';

			echo $this->parent->plan->get_layer_storage_fields($taxonomy_name,0);
			
		echo'</div>';
	}
	
	public function get_layer_type_fields($term){

		//collect our saved term field information
		
		$price=[];
		$price['price_amount'] = get_option('price_amount_' . $term->slug); 
		$price['price_period'] = get_option('price_period_' . $term->slug); 
		
		$storage=[];
		$storage['storage_amount'] 	= get_option('storage_amount_' . $term->slug);
		$storage['storage_unit'] 	= get_option('storage_unit_' . $term->slug);
		
		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Price </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo $this->parent->plan->get_layer_price_fields($term->taxonomy,$price);
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Storage </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo $this->parent->plan->get_layer_storage_fields($term->taxonomy,$storage);
						
			echo'</td>';
			
		echo'</tr>';
		
		if( $term->taxonomy == 'layer-type' ){
		
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
	
	public function set_layer_type_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['name'] 		= 'Name';
		$columns['visibility'] 	= 'Visibility';
		//$columns['slug'] 		= 'Slug';
		$columns['description'] = 'Description';
		$columns['price'] 		= 'Price';
		$columns['storage'] 	= 'Storage';
		//$columns['posts'] 	= 'Layers';
		//$columns['users'] 	= 'Users';

		return $columns;
	}	
	
	public function set_layer_range_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['name'] 		= 'Name';
		//$columns['slug'] 		= 'Slug';
		$columns['description'] = 'Description';
		$columns['price'] 		= 'Price';
		$columns['storage'] 	= 'Storage';
		//$columns['posts'] 	= 'Layers';
		//$columns['users'] 	= 'Users';

		return $columns;
	}
	
	public function set_account_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['name'] 		= 'Name';
		//$columns['slug'] 		= 'Slug';
		$columns['description'] = 'Description';
		$columns['price'] 		= 'Price';
		$columns['storage'] 	= 'Storage';
		//$columns['posts'] 	= 'Layers';
		//$columns['users'] 	= 'Users';

		return $columns;
	}
		
	public function add_layer_column_content($content, $column_name, $term_id){
	
		$term= get_term($term_id);
		
		if($column_name === 'visibility') {
			
			if(!$visibility = get_option('visibility_' . $term->slug)){
				
				$visibility = 'anyone';
			}
			
			if( $visibility == 'admin' ){
				
				$content.='<span class="label label-warning">'.$visibility.'</span>';
			}
			elseif( $visibility == 'none' ){
				
				$content.='<span class="label label-danger">'.$visibility.'</span>';
			}
			else{
				
				$content.='<span class="label label-success">'.$visibility.'</span>';
			}
			
		}
		elseif($column_name === 'price') {
			
			if(!$price_amount = get_option('price_amount_' . $term->slug)){
				
				$price_amount = 0;
			} 
			
			if(!$price_period = get_option('price_period_' . $term->slug)){
				
				$price_period = 'month';
			} 	
			
			$content.=$price_amount.'$'.' / '.$price_period;
		}
		elseif($column_name === 'storage') {
			
			if(!$storage_amount = get_option('storage_amount_' . $term->slug)){
				
				$storage_amount = 0;
			}
			
			if(!$storage_unit = get_option('storage_unit_' . $term->slug)){
				
				$storage_unit = 'templates';
			} 
			
			if($storage_unit=='templates'&&$storage_amount==1){
				
				$content.='+'.$storage_amount.' template';
			}
			elseif($storage_amount > 0){
				
				$content.='+'.$storage_amount.' '.$storage_unit;
			}
			else{
				
				$content.=$storage_amount.' '.$storage_unit;
			}
			
		}
		elseif($column_name === 'users') {
			
			$users=0;
			
			$content.=$users;
		}

		return $content;
	}
	
	public function add_account_column_content($content, $column_name, $term_id){
		
		return $this->add_layer_column_content($content, $column_name, $term_id);
	}
	
	public function save_layer_fields($term_id){

		if($this->parent->user->is_admin){
			
			//collect all term related data for this new taxonomy
			$term = get_term($term_id);
						
			//save our custom fields as wp-options
			
			if(isset($_POST[$term->taxonomy .'-price-amount'])&&is_numeric($_POST[$term->taxonomy .'-price-amount'])){

				update_option('price_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));			
			}
			
			if(isset($_POST[$term->taxonomy .'-price-period'])){

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

			if(isset($_POST['visibility_'.$term->slug])){

				update_option('visibility_'.$term->slug, $_POST['visibility_'.$term->slug]);			
			}
			
			if(isset($_POST[$term->taxonomy . '-meta'])){

				update_option('meta_'.$term->slug, $_POST[$term->taxonomy . '-meta']);			
			}
		}
	}
	
	public function save_library_fields($term_id){

		if($this->parent->user->is_admin){
			
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
		}
	}
}

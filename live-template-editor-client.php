<?php
/*
 * Plugin Name: Live Template Editor Client
 * Version: 1.0.4
 * Plugin URI: https://github.com/rafasashi
 * Description: Live Template Editor allows you to edit and save HTML5 and CSS3 templates.
 * Author: Rafasashi
 * Author URI: https://github.com/rafasashi
 * Requires at least: 4.6
 * Tested up to: 4.6
 *
 * Text Domain: ltple-client
 * Domain Path: /lang/
 *
 * GitHub Plugin URI: rafasashi/live-template-editor-client
 * GitHub Branch:     master
 *
 * @package WordPress
 * @author Rafasashi
 * @since 1.0.0
 */
 
	/**
	* Add documentation link
	*
	*/
	
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	if(!function_exists('is_dev_env')){
		
		function is_dev_env( $dev_ip = '176.132.10.223' ){
			
			if( $_SERVER['REMOTE_ADDR'] == $dev_ip || ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $dev_ip ) ){
				
				return true;
			}

			return false;		
		}			
	}	
	
	if(!function_exists('ltple_row_meta')){
	
		function ltple_row_meta( $links, $file ){
			
			if ( strpos( $file, basename( __FILE__ ) ) !== false ) {
				
				$new_links = array( '<a href="https://github.com/rafasashi" target="_blank">' . __( 'Documentation', 'cleanlogin' ) . '</a>' );
				$links = array_merge( $links, $new_links );
			}
			return $links;
		}
	}
	
	add_filter('plugin_row_meta', 'ltple_row_meta', 10, 2);
	
	$mode = ( is_dev_env() ? '-dev' : '');
	
	if( $mode == '-dev' ){
		
		ini_set('display_errors', 1);
	}	
	
	// Load plugin class files
	require_once( 'includes'.$mode.'/class-ltple-client.php' );
	require_once( 'includes'.$mode.'/class-ltple-client-settings.php' );
		
	// Autoload plugin libraries
	
	$lib = glob( __DIR__ . '/includes'.$mode.'/lib/class-ltple-client-*.php');
	
	foreach($lib as $file){
		
		require_once( $file );
	}
	
	/**
	 * Returns the main instance of LTPLE_Client to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object LTPLE_Client
	 */
	function LTPLE_Client ( $version = '1.0.0' ) {
		
		$instance = LTPLE_Client::instance( __FILE__, $version );
		
		if ( is_null( $instance->_dev ) ) {
			
			$instance->_dev = ( is_dev_env() ? '-dev' : '');
		}				
		
		if ( is_null( $instance->settings ) ) {
			
			$instance->settings = LTPLE_Client_Settings::instance( $instance );
		}

		return $instance;
	}
	
	// Add custom post type cb-default-layer
	
	$version = '1.0.1';
	
	LTPLE_Client($version)->register_post_type( 'cb-default-layer', __( 'Default Layers', 'live-template-editor-client' ), __( 'Default Layer', 'live-template-editor-client' ), '', array(

		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
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

	LTPLE_Client()->register_post_type( 'user-layer', __( 'User Layers', 'live-template-editor-client' ), __( 'User Layer', 'live-template-editor-client' ), '', array(

		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> array('slug'=>'user-layer'),
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> true,
		'show_in_rest' 			=> true,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array( 'title', 'editor', 'author' ),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));	

	LTPLE_Client()->register_post_type( 'subscription-plan', __( 'Subscription Plans', 'live-template-editor-client' ), __( 'Subscription Plan', 'live-template-editor-client' ), '', array(

		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu'		 	=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> array('slug'=>'plan'),
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> true,
		'show_in_rest' 			=> true,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
		'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	LTPLE_Client()->register_post_type( 'default-image', __( 'Default images', 'live-template-editor-client' ), __( 'Default image', 'live-template-editor-client' ), '', array(

		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> false,
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> false,
		//'supports'			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array('title', 'editor'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	LTPLE_Client()->register_post_type( 'user-image', __( 'User images', 'live-template-editor-client' ), __( 'User image', 'live-template-editor-client' ), '', array(

		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> false,
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> false,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array('title', 'editor', 'author'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	LTPLE_Client()->register_post_type( 'email-model', __( 'Email models', 'live-template-editor-client' ), __( 'Email model', 'live-template-editor-client' ), '', array(

		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> array('slug'=>'email-model'),
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> false,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array('title', 'editor'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));	
	
	LTPLE_Client()->register_post_type( 'email-campaign', __( 'Email Campaigns', 'live-template-editor-client' ), __( 'Email Campaign', 'live-template-editor-client' ), '', array(

		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export' 			=> true,
		'rewrite' 				=> false,
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> false,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array('title'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	
	LTPLE_Client($version)->register_post_type( 'nominee', __( 'Nominees', 'live-template-editor-client' ), __( 'Nominees', 'live-template-editor-client' ), '', array(

		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export'			=> true,
		'rewrite' 				=> array('slug'=>'nominee'),
		'capability_type' 		=> 'post',
		'has_archive' 			=> true,
		'hierarchical' 			=> true,
		'show_in_rest' 			=> true,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	LTPLE_Client($version)->register_post_type( 'user-plan', __( 'User Plans', 'live-template-editor-client' ), __( 'User Plans', 'live-template-editor-client' ), '', array(

		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export'			=> true,
		'rewrite' 				=> false,
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> true,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array( 'title'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	LTPLE_Client($version)->register_post_type( 'user-app', __( 'User Apps', 'live-template-editor-client' ), __( 'User Apps', 'live-template-editor-client' ), '', array(

		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'exclude_from_search' 	=> true,
		'show_ui' 				=> true,
		'show_in_menu' 			=> false,
		'show_in_nav_menus' 	=> true,
		'query_var' 			=> true,
		'can_export'			=> true,
		'rewrite' 				=> false,
		'capability_type' 		=> 'post',
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'show_in_rest' 			=> true,
		//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
		'supports' 				=> array( 'title', 'author'),
		'menu_position' 		=> 5,
		'menu_icon' 			=> 'dashicons-admin-post',
	));
	
	// Add custom taxonomy
	
	LTPLE_Client()->register_taxonomy( 'layer-type', __( 'Layer Type', 'live-template-editor-client' ), __( 'Layer Type', 'live-template-editor-client' ),  array('user-plan','cb-default-layer'), array(
		'hierarchical' 			=> false,
		'public' 				=> true,
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
	
	LTPLE_Client()->register_taxonomy( 'layer-range', __( 'Layer Range', 'live-template-editor-client' ), __( 'Layer Range', 'live-template-editor-client' ), array('user-plan','cb-default-layer','default-image'), array(
		'hierarchical' 			=> true,
		'public' 				=> true,
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
	
	LTPLE_Client()->register_taxonomy( 'account-option', __( 'Account Options', 'live-template-editor-client' ), __( 'Account Option', 'live-template-editor-client' ),  array('user-plan'), array(
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
	
	LTPLE_Client()->register_taxonomy( 'image-type', __( 'Image Type', 'live-template-editor-client' ), __( 'Image Type', 'live-template-editor-client' ),  array('default-image'), array(
		'hierarchical' 			=> false,
		'public' 				=> true,
		'show_ui' 				=> true,
		'show_in_nav_menus' 	=> true,
		'show_tagcloud' 		=> true,
		'meta_box_cb' 			=> null,
		'show_admin_column' 	=> true,
		'update_count_callback' => '',
		'show_in_rest'          => true,
		'rewrite' 				=> true,
		'sort'					=> '',
	));
	
	LTPLE_Client()->register_taxonomy( 'campaign-trigger', __( 'Campaign Trigger', 'live-template-editor-client' ), __( 'Campaign Trigger', 'live-template-editor-client' ),  array('email-campaign'), array(
		'hierarchical' 			=> false,
		'public' 				=> false,
		'show_ui' 				=> true,
		'show_in_nav_menus' 	=> true,
		'show_tagcloud' 		=> false,
		'meta_box_cb' 			=> null,
		'show_admin_column' 	=> true,
		'update_count_callback' => '',
		'show_in_rest'          => true,
		'rewrite' 				=> false,
		'sort' 					=> '',
	));
	
	LTPLE_Client()->register_taxonomy( 'marketing-channel', __( 'Marketing Channel', 'live-template-editor-client' ), __( 'Marketing Channel', 'live-template-editor-client' ),  array('user'), array(
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
		'sort'					=> '',
	));
	
	LTPLE_Client()->register_taxonomy( 'app-type', __( 'App Type', 'live-template-editor-client' ), __( 'App Type', 'live-template-editor-client' ),  array('user-image','user-app'), array(
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
		'sort'					=> '',
	));

	// Remove custom taxonomy default metaboxes
	/*
	add_action( 'admin_menu', function(){
		
		remove_meta_box('tagsdiv-layer-type', 'cb-default-layer', 'normal');
		remove_meta_box('layer-rangediv', 'cb-default-layer', 'normal');
	});
	*/

	// Add a metabox to custom post types
	
	add_action( 'add_meta_boxes', function(){

		LTPLE_Client()->admin->add_meta_box (
			
			'metabox_1',
			__( 'Layer configuration', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'layer-css',
			__( 'Layer CSS', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'layer-js',
			__( 'Layer Javascript', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'layer-output',
			__( 'Layer Output', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
		
		/*
		LTPLE_Client()->admin->add_meta_box (
			
			'layer-options',
			__( 'Layer Options', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
		*/
		
		LTPLE_Client()->admin->add_meta_box (
			
			'css-libraries',
			__( 'CSS Libraries', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'js-libraries',
			__( 'Javascript Libraries', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'layer-margin',
			__( 'Layer Margin', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
	
		LTPLE_Client()->admin->add_meta_box (
		
			'tagsdiv-layer-type',
			__( 'Layer Type', 'live-template-editor-client' ), 
			array("cb-default-layer"),
			'side'
		);
		
		LTPLE_Client()->admin->add_meta_box ( 
		
			'layer-rangediv',
			__( 'Layer Range', 'live-template-editor-client' ), 
			array("cb-default-layer", "default-image"),
			'side'
		);
	
		LTPLE_Client()->admin->add_meta_box (
		
			'default_layer_id',
			__( 'Default Layer', 'live-template-editor-client' ), 
			array("user-layer"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
		
			'tagsdiv-image-type',
			__( 'Image Type', 'live-template-editor-client' ), 
			array("default-image"),
			'side'
		);
	
		LTPLE_Client()->admin->add_meta_box (
		
			'plan_options',
			__( 'Plan options', 'live-template-editor-client' ), 
			array("subscription-plan"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
		
			'email_series',
			__( 'Email series', 'live-template-editor-client' ), 
			array("subscription-plan", "email-campaign"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
		
			'tagsdiv-campaign-trigger',
			__( 'Campaign Trigger', 'live-template-editor-client' ), 
			array("email-campaign"),
			'advanced'
		);
		
		LTPLE_Client()->admin->add_meta_box (
			
			'appData',
			__( 'App Data', 'live-template-editor-client' ), 
			array("user-app"),
			'advanced'
		);
		
	});
	
	// Add layer custom fields
	
	add_filter("cb-default-layer_custom_fields", function(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get layer types
		
		$terms=get_terms( array(
				
			'taxonomy' => 'layer-type',
			'hide_empty' => false,
		));
		
		$layer_types=[];
		$layer_types['none']='None';
		
		foreach($terms as $term){
			
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
			
				array('name'=>"layer-output"),
				'id'		=>"layerOutput",
				'label'		=>"",
				'type'		=>'select',
				'options'	=> array(
				
					'inline-css'	=>'Inline Style',
					'external-css'	=>'Style Sheet',
					'self-hosted'	=>'Self Hosted',
					'canvas'		=>'Canvas'
				),
				'selected'	=>'inline-css',
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"layer-options"),
				'id'		=>"layerOptions",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'line-break'=>'Line break (Enter)'
				
				),
				'checked'	=>array('margin-top'),
				'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"css-libraries"),
				'id'		=>"cssLibraries",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'bootstrap-3' 	=> 'Bootstrap 3'
				
				),
				//'checked'		=> array('bootstrap-3'),
				'description'	=> ''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"js-libraries"),
				'id'		=>"jsLibraries",
				'label'		=>"",
				'type'		=>'checkbox_multi',
				'options'	=>array(
				
					'jquery' 		=> 'JQuery',
					'bootstrap-3' 	=> 'Bootstrap 3'
				
				),
				//'checked'	=>array('jquery'),
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
	});
	
	// Add custom user-layer fields
	
	add_filter("user-layer_custom_fields", 	function (){
			
		$fields=[];
		
		$fields[]=array(
			"metabox" =>
			
				array('name'=>"default_layer_id"),
				'id'=>"defaultLayerId",
				'label'=>"Default Layer ID",
				'type'=>'text',
				'placeholder'=>"",
				'description'=>''
		);
		
		return $fields;
	});
	
	// Add default image custom fields
	
	add_filter("default-image_custom_fields", function(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get image types

		$terms=get_terms( array(
				
			'taxonomy' => 'image-type',
			'hide_empty' => false,
		));
		
		$image_types=[];
		$image_types['none']='None';
		
		foreach($terms as $term){
			
			$image_types[$term->slug]=$term->name;
		}
		
		//get current image type
		
		$terms = wp_get_post_terms( $post_id, 'image-type' );
		
		$default_image_type='';

		if(isset($terms[0]->slug)){
			
			$default_image_type=$terms[0]->slug;
		}
		
		$fields[]=array(
			"metabox" =>
				array('name'=>"tagsdiv-image-type"),
				'id'=>"new-tag-image-type",
				'name'=>'tax_input[image-type]',
				'label'=>"",
				'type'=>'select',
				'options'=>$image_types,
				'selected'=>$default_image_type,
				'description'=>''
		);		
		
		//get current layer range

		$terms=get_terms( array(
				
			'taxonomy' => 'layer-range',
			'hide_empty' => false,
		));

		$layer_ranges=[];
		
		foreach($terms as $term){
			
			$layer_ranges[$term->slug]=$term->name;
		}

		$fields[]=array(
			"metabox" =>
				array('name'=>"layer-rangediv"),
				'type'		=> 'checkbox_multi',
				'id'		=> 'layer-range',
				'name'		=> 'tax_input[layer-range][]',
				'label'		=> '',
				'taxonomy'	=> 'layer-range',
				//'selected'	=> $default_layer_range,
				'options' 	=> $layer_ranges,
				'description'=>''
		);
		
		return $fields;
	});
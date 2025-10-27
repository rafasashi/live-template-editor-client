<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Tutorials {
	

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		$this->parent->register_post_type( 'tutorial','Tutorials','Tutorial', '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> false,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'tutorial',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> array('slug'=>'tutorial'),
			'capability_type' 		=> 'page',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'editor', 'thumbnail', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));		
		
		// add tutorials shortcodes
		
		add_shortcode('ltple-tutorial', array( $this , 'render_tutorial' ) );
	}
	
	public function render_tutorial( $atts ){
		
		$atts = shortcode_atts( array(
		
			'slug'	=> '',
			'title' => '',
			
		), $atts, 'ltple-tutorial' );
		
		if( !empty($atts['slug']) ){
			
			if( empty($atts['title']) ){
				
				$atts['title'] = ucfirst(str_replace('-',' ',$atts['slug']));
			}
			
			$tutorial = '';
			
			if( $post = get_page_by_path( $atts['slug'], OBJECT, 'tutorial' ) ){
				
				$post_id = $post->ID;
				
				$tutorial = apply_filters( 'the_content', $post->post_content );
			}
			else{
				
				// insert tutorial
				
				$post_id = wp_insert_post(array(
				
					'post_type' 	=> 'tutorial',
					'post_status' 	=> 'publish',
					'post_name' 	=> $atts['slug'],
					'post_title' 	=> $atts['title'],
				));
			}
			
			if( $this->parent->user->is_admin ){
				
				$edit_url = add_query_arg(
				
					array(
					
						'post' 		=> $post_id,
						'action' 	=> 'edit',
					),
					get_admin_url() . 'post.php'
				);
				
				$tutorial .= '<br>'; 
				$tutorial .= '<a target="_blank" href="' . $edit_url . '">Edit</a>'; 
			}
		}
		else{
			
			$tutorial = 'Tutorial slug missing...';
		}
		
		return $tutorial;
	}	
	
	/**
	 * Main LTPLE_Client_Tutorials Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Tutorials is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Tutorials instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}

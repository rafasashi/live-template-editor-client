<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Theme {
		
	var $templates = array( 
	
		//'templates/saas.php' 		=> 'LTPLE SaaS',
		//'templates/full-page.php' => 'LTPLE Full Page',
	); 
	
	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	 
	public function __construct($parent) {
		
		$this->parent = $parent;
		
		$this->dir = get_theme_root() . '/live-template-editor-theme';
		
		add_action( 'template_redirect', array($this,'init') );
	
		if( is_dir($this->dir) ){
			
			// Add a filter to the attributes metabox to inject template into the cache.
			
			if( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
				
				// 4.6 and older
				
				add_filter(
					'page_attributes_dropdown_pages_args',
					array( $this, 'register_project_templates' )
				);
				
			} 
			else{
				
				// Add a filter to the wp 4.7 version attributes metabox
				
				add_filter(
					'theme_page_templates', array( $this, 'add_new_template' )
				);
			}
			
			// Add a filter to the save post to inject out template into the page cache
			
			add_filter(
				'wp_insert_post_data',
				array( $this, 'register_project_templates' )
			);
			
			// Add a filter to the template include to determine if the page has our
			// template assigned and return it's path
			
			add_filter(
				'template_include',
				array( $this, 'view_project_template')
			);
		}
	}
	
	public function init(){
		
		$this->slug = get_page_template_slug();
		
		if( !empty($this->slug) ){
			
			$this->path = $this->dir . '/' . $this->slug;
			
			if( file_exists($this->path) ){
					
				add_filter('template_directory',array($this,'get_template_directory'),1,3);

				add_filter('template_directory_uri',array($this,'get_template_directory_uri'),1,3);
				
				add_filter('stylesheet_uri',function($stylesheet_uri, $stylesheet_dir_uri){
					
					$stylesheet_dir_uri = get_template_directory_uri();
					
					$stylesheet_uri = $stylesheet_dir_uri . '/style.css';
					
					return apply_filters( 'ltple_stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
				
				},1,2);
			}
		}
	}
	
	public function get_template_directory($template_dir, $template, $theme_root){
				
		$template_dir = $this->dir;

		return apply_filters( 'ltple_template_directory', $template_dir, $template, $theme_root );
	}
	
	public function get_template_directory_uri($template_dir_uri, $template, $theme_root_uri){
				
		$template_dir_uri = dirname($template_dir_uri) . '/live-template-editor-theme';
		
		return apply_filters( 'ltple_template_directory_uri', $template_dir_uri, $template, $theme_root_uri );
	}
	
	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	 
	public function add_new_template( $posts_templates ) {
		
		$posts_templates = array_merge( $posts_templates, $this->templates );
		
		return $posts_templates;
	}
	
	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	 
	public function register_project_templates( $atts ) {
		
		// Create the key used for the themes cache
		
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		
		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		
		$templates = wp_get_theme()->get_page_templates();
		
		if ( empty( $templates ) ) {
			
			$templates = array();
		}
		
		// New cache, therefore remove the old one
		
		wp_cache_delete( $cache_key , 'themes');
		
		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		
		$templates = array_merge( $templates, $this->templates );
		
		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		
		return $atts;
	}
	
	/**
	 * Checks if the template is assigned to the page
	 */
	 
	public function view_project_template( $template ) {
		
		// Return the search template if we're searching (instead of the template for the first result)
		
		if ( is_search() ) {
			
			return $template;
		}
		
		// Get global post
		
		global $post;
		
		// Return template if post is empty
		
		if ( ! $post ) {
			
			return $template;
		}
		
		// Return default template if we don't have a custom one defined
		
		if ( ! isset( $this->templates[get_post_meta(
			
			$post->ID, '_wp_page_template', true
		
		)] ) ) {
			
			return $template;
		}
		
		// Allows filtering of file path
		
		$filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );
		
		$file =  $filepath . get_post_meta(
			
			$post->ID, '_wp_page_template', true
		);
		
		// Just to be safe, we check if the file exist first
		
		if ( file_exists( $file ) ) {
			
			return $file;
		} 
		else {
			
			echo $file;
		}
		
		// Return template
		
		return $template;
	}

	/**
	 * Main LTPLE_Client_Settings Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Settings instance
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
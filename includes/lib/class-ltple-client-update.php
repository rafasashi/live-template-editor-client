<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Update {

	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;

		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-update/v1', '/layers', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'update_layers'),
			) );
		} );		
	}
	
	public function update_layers($rest = NULL){
		
		$layers = [];
		
		if( $q = get_posts(array( 
	
			'post_type' 	=> 'cb-default-layer', 
			'posts_per_page'=> -1				
		))){
		
			foreach($q as $layer){
				
				$layers[$layer->ID] = add_post_meta( $layer->ID, 'layerContent', $layer->post_content, true);
			}
		}
		
		if( $q = get_posts(array( 
	
			'post_type' 	=> 'user-layer', 
			'posts_per_page'=> -1				
		))){
		
			foreach($q as $layer){
				
				$layers[$layer->ID] = add_post_meta( $layer->ID, 'layerContent', $layer->post_content, true);
			}
		}
		
		return $layers;
	}
	
	/**
	 * Main LTPLE_Client_Update Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Update is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Update instance
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
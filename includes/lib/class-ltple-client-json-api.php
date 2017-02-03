<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Json_API {
	
	var $parent;

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;

		
	}

	public function get_url( $action='get/leads', $user_id = 0 ){
		
		$url = $this->parent->urls->editor . '?api='.$action;
		
		if( $user_id > 0 ){
			
			$url .= '/' . $user_id;
		}
		
		return $url;
	}
	
	/**
	 * Main LTPLE_Client_Json_API Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Json_API is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Json_API instance
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
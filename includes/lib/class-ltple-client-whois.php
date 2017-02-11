<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Whois {
	
	var $parent;

	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 		= $parent;
		
		/*
		include_once( $this->parent->vendor . '/autoload.php' );
		
		$whois = new Whois();
		$query = 'example.com';
		$result = $whois->lookup($query,false);
		echo "<pre>";
		print_r($result);
		echo "</pre>";
		exit;
		*/
	}

	/**
	 * Main LTPLE_Client_Whois Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Whois is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Whois instance
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
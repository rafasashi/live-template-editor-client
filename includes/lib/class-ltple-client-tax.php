<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Tax {
	
	/**
	 * The single instance of LTPLE_Client_Tax.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;	

	var $is_enabled = null;
	var $vat_rate	= null;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		
	}
	
	public function is_enabled(){
		
		if( is_null($this->is_enabled)){
			
			$enabled = get_option($this->parent->_base . 'enable_taxes');
			
			$this->is_enabled = $enabled == 'on' ? true : false;
		}
		
		return $this->is_enabled;
	}
	
	public function get_vat_rate(){
		
		if( is_null($this->vat_rate)){
			
			$this->vat_rate =  intval(get_option($this->parent->_base . 'vat_rate'));
		}
		
		return $this->vat_rate;
	}

	/**
	 * Main LTPLE_Client_Tax Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Tax is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Tax instance
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

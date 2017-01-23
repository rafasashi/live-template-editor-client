<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_User_Profile {

	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;

		if( isset( $_REQUEST['my-profile'] ) && $this->parent->user->loggedin ){
			 
			$this->fields = $this->get_profile_fields();
		}
	}
	
	public function get_profile_fields(){
		
		$fields = array();
		
		$fields['nickname'] = array(

			'id' 			=> 'nickname',
			'label'			=> 'Nickname',
			'description'	=> 'Requiered',
			'placeholder'	=> 'Requiered',
			'type'			=> 'text'
		);
		
		$fields['url'] = array(
		
			'id' 			=> 'url',
			'label'			=> 'Web Site',
			'description'	=> '',
			'placeholder'	=> 'http://',
			'type'			=> 'text'			
		);
		
		return $fields;
	}
	
	
	/**
	 * Main LTPLE_Client_User_Profile Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Stars is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Stars instance
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
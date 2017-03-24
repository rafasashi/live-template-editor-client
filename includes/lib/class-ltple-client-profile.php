<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Profile {

	var $parent;
	var $layer;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;

		add_filter('init', array( $this, 'init_profile' ));
	}
	
	public function init_profile(){
		
		if( !is_admin() ){
			
			$this->fields 	= $this->get_fields();
			
			if( isset($_GET['pr']) && is_numeric($_GET['pr']) ){

				if( $layer = get_post( intval(get_user_meta( intval($_GET['pr']), $this->parent->_base . 'profile_template', true )) ) ){
					
					$this->layer = $layer;
				}				
			}			
		}
	}
	
	public function get_fields( $fields=[] ){
		
		/*
		$fields['user_login'] = array(

			'id' 			=> 'user_login',
			'label'			=> 'Username',
			'description'	=> '',
			'placeholder'	=> 'Username',
			'type'			=> 'text',
			'disabled'		=> true
		);
		*/
		
		$fields['nickname'] = array(

			'id' 			=> 'nickname',
			'label'			=> 'Nickname',
			'description'	=> '',
			'placeholder'	=> 'Nickname',
			'type'			=> 'text',
			'required'		=> true
		);
		
		$fields['description'] = array(

			'id' 			=> 'description',
			'label'			=> 'About me',
			'description'	=> '',
			'placeholder'	=> '',
			'type'			=> 'textarea'
		);
		
		$fields['user_url'] = array(
		
			'id' 			=> 'user_url',
			'label'			=> 'Web Site',
			'description'	=> '',
			'placeholder'	=> 'http://',
			'type'			=> 'url'			
		);
		
		return $fields;
	}
	
	/**
	 * Main LTPLE_Client_Profile Instance
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
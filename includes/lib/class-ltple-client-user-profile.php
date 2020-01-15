<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_User_Profile {

	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		$this->init(); // not working with init or wp_loaded filters
	}
	
	public function init(){

		if( !is_admin() ){
			
			if( $this->parent->user->loggedin ){
				
				if( isset( $_REQUEST['my-profile'] ) ){
					 
					$this->pictures = $this->get_pictures();
					
				}
			}
		}
	}
	
	public function get_pictures( $user_id = 0, $userApps = array() ){
	
		if( $user_id == 0) {
			
			$user_id = $this->parent->user->ID;
			
			$userApps = $this->parent->user->apps;
		}
		
		$pictures 	= array();
		
		//get gravatar picture
		
		$image 			= get_avatar_url( $user_id );
		$pictures[] 	= add_query_arg('_',time(),$image);
		
		// get connected twitter pictures
		
		foreach( $userApps as $i => $userApp ){
			
			$key = 'twitter-';
			
			if( strpos( $userApp->post_name, $key ) === 0 ){
				
				$name 		= str_replace($key,'',$userApp->post_name);
				$pictures[] = 'https://twitter.com/'.$name.'/profile_image?size=original&_'.time();
			}
		}

		// get local picture

		$pictures[] = $this->parent->image->get_local_avatar_url( $user_id );
		
		$fields['profile_picture'] = array(

			'id' 			=> $this->parent->_base . 'profile_picture',
			'label'			=> 'Avatar',
			'description'	=> 'Upload or select an avatar from <a class="label label-default" target="_blank" href="https://en.gravatar.com/">Gravatar</a> <a class="label label-info" href="'.$this->parent->apps->getAppUrl('twitter','connect').'">Twitter</a>',
			'type'			=> 'avatar',
			'options'		=> $pictures,
		);
		
		$fields['profile_banner'] = array(

			'id' 			=> $this->parent->_base . 'profile_banner',
			'label'			=> 'Header',
			'description'	=> 'Upload a header picture 1920 x 1080 pixels recommended',
			'type'			=> 'banner',
			'default'		=> $this->parent->image->get_banner_url( $user_id ) . '?' . time(),
		);
		
		return $fields;
	}
	
	/**
	 * Main LTPLE_Client_User_Profile Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Profile is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Profile instance
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

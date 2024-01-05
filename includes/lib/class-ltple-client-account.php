<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Account {

	private $parent;
	
	var $notificationFields = null;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		add_action('wp', array( $this,'init_account'),9999);
		
		add_shortcode('ltple-client-account', array( $this , 'get_account_shortcode' ) );
	}
	
	public function init_account(){
		
		if( !is_admin() && $this->parent->user->loggedin ){
		
			// update account fields
		
			$this->handle_update_account();
		}
	}
	
	public function get_account_shortcode(){
		
		ob_start();
		
		include($this->parent->views . '/navbar.php');
		
		if( $this->parent->user->loggedin ){

			include($this->parent->views . '/account.php');
		}
		else{
			
			echo $this->parent->login->get_form();
		}
		
		return ob_get_clean();
	}

	public function get_notification_fields(){
		
		if( is_null($this->notificationFields) ){
			
			$settings = array();
			
			if( !empty($this->parent->user->notify) ){
				
				$descriptions = $this->parent->email->get_notification_fields('description');
				
				foreach( $this->parent->user->notify as $key => $value ){
					
					if( !empty($descriptions[$key]) ){
						
						$settings[$key] = array(

							'id' 			=> $this->parent->_base . 'notify['.$key.']',
							'label'			=> ucfirst($key),
							'description'	=> $descriptions[$key],
							'type'			=> 'switch',
							'data'			=> ( $value == 'true' ? 'on' : 'off' ),
						);				
					}
				}
			}
			
			$this->notificationFields = apply_filters('ltple_notification_fields',$settings,$this->parent->user->ID);
		}
		
		return $this->notificationFields;
	}

	public function handle_update_account(){
		
		if( !empty($this->parent->user->ID) ){
			
			$user_id 	= $this->parent->user->ID;
			$notify 	= $this->parent->user->notify;

			if( !empty($_POST['settings']) ){
				
				if( $_POST['settings'] == 'email-notifications' ){
					
					if( !empty($notify) ){
						
						// save notification settings			
						
						foreach( $notify as $key => $value ){
						
							if( !empty($_POST['ltple_notify'][$key]) && $_POST['ltple_notify'][$key] == 'on' ){
								
								$notify[$key] = 'true';
							}
							else{
								
								$notify[$key] = 'false';
							}
						}

						update_user_meta($user_id, 'ltple_notify', $notify);
					}
					
					do_action('ltple_update_notification_settings',$user_id);
				}
				
				do_action('ltple_update_account');
			}
		}
	}

	/**
	 * Main LTPLE_Client_Account Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Account is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Account instance
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

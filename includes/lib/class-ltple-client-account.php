<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Account {

	private $parent;
	
	var $notificationSettings 	= null;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		add_filter('ltple_loaded', array( $this, 'init_account' ));
		
		add_shortcode('ltple-client-account', array( $this , 'get_account_shortcode' ) );
	}
	
	public function init_account(){
		
		if( !is_admin() ){
			
			if( $this->parent->user->loggedin ){
			
				// get account fields

				if( empty($_GET['tab']) || $_GET['tab'] == 'email-notifications' ){
					
					$this->set_notification_fields();
				}
				
				// update account fields
			
				$this->handle_update_account();
			}
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

	public function set_notification_fields(){
		
		if( is_null($this->notificationSettings) ){
			
			if( !empty($this->parent->user->notify) ){
				
				$descriptions = $this->parent->email->get_notification_settings('description');
				
				foreach( $this->parent->user->notify as $key => $value ){
					
					if( !empty($descriptions[$key]) ){
						
						$this->notificationSettings[$key] = array(

							'id' 			=> $this->parent->_base . 'notify['.$key.']',
							'label'			=> ucfirst($key),
							'description'	=> $descriptions[$key],
							'type'			=> 'switch',
							'data'			=> ( $value == 'true' ? 'on' : 'off' ),
						);				
					}
				}
			}
		}
		
		return $this->notificationSettings;
	}

	public function handle_update_account(){
			
		if(!empty($_POST['settings'])){
			
			if( $_POST['settings'] == 'email-notifications' && !empty($this->parent->user->notify) ){
				
				// save notification settings			
				
				$notify = $this->parent->user->notify;
				
				foreach( $notify as $key => $value ){
				
					if( !empty($_POST['ltple_notify'][$key]) && $_POST['ltple_notify'][$key] == 'on' ){
						
						$notify[$key] = 'true';
						
						$this->notificationSettings[$key]['data'] = 'on';
					}
					else{
						
						$notify[$key] = 'false';
						
						$this->notificationSettings[$key]['data'] = 'off';
					}
				}
				
				update_user_meta($this->parent->user->ID, 'ltple__can_spam', $notify['series']);
					
				update_user_meta($this->parent->user->ID, 'ltple_notify', $notify);					
			
				$this->parent->user->notify = $notify;
			}
			
			do_action('ltple_update_account');
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

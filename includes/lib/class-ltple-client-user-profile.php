<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_User_Profile {

	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		if($this->parent->user->loggedin){
			
			if( isset( $_REQUEST['my-profile'] ) || isset( $_GET['pr'] ) ){
				 
				$this->customization = $this->get_customization();

				$this->apps = $this->get_apps();
				
				if(!empty($_POST)){
					
					// save general information
					
					foreach( $this->parent->profile->fields as $id => $field){
						
						if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
							
							update_user_meta( $this->parent->user->ID, $id, sanitize_text_field($_POST[$id]) );
						}
					}
					
					// save profile customization
					
					foreach( $this->customization as $field){
						
						$id = $field['id'];
						
						if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
							
							update_user_meta( $this->parent->user->ID, $id, wp_kses_post($_POST[$id]) );
						}
					}
				}
			}
		}
	}

	public function get_customization(){
		
		if( !empty($this->parent->user->layers) ){
			
			$templates = array( -1 => 'none' );
			
			foreach($this->parent->user->layers as $i => $layer) {
				
				$templates[$layer->ID] = ucfirst($layer->post_title);
			}
		}
		else{
			
			$templates = array( -1 => 'no saved templates' );
		}
		
		$fields = array();
		
		$fields['profile_template'] = array(

			'id' 			=> $this->parent->_base . 'profile_template',
			'label'			=> 'Template',
			'description'	=> 'Use a saved template instead of the custom html below',
			'type'			=> 'select',
			'options'		=> $templates,
			'required'		=> true,
		);
		
		$fields['profile_html_body'] = array(

			'id' 			=> $this->parent->_base . 'profile_html',
			'label'			=> 'HTML body',
			'description'	=> '',
			'placeholder'	=> '',
			'type'			=> 'textarea'
		);
		
		$fields['profile_css'] = array(
		
			'id' 			=> $this->parent->_base . 'profile_css',
			'label'			=> 'CSS',
			'description'	=> '',
			'placeholder'	=> '',
			'type'			=> 'textarea'			
		);
		
		return $fields;
	}
	
	public function get_apps(){
		
		$fields = array();
		
		foreach($this->parent->apps->appList as $app){
			
			$key = 'display_'.str_replace('-','_',$app->slug);
			
			$accounts = array( -1 => 'none' );
			
			$fields[$key] = array(

				'id' 			=> $this->parent->_base . $key,
				'label'			=> ucfirst($app->name),
				'description'	=> '',
				'type'			=> 'select',
				'options'		=> $accounts,
				'required'		=> true,
			);
		}
		
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
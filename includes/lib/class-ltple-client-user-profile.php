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
					
					// save displayed apps
					
					foreach( $this->apps as $field){
						
						$id = $field['id'];
						
						if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
							
							$content = wp_kses_post($_POST[$id]);

							update_user_meta( $this->parent->user->ID, $id, $content );
						}
					}
					
					// save profile customization
					
					foreach( $this->customization as $field){
						
						$id = $field['id'];
						
						if( isset($_POST[$id]) && ( !isset($field['disabled']) || $field['disabled'] == false ) && ( !isset($field['required']) || $field['required'] === false || ( $field['required'] === true && !empty($_POST[$id])) ) ){
							
							$content = wp_kses_post($_POST[$id]);
							
							if(isset($field['allowed_tags'])){
								
								$content = strip_tags($content, $field['allowed_tags']);
							}
							
							update_user_meta( $this->parent->user->ID, $id, $content );
						}
					}
				}
			}
		}
	}

	public function get_customization(){
		
		$templates = array( -1 => 'none', -2 => 'custom HTML' );
		
		if( !empty($this->parent->user->layers) ){

			foreach($this->parent->user->layers as $i => $layer) {
				
				$templates[$layer->ID] = 'saved - ' . ucfirst($layer->post_title);
			}
		}
		
		$fields = array();
		
		$fields['profile_template'] = array(

			'id' 			=> $this->parent->_base . 'profile_template',
			'label'			=> 'Template',
			'description'	=> '',
			'type'			=> 'select',
			'options'		=> $templates,
			'required'		=> true,
		);
		
		$fields['profile_title'] = array(

			'id' 			=> $this->parent->_base . 'profile_title',
			'label'			=> 'Title',
			'description'	=> 'Add a title to your page without ' . htmlentities('<title></title>'),
			'type'			=> 'text',
			'placeholder'	=> 'Welcome to my profile',
			'required'		=> true,
		);

		$fields['profile_html_body'] = array(

			'id' 			=> $this->parent->_base . 'profile_html',
			'label'			=> 'HTML body',
			'description'	=> 'Use HTML content without ' . htmlentities('<body></body>'),
			'placeholder'	=> '',
			'type'			=> 'textarea',
			'allowed_tags'	=> '<div><p><a><header><section><aside><main><nav><footer><em><i><u><font><strong><br><hr><h1><h2><h3><h4><h5><h6><img><ol><ul><li><span>',
		);
		
		$fields['profile_css'] = array(
		
			'id' 			=> $this->parent->_base . 'profile_css',
			'label'			=> 'CSS',
			'description'	=> 'Add CSS rules without ' . htmlentities('<style></style>'),
			'placeholder'	=> '',
			'type'			=> 'textarea',
			'allowed_tags'	=> '',			
		);
		
		return $fields;
	}
	
	public function get_apps( $user_id = 0 ){
		
		$fields = array();
		
		if( $user_id == $this->parent->user->ID && !empty($this->parent->user->apps) ){
			
			$userApps = $this->parent->user->apps;
		}
		else{
			
			$userApps = get_posts(array(
					
				'author'      => $user_id,
				'post_type'   => 'user-app',
				'post_status' => 'publish',
				'numberposts' => -1
			));				
		}
		
		foreach($this->parent->apps->appList as $app){
			
			$key = 'display_'.str_replace('-','_',$app->slug);
			
			$accounts = array( 'none' => 'none' );
			
			foreach( $this->parent->user->apps as $userApp ){
				
				if(strpos($userApp->post_name, $app->slug . '-')===0){
					
					$accounts[$userApp->post_name] = $userApp->post_title;
				}
			}
			
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
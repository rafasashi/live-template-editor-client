<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_User_Domains {

	var $parent;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		if( $this->parent->user->loggedin && $this->parent->settings->options->enable_subdomains == 'on' ){
			
			$this->list = $this->get_user_domains( $this->parent->user, true );
			
			$this->save_user_domains();
		}
	}
	
	public static function get_user_domains( $user, $insert = false ){
	
		$subdomain = $user->user_login . '.' . $_SERVER['HTTP_HOST'];

		$domains = get_posts(array(
			
			'author'   		=> $user->ID,
			'post_type'   	=> 'user-domain',
			'post_status' 	=> 'publish',
			//'numberposts' => -1,
		));
		
		/*
		if( $insert && !in_array_field($subdomain,'post_title',$domains) ){

			$post_id = wp_insert_post(array(
				
				'post_author' 	=> $user->ID,
				'post_title' 	=> $subdomain,
				'post_type' 	=> 'user-domain',
				'post_status' 	=> 'publish'
			));

			$domains[] = get_post($post_id);
		}
		*/
		
		if( !empty($domains) ){
			
			foreach( $domains as $domain ){
				
				$domain->domainUrls = get_post_meta($domain->ID ,'domainUrls', true);
			}				
		}	

		return $domains;
	}
	
	public function save_user_domains(){

		if( !is_admin() ){
		
			if( !empty($_POST['layerId']) && !empty($_POST['domainUrl']['domainId']) && isset($_POST['domainUrl']['domainPath']) && !empty($_POST['domainAction']) ){
				
				$layerId 	= floatval($_POST['layerId']);
				
				$domainId 	= floatval($_POST['domainUrl']['domainId']);
				
				$domainPath = sanitize_text_field($_POST['domainUrl']['domainPath']);
				
				if( $_POST['domainAction'] == 'assign' && $layerId > 0 && is_numeric($domainId) ){
					
					if( $this->parent->user->is_admin || in_array_field($layerId, 'ID', $this->parent->user->layers) ){
						
						foreach( $this->list as $list ){
							
							if( $domainId == $list->ID ){
								
								if( in_array( $domainPath, $list->domainUrls) ){
									
									$this->parent->message .= '<div class="alert alert-warning">';
									
										$this->parent->message .= 'This url already exists...';
										
									$this->parent->message .= '</div>';
								}
								else{
									
									// update new domain

									$list->domainUrls[$layerId] = $domainPath;
								
									update_post_meta( $list->ID, 'domainUrls', $list->domainUrls );
								}
							}
							elseif( isset($list->domainUrls[$layerId]) ){
								
								// update previous domain
								
								unset($list->domainUrls[$layerId]);
								
								update_post_meta( $list->ID, 'domainUrls', $list->domainUrls );
							}
						}
					}
				}
			}
		}		
	}
	
	/**
	 * Main LTPLE_Client_User_Domains Instance
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
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Whois {
	
	var $parent;

	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 		= $parent;
		
		if(!empty($_POST['valid_domain'])){
			
			$this->init_whois();
		}
	}
	
	public function init_whois(){
		
		include_once( $this->parent->vendor . '/autoload.php' );
		
		$whois = new Whois();
		
		foreach( $_POST['valid_domain'] as $id){
			
			if(isset($_POST[$id]['domain_name'])){
				
				$_SESSION['message'] = '';
				
				$domains = $_POST[$id]['domain_name'];
				
				foreach( $domains['name'] as $d => $name ){

					if( isset($domains['ext'][$d]) ){

						$ext = $domains['ext'][$d];
					
						$domain = str_replace( array('_','.'),'-', sanitize_title( str_replace($ext,'',$name) )) . $ext;

						$result = $whois->lookup($domain,false);

						if( !isset($result["regrinfo"]["registered"]) || $result["regrinfo"]["registered"] != 'no'){
							
							unset($_POST[$id]);
							
							$_SESSION['message'] .= '<div class="alert alert-warning" style="margin-bottom:0px;">';
								
								$_SESSION['message'].= 'Sorry, <b>'.$domain.'</b> is already registered by someone else...';
							
							$_SESSION['message'] .= '</div>';
						}
						else{
							
							$_SESSION['message'] .= '<div class="alert alert-success" style="margin-bottom:0px;">';
								
								$_SESSION['message'].= 'Congratulations, <b>'.$domain.'</b> is available';
							
							$_SESSION['message'] .= '</div>';
						}
					}
				}
			}
		}	
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
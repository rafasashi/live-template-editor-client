<?php

if ( ! defined( 'ABSPATH' ) ) exit;
 
class LTPLE_Client_Session {

	public $key_num = 1;

	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		// set secret key number

		if(isset($_GET['ltple-key'])){
			
			$this -> key_num = (int)trim($_GET['ltple-key']);
		}

		// Handle login synchronization
		
		add_action( 'init', array( $this, 'synchronize_session' ), 0 );			
	
		// Handle profile updates
		
		add_action( 'user_profile_update_errors', array( $this, 'prevent_email_change'), 10, 3 );
		
		add_action( 'admin_init', array( $this, 'disable_user_profile_fields'));	
	
		
	}

	
	public function prevent_email_change( $errors, $update, $user ) {
	
		if( !empty($user->ID) ){
	
			$old = get_user_by('id', $user->ID);

			if( $user->user_email != $old->user_email   && (!current_user_can('create_users')) ){
				
				$user->user_email = $old->user_email;
			}
		}
	}
	
	public function disable_user_profile_fields() {
	 
		global $pagenow;
	 
		// apply only to user profile or user edit pages
		if ($pagenow!=='profile.php' && $pagenow!=='user-edit.php') {
			
			return;
		}
	 
		// do not change anything for the administrator
		if (current_user_can('administrator')) {
			
			return;
		}
	 
		add_action( 'admin_footer', array( $this,'disable_user_profile_fields_js' ));
	}
	 
	 
	/**
	 * Disables selected fields in WP Admin user profile (profile.php, user-edit.php)
	 */
	public function disable_user_profile_fields_js() {
		
		?>
			<script>
				jQuery(document).ready( function($) {
					var fields_to_disable = ['email', 'username'];
					for(i=0; i<fields_to_disable.length; i++) {
						if ( $('#'+ fields_to_disable[i]).length ) {
							$('#'+ fields_to_disable[i]).attr("disabled", "disabled");
							$('#'+ fields_to_disable[i]).after("<span class=\"description\"> " + fields_to_disable[i] + " cannot be changed.</span>");
						}
					}
				});
			</script>
		<?php
	}
	
	
	public function synchronize_session(){
		
		// set user information
		
		$this->user_id = get_current_user_id();
		
		// check user verified
		
		if( current_user_can('administrator') ) {

			$this->user_verified = 'true';
		}
		else{
			
			$this->user_verified = get_user_meta( $this->user_id, "ussync_email_verified", TRUE);
		}
		
		// add cors header
		if(is_user_logged_in()){
			
			add_action( 'send_headers', array($this, 'add_cors_header') );
			add_action( 'send_headers', array($this, 'add_content_security_policy') );
		}		
		
		// synchronize sessions

		if( isset($_GET['action']) && $_GET['action']=='logout' ){
			
			$this->get_domains(true);
		}
		elseif( isset($_GET['ltple-status']) && $_GET['ltple-status']=='loggedin' ){
			
			echo 'User logged in!';
			exit;
		}
		elseif( is_user_logged_in() && isset($_GET['redirect_to']) ){
			
			if( !empty($_GET['reauth']) && $_GET['reauth'] == '1' ){
				
				echo 'Error accessing the current session...';			
			}
			else{

				wp_safe_redirect( trim( $_GET['redirect_to'] ) );
			}
			
			exit;
		}
		elseif( isset($_GET['ltple-token']) && isset($_GET['ltple-id']) && isset($_GET['ltple-ref']) ){

			//decrypted user_name
			
			$user_name = trim($_GET['ltple-id']);
			$user_name = $this->parent->ltple_decrypt_uri($user_name);

			//decrypted user_name
			
			$user_ref = ($_GET['ltple-ref']);
			
			$user_ref = $this->parent->ltple_decrypt_uri($user_ref);
			
			//decrypted user_email
			
			$user_email = trim($_GET['ltple-token']);
			$user_email = $this->parent->ltple_decrypt_uri($user_email);
			
			//set user ID
			
			$user_email = sanitize_email($user_email);
			
			//get domain list

			$domain_list = array( $this->parent->server->url );
			
			//get valid domains

			$domains=[];
			
			foreach($domain_list as $domain){
				
				$domain = trim($domain);
				$domain = rtrim($domain,'/');
				$domain = preg_replace("(^https?://)", "", $domain);
				
				$domains[$domain]='';
			}

			//check referer
			
			$valid_referer=false;
			
			if(isset($domains[$user_ref])){
				
				$valid_referer=true;
			}			
			
			if($valid_referer===true){
				
				if(isset($_GET['ltple-status']) && $_GET['ltple-status']=='loggingout'){
					
					// Logout user
					
					if( $user = get_user_by('email', $user_email ) ){
						
						// get all sessions for user with ID
						$sessions = WP_Session_Tokens::get_instance($user->ID);

						// we have got the sessions, destroy them all!
						$sessions->destroy_all();	

						echo 'User logged out...';
						exit;					
					} 
					else{
						
						$this->parent->ltple_decrypt_uri($_GET['ltple-token']);
						
						echo 'Error logging out...';
						exit;					
					}
				}			
				else{
					
					$current_user = wp_get_current_user();
					
					if(!is_user_logged_in()){			
						
						// check if the user exists
						
						if( !email_exists( $user_email ) ){
						
							$ussync_no_user = get_option('ussync_no_user_'.$this -> key_num);
						
							if( $ussync_no_user == 'register_suscriber' ){
								
								// register new suscriber
								
								$user_data = array(
								
									'user_login'  =>  $user_name,
									'user_email'   =>  $user_email,
								);
														
								if( get_userdatabylogin($user_name) ){
									
									echo 'User name already exists!';
									exit;							
								}
								elseif( $user_id = wp_insert_user( $user_data ) ) {
									
									// update email status
									
									add_user_meta( $user_id, 'ussync_email_verified', 'true');
								}
								else{
									
									echo 'Error creating a new user!';
									exit;								
								}
							}
							else{
								
								echo 'This user doesn\'t exist...';
								exit;							
							}
						}
						
						// destroy current user session

						$sessions = WP_Session_Tokens::get_instance($current_user->ID);
						$sessions->destroy_all();	

						// get new user
						
						$user = get_user_by('email',$user_email);
						
						if( isset($user->ID) && intval($user->ID) > 0 ){
							
							//do the authentication
							
							clean_user_cache($user->ID);
							
							wp_clear_auth_cookie();
							wp_set_current_user( $user->ID );
							wp_set_auth_cookie( $user->ID , true, is_ssl());

							update_user_caches($user);
							
							if(is_user_logged_in()){
								
								//redirect after authentication
								
								//wp_safe_redirect( rtrim( get_site_url(), '/' ) . '/?ltple-status=loggedin');

								echo 'User '.$user->ID . ' logged in...';
								exit;
							}
						}
						else{
							
							echo 'Error logging in...';
							exit;						
						}					
					}
					elseif($current_user->user_email != $user_email){
						
						//wp_mail($dev_email, 'Debug user sync id ' . $current_user->ID . ' - ip ' . $this->user_ip . ' user_email: '. $current_user->user_email .' request email: '. $user_email.' $_SERVER: ' . print_r($_SERVER,true));
					
						echo 'Another user already logged in...';
						exit;
					}
					else{
						
						echo 'User already logged in...';
						exit;
					}
				}
			}
			else{

				echo 'Host not allowed to synchronize...';
				exit;				
			}
		}
		elseif( is_user_logged_in() && !isset($_GET['ltple-token']) && $this->user_verified === 'true'){
			
			//add footers
			
			if( is_admin() ) {
				
				add_action( 'admin_footer_text', array( $this, 'get_domains' ));
			}
			else{
				
				add_action( 'wp_footer', array( $this, 'get_domains' ));
			}			
		}
	}
	
	
	public function add_cors_header() {
		
		// Allow from valid origin
		/*
		if(isset($_SERVER['HTTP_ORIGIN'])) {
			
			//header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header("Access-Control-Allow-Origin: *");
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}

		// Access-Control headers are received during OPTIONS requests

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
				header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
				header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

			exit(0);
		}
		*/
	}
	
	public function add_content_security_policy() {
		
		if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
			
			header("Content-Security-Policy: upgrade-insecure-requests");
		}
	}
	
	
	public function get_domains($loggingout=false){
		
		if( $user = wp_get_current_user() ){

			//get list of domains
			
			$domains = array( $this->parent->server->url );

			//get encrypted user name
			
			$user_name = $user->user_login;
			$user_name = $this->parent->ltple_encrypt_uri($user_name);
			
			//get encrypted user email
			
			$user_email = $user->user_email;
			$user_email = $this->parent->ltple_encrypt_uri($user_email);
			
			//get current domain
			
			$current_domain = get_site_url();
			$current_domain = rtrim($current_domain,'/');
			$current_domain = preg_replace("(^https?://)", "", $current_domain);
			
			//get encrypted user referer
			
			//$user_ref = $_SERVER['HTTP_HOST'];
			$user_ref = $current_domain;
			$user_ref = $this->parent->ltple_encrypt_uri($user_ref);
			
			if(!empty($domains)){
				
				foreach($domains as $domain){
					
					$domain = trim($domain);
					$domain = rtrim($domain,'/');
					$domain = preg_replace("(^https?://)", "", $domain);

					if( $loggingout === true ){

						$url = $this->parent->request->proto . $domain . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num . '&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&ltple-status=loggingout'.'&_' . time();

						$response = wp_remote_get( $url, array(
						
							'timeout'     => 5,
							'user-agent'  => $this -> user_agent,
							'headers'     => array(
							
								'X-Forwarded-For' => $this->user_ip
							),
						)); 						
					}
					elseif($current_domain != $domain){
						
						//output html
					
						//echo '<img class="ltple" src="' . $this->parent->request->proto . $domain . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num.'&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&_' . time() . '" height="1" width="1" style="border-style:none;" >';								
						
						echo'<iframe class="ltple" src="' . $this->parent->request->proto . $domain . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num.'&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&_' . time() . '" style="width:1px;height:1px;border-style:none;position:absolute;display:block;"></iframe>';
					}
				}
				
				if( $loggingout === true ){
					
					wp_logout();
					
					if(!empty($_GET['redirect_to'])){
						
						wp_safe_redirect( trim( $_GET['redirect_to'] ) );
					}
					else{
						
						wp_safe_redirect( wp_login_url() );
					}
					
					exit;
				}				
			}
		}
	}

	
}

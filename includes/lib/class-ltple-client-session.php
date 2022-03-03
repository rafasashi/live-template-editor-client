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
	
		register_activation_hook(__FILE__, array($this, 'activate_plugins_email'));

		add_shortcode('ussyncemailverificationcode', array($this, 'get_email_verification_link'));

		add_filter('manage_users_columns', array($this, 'update_user_table'), 10, 1);
		add_filter('manage_users_custom_column', array($this, 'modify_user_table_row'), 10, 3);
		
		add_action('user_register', array( $this, 'after_user_register'), 10, 1);
		add_action('admin_head', array($this, 'verify_user'));
		
		add_action('init', array($this, 'verify_registered_user'));	
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
			
			$this->get_servers(true);
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
		/*
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
			
			//get server list

			$server_list = array( $this->parent->server->url );
			
			//get valid servers

			$servers=[];
			
			foreach($server_list as $server){
				
				$server = trim($server);
				$server = rtrim($server,'/');
				$server = preg_replace("(^https?://)", "", $server);
				
				$servers[$server]='';
			}

			//check referer
			
			$valid_referer=false;
			
			if(isset($servers[$user_ref])){
				
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
						
							echo 'This user doesn\'t exist...';
							exit;
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
						
						//wp_mail($dev_email, 'Debug user sync id ' . $current_user->ID . ' - ip ' . $this->parent->request->ip . ' user_email: '. $current_user->user_email .' request email: '. $user_email.' $_SERVER: ' . print_r($_SERVER,true));
					
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
				
				add_action( 'admin_footer_text', array( $this, 'get_servers' ));
			}
			else{
				
				add_action( 'wp_footer', array( $this, 'get_servers' ));
			}			
		}
		*/
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
	
	
	public function get_servers($loggingout=false){
		
		if( $user = wp_get_current_user() ){

			//get list of servers
			
			$servers = array( $this->parent->server->url );

			//get encrypted user name
			
			$user_name = $user->user_login;
			$user_name = $this->parent->ltple_encrypt_uri($user_name);
			
			//get encrypted user email
			
			$user_email = $user->user_email;
			$user_email = $this->parent->ltple_encrypt_uri($user_email);
			
			//get current server
			
			$current_server = get_site_url();
			$current_server = rtrim($current_server,'/');
			$current_server = preg_replace("(^https?://)", "", $current_server);
			
			//get encrypted user referer
			
			//$user_ref = $_SERVER['HTTP_HOST'];
			$user_ref = $current_server;
			$user_ref = $this->parent->ltple_encrypt_uri($user_ref);
			
			if(!empty($servers)){
				
				foreach($servers as $server){
					
					$server = trim($server);
					$server = rtrim($server,'/');
					$server = preg_replace("(^https?://)", "", $server);

					if( $loggingout === true ){

						$url = $this->parent->request->proto . $server . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num . '&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&ltple-status=loggingout'.'&_' . time();

						$response = wp_remote_get( $url, array(
						
							'timeout'     => 5,
							'user-agent'  => $this->parent->request->user_agent,
							'headers'     => array(
								
								'X-Forwarded-Server' 	=> $_SERVER['HTTP_HOST'],
								'X-Forwarded-For' 		=> $this->parent->request->ip
							),
						)); 						
					}
					elseif($current_server != $server){
						
						//output html
					
						//echo '<img loading="lazy" class="ltple" src="' . $this->parent->request->proto . $server . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num.'&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&_' . time() . '" height="1" width="1" style="border-style:none;" >';								
						
						echo'<iframe class="ltple" src="' . $this->parent->request->proto . $server . '/?ltple-token='.$user_email.'&ltple-key='.$this -> key_num.'&ltple-id='.$user_name.'&ltple-ref='.$user_ref.'&_' . time() . '" style="width:1px;height:1px;border-style:none;position:absolute;display:block;"></iframe>';
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
	
	public function get_user_data($token,$user_id=null,$reset=true){
		
		$data = null;
		
		if( is_null($user_id) ){
		
			if( $user = wp_get_current_user() ){
				
				$user_id = $user->ID;
			}
		}
		
		if( !empty($user_id) ){
			
			$token = sanitize_title($token);
			
			$data = get_user_meta($user->ID,$this->parent->_base . 'session_' . $token ,true);
		
			if( $reset === true && !empty($data) ){
				
				delete_user_meta($user->ID,$this->parent->_base . 'session_' . $token);
			}
		}
		
		return $data;
	}
	
	public function update_user_data($token,$value,$user_id=null){

		if( is_null($user_id) ){
		
			if( $user = wp_get_current_user() ){
				
				$user_id = $user->ID;
			}
		}
		
		if( !empty($user_id) ){
			
			$token = sanitize_title($token);
			
			$value = wp_kses_normalize_entities($value);
	
			if( !empty($value) ){
				
				return update_user_meta($user_id,$this->parent->_base . 'session_' . $token,$value);
			}
		}
		
		return false;
	}

	public function after_user_register($user_id){

		// the new user just registered but never logged in yet
		add_user_meta($user_id, 'ussync_has_not_logged_in_yet', 'true');
	}

	public function verify_registered_user(){
		
		if(isset($_GET["ussync_confirmation_verify"])){
			
			$user_meta = explode("@", base64_decode($_GET["ussync_confirmation_verify"]));
			
			if (get_user_meta((int) $user_meta[1], "ussync_email_verifiedcode", TRUE) == $user_meta[0]) {
				
				update_user_meta((int) $user_meta[1], "ussync_email_verified", "true");
				
				delete_user_meta((int) $user_meta[1], "ussync_email_verifiedcode");
				
				echo '<div class="updated fade"><p><b>Congratulations</b> your account has been successfully verified!</p></b></div>';
			}
			elseif(get_user_meta((int) $user_meta[1], "ussync_email_verified", TRUE) == 'true'){
				
				echo '<div class="updated fade"><p>Your account has already been verified...</p></b></div>';
			}
			else{
				
				echo '<div class="updated fade"><p><b>Oops</b> something went wrong during your account validation...</p></b></div>';
			}
		}			
		elseif(is_user_logged_in()){
			
			$user_id = get_current_user_id();
			
			$user_meta = get_user_meta($user_id);

			if(isset($user_meta['ussync_has_not_logged_in_yet'])){
				
				delete_user_meta($user_id, 'ussync_has_not_logged_in_yet');
				
				update_user_meta($user_id, 'ussync_email_verified', 'true');
			}					
		}
	}		

	public function activate_plugins_email() {
		
		ob_start();
		include plugin_dir_path(__FILE__) . "views/demo_email.html";
		$demo_email_content = ob_get_clean();
		
		update_option("ussync-email-header", $demo_email_content,false);
		update_option("ussync_email_confemail", get_option("admin_email"),false);
		update_option("ussync_email_conf_title", "Please Verify Your email Account",false);
	}

	public function view_email_setting() {
		
		include plugin_dir_path(__FILE__) . "views/email-setting.php";
	}

	public function view_email_verification() {

		include plugin_dir_path(__FILE__) . "views/email-verification.php";
	}

	public function codeMailSender($email) {
				
		$urlparts = parse_url(site_url());
		$domain = $urlparts ['host'];						
				
		$Email_title = get_option("ussync_email_conf_title");
		$sender_email = get_option("ussync_email_confemail");
		$message = get_option("ussync-email-header");
		
		$headers   = [];
		$headers[] = 'From: ' . get_bloginfo('name') . ' <noreply@'.$domain.'>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html';
		
		$preMesaage = "<html><body><div style='width:700px;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div></div></body></html>";
		
		if(!wp_mail($email, $Email_title, $preMesaage, $headers)){
			
			global $phpmailer;
			
			var_dump($phpmailer->ErrorInfo);exit;				
		}
	}		
	
	public function get_email_verification_link(){
		
		$link='';
		
		if(isset($_GET["user_id"]) && wp_verify_nonce($_GET["wp_nonce"], "ussync_email")){
			
			$user_id = $_GET['user_id'];
			
			$secret = get_user_meta( (int) $user_id, "ussync_email_verifiedcode", true);
			
			$createLink = $secret . "@" . $user_id;
			
			$hyperlink = get_admin_url() . "profile.php?ussync_confirmation_verify=" . base64_encode($createLink);
			
			$link .= "<a href='" . $hyperlink . "'> Click here to verify</a>";
		}
		
		return $link;
	}

	public function update_user_table($column) {
		
		$column['ussync_verified'] = 'Verified user';
		return $column;
	}

	public function modify_user_table_row($val, $column_name, $user_id) {
		
		$user_role = get_userdata($user_id);
		
		$row='';
		
		if ($column_name == "ussync_verified") {

			if ($user_role->roles[0] != "administrator") {
				
				if (get_user_meta($user_id, "ussync_email_verified", true) != "true") {
					
					if (get_user_meta($user_id, "ussync_has_not_logged_in_yet", true) == "true") {
						
						$text = "<img src='" . $this->parent->assets_url . "images/time.png' width=25 height=25>";
						$row .= "<a title=\"Validate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "true"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";							
					}
					else{
						
						$text = "<img src='" . $this->parent->assets_url . "images/wrong_arrow.png' width=25 height=25>";
						$row .= "<a title=\"Validate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "true"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";
					}
					

					//$text = "<img src='" . $this->parent->assets_url . "images/send.png' width=25 height=25>";
					//$row .= "<a title=\"Resend Validation Email\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "resend"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";						
				}
				else{
					
					$text = "<img src='" . $this->parent->assets_url . "images/right_arrow.png' width=25 height=25>";
					$row .= "<a title=\"Unvalidate User\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ussync_email"), "ussync_confirm" => "false"), get_admin_url() . "users.php") . "\">" . apply_filters("ussync_email_confirmation_manual_verify", $text) . "</a>";						
				}
				
			} 
			else {
				
				$row .= "Admin";
			}
		}
		
		return $row;
	}

	public function verify_user() {
		
		//var_dump(wp_verify_nonce($_GET["wp_nonce"], "ussync_email"));
		
		if(isset($_GET["user_id"]) && isset($_GET["wp_nonce"]) && wp_verify_nonce($_GET["wp_nonce"], "ussync_email") && isset($_GET["ussync_confirm"])) {
			
			if($_GET["ussync_confirm"] === 'true' || $_GET["ussync_confirm"] === 'false'){
				
				update_user_meta($_GET["user_id"], "ussync_email_verified", $_GET["ussync_confirm"]);
			}
			elseif($_GET["ussync_confirm"] === 'resend'){

				$user_id = intval($_GET['user_id']);
				
				$email_verified = get_user_meta(($user_id), "ussync_email_verified", TRUE);
				
				if( $email_verified !== 'true' ){
					
					$user = get_user_by("id", $user_id);
					
					$scret_code = md5( $user->user_email . time() );
					
					update_user_meta($user_id, "ussync_email_verifiedcode", $scret_code);
					
					$this->codeMailSender($user->user_email);
					
					echo '<div class="updated fade"><p>Email sent to '.$user->user_email.'</p></b></div>';						
				}
			}
		}
	}	
}

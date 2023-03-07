<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Login {
	
	var $parent;
	
	var $reg_tok;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		add_filter('login_url', array($this, 'filter_login_url'), 10, 3 );
	
		add_filter('register_url', array($this, 'set_register_url'), 10, 3 );
		
		add_filter('register_form', array($this, 'add_form_validation'), 10, 3 );
		
		add_action('template_redirect', array( $this, 'enqueue_login_scripts' ));

		add_filter('body_class', function( $classes ) {
			
			return array_merge( $classes, array( 'login', 'login-action-login', 'login-action-login', 'wp-core-ui' ) );
		});		
		
		add_filter('login_redirect', array($this, 'set_login_redirect_url'), 10, 3 );		
		
		add_shortcode('ltple-client-login', array($this , 'get_login_shortcode' ) );

		add_action('register_post',       array( $this, 'check_honeypot' ), 0  );
		add_action('login_form_register', array( $this, 'check_honeypot' ), 0  );

		add_action('register_post', array($this,'check_email_regitration'), 10, 3 );				
		
		add_filter('registration_errors', array($this, 'handle_custom_registration'), 9999, 3 );
		
		add_filter('ltple_current_user', function($user){
			
			if( !is_admin() && empty($user->ID) ){
				
				if( !empty($_COOKIE['reg_tok']) ){
					
					$this->reg_tok = wp_kses_normalize_entities($_COOKIE['reg_tok']);
				}
				elseif( !empty($_REQUEST['reg_tok']) ){
					
					$this->reg_tok = wp_kses_normalize_entities($_REQUEST['reg_tok']);
				}
				else{
					
					$this->reg_tok = substr( wp_hash( 'reg_token_' . time(), 'nonce' ), -12, 10 );
					
					if( !headers_sent() ){
					
						setcookie('reg_tok', $this->reg_tok, time()+3600, '/');
					}
				}
			}
			
			return $user;
		});

	}

	public function get_form(){
		
		$form = '';
		
		$form .= '<style>';
		
			$form .= file_get_contents( $this->parent->assets_dir . '/css/login.css' );
		
		$form .= '</style>';
		
		$form .= do_shortcode('[ltple-client-login]');
		
		return $form;
	}
	
	public function check_honeypot() {

		if ( isset( $_POST['reg_hp_name'] ) && !empty( $_POST['reg_hp_name'] ) )
			wp_die( __( 'You filled out a form field that was created to stop spammers. Please go back and try again or contact the site administrator if you feel this was in error.', 'registration-honeypot' ) );
	}

	public function print_honeypot_scripts() { 
		?>
			<script type="text/javascript">jQuery( '#reg_hp_name' ).val( '' );</script>
		<?php 
	}
	
	public function is_valid_registration($email){
		
		if( !empty($_POST['reg_email_nonce']) && !empty($this->reg_tok) ){
			
			if( wp_verify_nonce($_POST['reg_email_nonce'],'reg_email_'.$this->reg_tok) ){

				return true;
			}
		}
		
		return false;
	}

	public function check_email_regitration( $user_login, $user_email, $errors ) {
		
		if( empty($errors->errors['bad_registration']) ){
		
			if( !$this->is_valid_registration($user_email) ) {
				
				$errors->add( 'bad_registration', '<strong>ERROR</strong>: This registration request is incomplete.' );
			}
		}
	}
	
	public function handle_custom_registration( $errors = NULL, $sanitized_user_login = NULL, $user_email = NULL ){
		
		if( !empty($errors) && !empty($this->reg_tok) ){

			$success = NULL;
			
			// check registration
			
			if( !empty($errors->errors['empty_username']) ) unset($errors->errors['empty_username']);
			
			if( empty($errors->errors['bad_registration']) ){
			
				if( !$this->is_valid_registration($user_email) ) {
					
					$errors->add( 'bad_registration', '<strong>ERROR</strong>: This registration request is incomplete.' );
				}
				else{
					
					// check email
					
					if( empty( $errors->errors['bad_email_domain'] ) ){
				
						if( !empty( $errors->errors['email_exists'] ) ){

							if( $user = get_user_by( 'email', $user_email ) ){
								
								if( !empty($user->data) ){
									
									$user = $user->data;
								
									// check if email imported

									$user->last_seen = intval( get_user_meta( $user->ID, $this->parent->_base . '_last_seen',true) );
									
									if( $user->last_seen === 0 ){

										// send new user notification
										
										wp_new_user_notification( $user->ID, NULL, 'user' );
										
										// output success message
										
										$success = 'A confirmation email has been sent to <b>'.$user_email.'</b>';
									}
								}
							}
						}
						elseif( empty($errors->errors) ){
							
							// add new user
							
							if( $user = $this->parent->email->insert_user($user_email) ){
								
								// send new user notification
										
								wp_new_user_notification( $user['id'], NULL, 'user' );					
								
								// output success message
								
								$success = 'A confirmation email has been sent to <b>'.$user_email.'</b>';
							}
						}
					}
				}
			}					
			
			// store message in session 
			
			$message = array();
			
			if( !empty($success) ){
				
				$message['success'] = $success;
			}
			else{
				
				$message['errors'] = $errors->errors;
			}
			
			set_transient('reg_email_' . $this->reg_tok , $message, 60 * 60 );
		}
		
		// redirect to login page
		
		$login_url = add_query_arg( array(
		
			'redirect_to' 	=> ( isset($_GET['redirect_to']) ? sanitize_url($_GET['redirect_to']) : ''),
			'action' 		=> 'register',
			
		), $this->parent->urls->login );			
		
		wp_redirect($login_url);
		exit;
	}
	
	public function filter_login_url( $login_url, $redirect, $force_reauth ) {
		
		$login_url = home_url( '/login/' );
		
		if( !empty($redirect) ){
			
			$login_url = add_query_arg( 'redirect_to', urlencode(urldecode($redirect)), $login_url );
		}
		
		return $login_url;
	}
	
	public function get_register_url( $register_url ) {
		
		$register_url = add_query_arg( array(

			'action' => 'register',
			
		), $register_url );		
		
		if( $this->parent->inWidget ){
			
			$register_url = add_query_arg( array(
			
				'output' 		=> 'widget',
				'redirect_to' 	=> $this->parent->urls->current,
				
			), $register_url );
		}
		elseif( !empty($_GET['redirect_to']) ){
		
			$register_url = add_query_arg( array(
			
				'redirect_to' 	=> urlencode( $_GET['redirect_to'] ),
				
			), $register_url );
		}
		
		if( !empty($_GET['loe']) ){

			$register_url = add_query_arg( array(

				'loe' 	=> $_GET['loe'],
				
			), $register_url );
		}	

		return $register_url;
	}

	public function set_register_url( $register_url ) {

		return $register_url = $this->get_register_url($register_url);
	}
	
	public function set_login_redirect_url( $redirect_to, $request, $user ) {
		
		if( $redirect_to == admin_url() ){
			
			$redirect_to = isset( $this->parent->urls->dashboard ) ? $this->parent->urls->dashboard : $this->parent->urls->home;
		}
		
		return $redirect_to;
	}
	
	public function get_login_shortcode(){

		ob_start();
		
		include($this->parent->views . '/login.php');
		
		return ob_get_clean();
	}
	
	public function add_form_validation(){
		
		echo'<input type="hidden" name="reg_email_nonce" id="reg_email_nonce" value="' . wp_create_nonce('reg_email_'.$this->reg_tok) . '">';
		echo'<input type="hidden" name="reg_tok" id="reg_tok" value="' . $this->reg_tok . '">';
		
		wp_enqueue_script( 'jquery' );
		add_action( 'login_footer', array( $this, 'print_honeypot_scripts' ), 25 ); 
		
		?>

		<p class="reg_hp_name_field" style="display:none;">
			<label for="reg_hp_name">Only fill in if you are not human</label><br />
			<input type="text" name="reg_hp_name" id="reg_hp_name" class="input" value="" size="25" autocomplete="off" /></label>
		</p>
		
		<?php
	}
	
	public function enqueue_login_scripts(){
		
		if( is_page() && get_queried_object()->post_name == 'login' ){
			
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		}
		elseif( 1==2 && is_front_page() && $this->parent->user->loggedin  ){
			
			wp_redirect($this->parent->urls->dashboard);
			exit;
		}
	}
	
	/**
	 * Load login CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		wp_register_style( $this->parent->_token . '-login', esc_url( $this->parent->assets_url ) . 'css/login.css', array(), $this->parent->_version );
		wp_enqueue_style( $this->parent->_token . '-login' );
	} // End enqueue_styles ()

	/**
	 * Load login Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_register_script( $this->parent->_token . '-login', esc_url( $this->parent->assets_url ) . 'js/login.js', array( 'jquery' ), $this->parent->_version );
		wp_enqueue_script( $this->parent->_token . '-login' );
		
	} // End enqueue_scripts ()	
} 
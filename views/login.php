<?php 
	
	$ltple = LTPLE_Client::instance();
	
	// get pre filled user email

	$user_email = ( !empty($_GET['loe']) ? $this->parent->ltple_decrypt_uri($_GET['loe'])  : '' );

	// output form
	
	echo'<div id="login-wrap">';

		echo'<p style="width:350px;padding:10px;font-size:20px;margin:10px auto;" class="register">';
		
			if(isset($_REQUEST['action'])&&$_REQUEST['action']=='register'){
				
				echo'New Registration';
			}	
			else{
				
				echo'Login Now';
			}
			
		echo'</p>';
	
		// output message
		
		$show_form = true;
	
		if( !empty( $_SESSION['errors']->errors ) || !empty($_SESSION['success']) ){
			
			echo'<div id="login_errors" style="width:350px;margin:10px auto;">';
				
				if( !empty( $_SESSION['errors']->errors ) ){
				
					echo'<div class="alert alert-warning">';
						
						foreach( $_SESSION['errors']->errors as $error ){
							
							echo reset($error) . '<br/>';
						}
					
					echo'</div>';
				}
				
				if( !empty( $_SESSION['success'] ) ){
				
					echo'<div class="alert alert-success">';

						echo $_SESSION['success'];

					echo'</div>';
					
					$show_form = false;
				}
			
			echo'</div>';

			// empty messages
			
			$_SESSION['success'] 	= '';
			$_SESSION['errors'] 	= '';
		}
		
		if($show_form){
		
			echo'<div id="login">';

				// login form
				
				if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'register' ){
					
					echo'<form name="registerform" id="loginform" action="' . wp_registration_url() . '" method="post" novalidate="novalidate">';
						
						echo'<p>';
							
							echo'<label for="user_email">Email<br>';
							
								if(empty($user_email)){
									
									echo'<input type="email" name="user_email" id="user_email" class="input" value="" size="25">';
								}
								else{
									
									echo'<input type="email" class="input" value="'.$user_email.'" size="25" disabled>';
									
									echo'<input type="hidden" name="user_email" id="user_email" value="'.$user_email.'">';
								}
								
								do_action('register_form');
							
							echo'</label>';
						
						echo'</p>';
						
						do_action('login_footer');
							
						echo'<p id="reg_passmail">Registration confirmation will be emailed to you.</p>';
						
						echo'<br class="clear">';
						
						echo'<input type="hidden" name="redirect_to" value="">';
						
						echo'<p class="submit" style="margin-bottom:50px;"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Register"></p>';
					
					echo'</form>';			
				}
				else{
					
					echo'<button style="border-radius:5px;width:100%;" class="btn-lg btn-primary" type="button" data-toggle="collapse" data-target="#emailLogin" aria-expanded="false" aria-controls="emailLogin">';
						
						echo'Email Login';
					
					echo'</button>';				
					
					echo'<div id="emailLogin" class="collapse' . (!empty($user_email) ? ' in' : '' ).'" style="margin-top: 25px;">';
						
						if( $ltple->inWidget ){
						
							$redirect_to 	= $ltple->urls->current;
							
							$register_url	= add_query_arg( array(
								
								'output' => 'widget',
								
							),$this->get_register_url( wp_login_url() ));
						
							$password_url	= add_query_arg( array(
								
								'output' => 'widget',
								
							),wp_lostpassword_url());
						
						}
						else{
							
							if( !empty($_GET['redirect_to']) ){
								
								$redirect_to = $_GET['redirect_to'];
							}
							elseif( strpos($this->parent->urls->current,$this->parent->urls->login) === false ){
								
								$redirect_to = $this->parent->urls->current;
							}
							else{
								
								$redirect_to = admin_url();
							}
							
							$register_url = $this->get_register_url( wp_login_url() );
						
							$password_url = wp_lostpassword_url();
						}
						
						wp_login_form( array(
						
							'redirect' 			=> $redirect_to, 
							'form_id' 			=> 'loginform',
							'label_username' 	=> __( 'Email' ),
							'value_username' 	=> $user_email,
							'label_password' 	=> __( 'Password' ),
							'label_remember' 	=> __( 'Remember Me' ),
							'label_log_in' 		=> __( 'Log In' ),
							'value_remember' 	=> true,
							'remember' 			=> true
						));
						
						echo'<div style="width:100%;text-align:center;display:block;">';
							
							echo'<a href="' . $register_url . '">Register</a>';					
							echo' | ';
							echo'<a href="' . $password_url . '">Lost Password</a>';
						
						echo'</div>';
						
					echo'</div>';
				}

				if( empty($user_email) && empty($ltple->profile->id) ){
				
					do_action('ltple_alternative_login');
				}
				
			echo'</div>';
		}
		
	echo'</div>';
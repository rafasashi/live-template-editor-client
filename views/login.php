<?php 

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
				}
			
			echo'</div>';

			// empty messages
			
			$_SESSION['success'] 	= '';
			$_SESSION['errors'] 	= '';
		}
	
		echo'<div id="login">';

			if ( 1==1 || ! is_user_logged_in() ) {
				
				// login form
				
				if(isset($_REQUEST['action'])&&$_REQUEST['action']=='register'){
					
					echo'<form name="registerform" id="loginform" action="' . wp_registration_url() . '" method="post" novalidate="novalidate">';
						
						/*
						echo'<p>';
							
							echo'<label for="user_login">Username<br>';
							
							echo'<input type="text" name="user_login" id="user_login" class="input" value="" size="20"></label>';
						
						echo'</p>';
						*/
						
						echo'<p>';
							
							echo'<label for="user_email">Email<br>';
							
							echo'<input type="email" name="user_email" id="user_email" class="input" value="" size="25"></label>';
						
						echo'</p>';
							
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
					
					echo'<div id="emailLogin" class="collapse" style="margin-top: 25px;">';
												
						wp_login_form( array(
						
							'redirect' => admin_url(), 
							'form_id' => 'loginform',
							'label_username' => __( 'Email' ),
							'label_password' => __( 'Password' ),
							'label_remember' => __( 'Remember Me' ),
							'label_log_in' => __( 'Log In' ),
							'value_remember' => true,
							'remember' => true
						));
						
						
						echo'<div style="width:100%;text-align:center;margin-bottom:50px;display:block;">';
						
							$register_url = add_query_arg( array(
		
								'redirect_to' 	=> ( isset($_GET['redirect_to']) ? $_GET['redirect_to'] : ''),
								'action' 		=> 'register',
								
							), wp_login_url() );
						
							echo'<a href="' . $register_url . '">Register</a>';					
							echo' | ';
							echo'<a href="' . wp_lostpassword_url() . '">Lost Password</a>';
						
						echo'</div>';
						
					echo'</div>';
				}
					
				echo'<a href="' . $this->twitterUrl . '" style="border-radius:5px;width:100%;display: block;text-align: center;margin-top: 10px;" class="btn-lg btn-info">';
					
					echo'Twitter Login';
				
				echo'</a>';
			} 
			else {
			
				wp_loginout( home_url() );
				
				echo " | ";
				
				wp_register('', '');
			}
			
		echo'</div>';
		
	echo'</div>';
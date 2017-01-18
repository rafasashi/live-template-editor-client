<?php

if ( ! defined( 'ABSPATH' ) ) exit;

use Abraham\TwitterOAuth\TwitterOAuth;

class LTPLE_Client_App_Twitter {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent ) {
		
		$this->parent 	= $parent;
		
		// get app term

		$this->term = get_term_by('slug',$app_slug,'app-type');
		
		// get app parameters
		
		$parameters = get_option('parameters_'.$app_slug);
		
		if( isset($parameters['key']) ){
			
			$twt_consumer_key 		= array_search('twt_consumer_key', $parameters['key']);
			$twt_consumer_secret 	= array_search('twt_consumer_secret', $parameters['key']);
			$twt_oauth_callback 	= $this->parent->urls->editor;

			if( !empty($parameters['value'][$twt_consumer_key]) && !empty($parameters['value'][$twt_consumer_secret]) ){
			
				define('CONSUMER_KEY', 		$parameters['value'][$twt_consumer_key]);
				define('CONSUMER_SECRET', 	$parameters['value'][$twt_consumer_secret]);
				define('OAUTH_CALLBACK', 	$twt_oauth_callback);

				// get current action
				
				if(!empty($_REQUEST['action'])){
					
					$this->action = $_REQUEST['action'];
				}
				elseif(!empty($_SESSION['action'])){
					
					$this->action = $_SESSION['action'];
				}
				
				$methodName = 'app'.ucfirst($this->action);

				if(method_exists($this,$methodName)){
					
					$this->$methodName();
				}
			}
		}
	}
	
	public function do_tweet_shortcodes( $str, $screen_name ){
		
		$shortcodes 	= [];
		$shortcodes[] 	= '*|TWT_NAME|*';
		$shortcodes[] 	= '*|DATE:d/m/y|*'; // date
		$shortcodes[] 	= '*|DATE:y|*'; 	// year
		
		$data 			= [];
		$data[]			= $screen_name;
		$data[]			= date( 'd/m/y', time());
		$data[]			= date( 'y'	 , time());
		
		$str = str_replace($shortcodes,$data,$str);
		
		return $str;
	}
	
	public function appImportImg(){
		
		if(!empty($_REQUEST['id'])){
		
			if( $this->app = LTPLE_Client_Apps::getAppData( $_REQUEST['id'], $this->parent->user->ID ) ){

				$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->app->oauth_token, $this->app->oauth_token_secret);
				
				$items = $this->connection->get('statuses/user_timeline', array( 
				
					'screen_name' 		=> $this->app->screen_name, 
					'count' 			=> 200,
					'trim_user'			=> true,
					'exclude_replies'	=> true,
					'include_entities' 	=> true,
					'include_rts' 		=> false
				));

				$urls = [];
				
				if(!empty($items)){
					
					foreach($items as $item){
						
						if(isset($item->entities->media)){
							
							foreach($item->entities->media as $media){
								
								if($media->type == 'photo'){
									
									$img_title	= basename($media->media_url);
									$img_url	= $media->media_url;
									
									if(!get_page_by_title( $img_title, OBJECT, 'user-image' )){
										
										if($image_id = wp_insert_post(array(
									
											'post_author' 	=> $this->parent->user->ID,
											'post_title' 	=> $img_title,
											'post_content' 	=> $img_url,
											'post_type' 	=> 'user-image',
											'post_status' 	=> 'publish'
										))){
											
											wp_set_object_terms( $image_id, $this->term->term_id, 'app-type' );
										}
									}
								}						
							}
						}
					}
				}
			}
		}
	}
	
	public function appConnect(){
		
		if( isset($_REQUEST['action']) ){
			
			$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
			
			if(!isset($_SESSION['oauth_token'])){
				
				$this->request_token = $this->connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));	

				$_SESSION['app'] 				= 'twitter';
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');
				$_SESSION['oauth_token'] 		= $this->request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $this->request_token['oauth_token_secret'];			
			}
			
			if(isset($_SESSION['oauth_token'])){
			
				$this->oauth_url = $this->connection->url('oauth/authenticate', array('oauth_token' => $_SESSION['oauth_token']));
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting twitter oauth...';
				exit;		
			}
		}
		elseif( isset($_SESSION['action']) ){

			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				$this->request_token = [];
				$this->request_token['oauth_token'] 		= $_SESSION['oauth_token'];
				$this->request_token['oauth_token_secret'] 	= $_SESSION['oauth_token_secret'];

				if(isset($_REQUEST['oauth_token']) && $this->request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
					
					//flush session
					session_destroy();
					
					// store failure message

					$_SESSION['message'] = '<div class="alert alert-danger">';
						
						$_SESSION['message'] .= 'Twitter connection failed...';
							
					$_SESSION['message'] .= '</div>';
				}
				elseif(isset($_REQUEST['oauth_verifier'])){
					
					$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->request_token['oauth_token'], $this->request_token['oauth_token_secret']);
					
					//get the long lived access_token that authorized to act as the user
					
					$this->access_token = $this->connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
					
					//flush session
					session_destroy();

					//store access_token in session					
					
					$_SESSION['access_token'] = $this->access_token;
					
					// store access_token in database		
					
					$app_title = wp_strip_all_tags( 'twitter - ' . $_SESSION['access_token']['screen_name'] );
					
					$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
					
					if( empty($app_item) ){
						
						// create app item
						
						$app_id = wp_insert_post(array(
						
							'post_title'   	 	=> $app_title,
							'post_status'   	=> 'publish',
							'post_type'  	 	=> 'user-app',
							'post_author'   	=> $this->parent->user->ID
						));
						
						wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );
					}
					else{

						$app_id = $app_item->ID;
					}
						
					// update app item
						
					update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));

					// do welcome actions
							
					$this->do_welcome_actions();	

					if(!empty($_SESSION['ref'])){
						
						// handle redirection
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting twitter callback...';
						exit;	
					}
					else{
						
						// store success message

						$_SESSION['message'] = '<div class="alert alert-success">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected a Twitter account!';
								
						$_SESSION['message'] .= '</div>';						
					}

				}
				else{
					
					//flush session
					session_destroy();					
				}
			}

			//var_dump($this->parent->user->ID);exit;
		}
	}
	
	public function appLogin(){
		
		if( isset($_REQUEST['action']) ){
			
			$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
			
			if(!isset($_SESSION['oauth_token'])){
				
				$this->request_token = $this->connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));	

				$_SESSION['app'] 				= 'twitter';
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');
				$_SESSION['oauth_token'] 		= $this->request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $this->request_token['oauth_token_secret'];			
			}
			
			if(isset($_SESSION['oauth_token'])){
			
				$this->oauth_url = $this->connection->url('oauth/authenticate', array(
				
					'oauth_token' => $_SESSION['oauth_token'],
					'force_login' => 'false'
				));
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting twitter oauth...';
				exit;		
			}
		}
		elseif( isset($_SESSION['action']) ){

			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				$this->request_token = [];
				$this->request_token['oauth_token'] 		= $_SESSION['oauth_token'];
				$this->request_token['oauth_token_secret'] 	= $_SESSION['oauth_token_secret'];

				if(isset($_REQUEST['oauth_token']) && $this->request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
					
					//flush session
					session_destroy();
					
					// store failure message

					echo 'Twitter login failed...';
					exit;
				}
				elseif(isset($_REQUEST['oauth_verifier'])){
					
					$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->request_token['oauth_token'], $this->request_token['oauth_token_secret']);
					
					//get the long lived access_token that authorized to act as the user
					
					$this->access_token = $this->connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
					
					//flush session
					session_destroy();

					//store access_token in session					
					
					$_SESSION['access_token'] = $this->access_token;
					
					// get associated user id
					
					$app_title = wp_strip_all_tags( 'twitter - ' . $_SESSION['access_token']['screen_name'] );
					
					$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );

					if(empty($app_item->post_author)){
						
						// start user connection
						
						$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
						
						// get account info 

						$account =	$this->connection->get('account/verify_credentials', array(

							'include_entities' 	=> 'false',
							'skip_status' 		=> 'true',
							'include_email' 	=> 'true'
						));

						if(!empty($account->email)){
							
							$this->userId = email_exists($account->email);

							if(!is_numeric($this->userId)){
								
								// get unique user name
								
								$user_name = $account->screen_name;
								
								if( username_exists( $user_name ) ){
									
									$i=2;
									
									while(username_exists( $user_name.$i )){
										
										++$i;
									}
									
									$user_name = $user_name.$i;
								}
								
								$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
								
								$this->userId = wp_create_user( $user_name, $random_password, $account->email );						
							}
							
							if(is_numeric($this->userId)){
								
								$this->userId = intval($this->userId);
								
								// create app item
								
								$app_id = wp_insert_post(array(
								
									'post_title'   	 	=> $app_title,
									'post_status'   	=> 'publish',
									'post_type'  	 	=> 'user-app',
									'post_author'   	=> $this->userId
								));
								
								wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );

								// update app item
									
								update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));

								// do welcome actions
								
								$this->do_welcome_actions();								
							}
							else{
								
								echo'<pre>';
								var_dump($this->userId);
								
								//echo'Error creating a new Twitter user...';
								exit;
							}							
						}
						else{
							
							echo 'Error getting the email associated to this Twitter account...';
							exit;
						}
					}
					else{
						
						$this->userId = intval( $app_item->post_author );
					}
					
					if(is_numeric($this->userId)){
					
						// set current user
						
						$this->parent->user = wp_set_current_user( $this->userId );
						
						// set auth cookie
						
						wp_set_auth_cookie($this->userId, true);
					}
					else{
						
						echo'Error logging current Twitter user...';
						exit;
					}
					
					// handle redirection

					if(!empty($_SESSION['ref'])){
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting twitter callback...';
						exit;	
					}
				}
				else{
					
					//flush session
					session_destroy();					
				}
			}
		}
	}
	
	public function do_welcome_actions(){
							
		// get main account

		if($this->main_token = LTPLE_Client_Apps::getAppData( get_option( $this->parent->_base . 'twt_main_account' ))){
			
			// new account follow main account
			
			$this->connection->post('friendships/create', array(
			
				'screen_name' => $this->main_token->screen_name
			));
			
			// start main account connection
			
			$this->main_connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->main_token->oauth_token, $this->main_token->oauth_token_secret);
			
			// main account follow new account
			
			$this->main_connection->post('friendships/create', array(
			
				'screen_name' => $this->access_token['screen_name']
			));						
			
			// welcome tweet on behalf of main account

			$tweet_content = get_option( $this->parent->_base . 'twt_welcome_tweet' );
			
			if(!empty($tweet_content )){
				
				$this->main_connection->post('statuses/update', array(
				
					'status' => $this->do_tweet_shortcodes($tweet_content,$this->access_token['screen_name'])
				));
			}
			
			// welcome DM on behalf of main account
			
			$dm_content = get_option( $this->parent->_base . 'twt_welcome_dm' );
			
			if(!empty($dm_content )){
				
				$this->main_connection->post('direct_messages/new', array(
				
					'screen_name' 	=> $this->access_token['screen_name'],
					'text' 			=> $this->do_tweet_shortcodes($dm_content,$this->access_token['screen_name'])
				));
			}
		}
	}
} 
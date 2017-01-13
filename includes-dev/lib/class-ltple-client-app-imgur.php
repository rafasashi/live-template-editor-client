<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_App_Imgur {
	
	var $parent;
	var $consumer_key;
	var $consumer_secret;
	var $oauth_callback;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent ) {
		
		$this->parent 	= $parent;

		// get app term

		$this->term = get_term_by('slug',$app_slug,'app-type');
		
		// get app credentials

		define('CONSUMER_KEY', 		get_option( $this->parent->_base . 'imgur_consumer_key' ));
		define('CONSUMER_SECRET', 	get_option( $this->parent->_base . 'imgur_consumer_secret' ));
		
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

	public function appImportImg(){
		
		if(!empty($_REQUEST['id'])){
		
			if( $this->app = LTPLE_Client_Apps::getAppData( $_REQUEST['id'], $this->parent->user->ID, true ) ){
				
				$client = new \Imgur\Client();
				$client->setOption('client_id', CONSUMER_KEY);
				$client->setOption('client_secret', CONSUMER_SECRET);
				
				$client->setAccessToken($this->app);		

				if($client->checkAccessTokenExpired()) {
					
					$client->refreshToken();
				}

				$images = $client->api('account')->images();

				$urls = [];
				
				if(!empty($images)){
					
					foreach($images as $image){
						
						if(!empty($image['link'])){
							
							$img_title	= basename($image['link']);
							$img_url	= $image['link'];
							
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
	
	public function appConnect(){

		$client = new \Imgur\Client();
		$client->setOption('client_id', CONSUMER_KEY);
		$client->setOption('client_secret', CONSUMER_SECRET);

		if( isset($_REQUEST['action']) ){
			
			if(!isset($_SESSION['token'])){

				$_SESSION['app'] 				= 'imgur';
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');

				$this->oauth_url = $client->getAuthenticationUrl();
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting imgur oauth...';
				exit;
			}			
		}
		elseif( isset($_SESSION['action']) ){
			
			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				if(isset($_REQUEST['code'])){
					
					//get access_token
					
					$client->requestAccessToken($_REQUEST['code']);
					
					$this->access_token = $client->getAccessToken();
					
					//flush session
					session_destroy();					
					
					//store access_token in session					
					$_SESSION['access_token'] = $this->access_token;
					
					if(!empty($this->access_token['account_username'])){

						// store access_token in database		
						
						$app_title = wp_strip_all_tags( 'imgur - ' . $this->access_token['account_username'] );
						
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
					}
					
					if(!empty($_SESSION['ref'])){
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting imgur callback...';
						exit;	
					}
					else{
						
						// store success message

						$_SESSION['message'] = '<div class="alert alert-success">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected a Imgur account!';
								
						$_SESSION['message'] .= '</div>';						
					}
				}
				else{
					
					//flush session
					session_destroy();					
				}
			}
		}
	}
} 
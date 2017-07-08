<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_App_Tumblr {
	
	var $parent;
	var $apps;

	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent, $apps ) {

		$this->parent 		= $parent;
		$this->parent->apps = $apps;

		// get app term

		$this->term = get_term_by('slug',$app_slug,'app-type');
		
		// get app parameters
		
		$parameters = get_option('parameters_'.$app_slug);
		
		if( isset($parameters['key']) ){
			
			$tblr_consumer_key 		= array_search('tblr_consumer_key', $parameters['key']);
			$tblr_consumer_secret 	= array_search('tblr_consumer_secret', $parameters['key']);
			$tblr_oauth_callback 	= $this->parent->urls->editor;

			if( !empty($parameters['value'][$tblr_consumer_key]) && !empty($parameters['value'][$tblr_consumer_secret]) ){
			
				define('CONSUMER_KEY', 		$parameters['value'][$tblr_consumer_key]);
				define('CONSUMER_SECRET', 	$parameters['value'][$tblr_consumer_secret]);
				//define('OAUTH_CALLBACK', 	$tblr_oauth_callback);

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
			else{
				
				$_SESSION['message'] = '<div class="alert alert-danger">';
					
					$_SESSION['message'] .= 'Sorry, tumblr is not available on this platform yet, please contact the dev team...';
						
				$_SESSION['message'] .= '</div>';				
			}
		}
	}

	public function appImportImg(){
		
		if(!empty($_REQUEST['id'])){
		
			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID ) ){
				
				$client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $this->app->oauth_token, $this->app->oauth_token_secret);
										
				$blog = $client->getBlogPosts($this->app->user_name);
				
				$urls = [];
				
				if(!empty($blog->posts)){
					
					foreach($blog->posts as $item){
						
						if(!empty($item->photos)){
							
							foreach($item->photos as $photo){
								
								$img_title	= basename($photo->original_size->url);
								$img_url	= $photo->original_size->url;
								
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
	
	public function appConnect(){
		
		$client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET);
		
		if( isset($_REQUEST['action']) ){
			
			$this->connection = $client->getRequestHandler();
			$this->connection->setBaseUrl('https://www.tumblr.com/');

			if(!isset($_SESSION['oauth_token'])){
				
				// start the old gal up
				$resp = $this->connection->request('POST', 'oauth/request_token', array());
				
				// get the oauth_token
				parse_str($resp->body, $this->request_token);

				$_SESSION['app'] 				= 'tumblr';
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? $this->parent->request->proto . urldecode($_REQUEST['ref']) : '');
				$_SESSION['oauth_token'] 		= $this->request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $this->request_token['oauth_token_secret'];			
			}
			
			if(isset($_SESSION['oauth_token'])){
			
				$this->oauth_url = 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $_SESSION['oauth_token'];
				
				wp_redirect($this->oauth_url);
				echo 'Redirecting tumblr oauth...';
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
						
						$_SESSION['message'] .= 'Tumblr connection failed...';
							
					$_SESSION['message'] .= '</div>';
				}
				elseif(isset($_REQUEST['oauth_verifier'])){
					
					// set temporary oauth_token
					
					$client->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
					
					// get new Request Handler
					
					$this->connection = $client->getRequestHandler();
					$this->connection->setBaseUrl('https://www.tumblr.com/');			
					
					//get the long lived access_token that authorized to act as the user
					
					$resp = $this->connection->request('POST', 'oauth/access_token', array('oauth_verifier' => $_REQUEST['oauth_verifier']));
					parse_str($resp->body, $this->access_token);

					//flush session
					session_destroy();

					//store access_token in session					
					
					$_SESSION['access_token'] = $this->access_token;
					
					// set access oauth_token
					$client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
					
					// get user info
					
					$info = $client->getUserInfo();

					if(!empty($info->user->blogs)){
						
						// append user name
						
						$this->access_token['user_name'] = $info->user->name;
						
						// get main account token
						
						//$this->main_token = $this->parent->apps->getAppData( get_option( $this->parent->_base . 'tblr_main_account' ));
						
						foreach($info->user->blogs as $blog){

							if( $blog->admin === true ){
								
								// store access_token in database		
								
								$app_title = wp_strip_all_tags( 'tumblr - ' . $blog->name );
								
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
									
									// hook connected app
									
									do_action( 'ltple_thumblr_account_connected');
									
									$this->parent->apps->newAppConnected();
								}
								else{
									
									$app_id = $app_item->ID;
								}
									
								// update app item
									
								update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));							
							}							
						}
					}
					
					if(!empty($_SESSION['ref'])){
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting tumblr callback...';
						exit;	
					}
					else{
						
						// store success message

						$_SESSION['message'] = '<div class="alert alert-success">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected a Tumblr account!';
								
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
} 
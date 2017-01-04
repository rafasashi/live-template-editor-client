<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_App_Wordpress {
	
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

		define('CONSUMER_KEY', 		get_option( $this->parent->settings->base . 'wpcom_consumer_key' ));
		define('CONSUMER_SECRET', 	get_option( $this->parent->settings->base . 'wpcom_consumer_secret' ));
		define('OAUTH_CALLBACK', 	get_option( $this->parent->settings->base . 'wpcom_oauth_callback' ));

		include( $this->parent->vendor . '/wp-rest-php-lib/src/wpcom.php' );
		
		//Set client
		$this->client = new WPCOM_REST_Client;
		$this->client->set_auth_key( CONSUMER_KEY, CONSUMER_SECRET );

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
		
			if( $this->app = LTPLE_Client_Apps::getAppData( $_REQUEST['id'], $this->parent->user->ID, false ) ){
				
				$this->client->set_auth_token($this->app->access_token);

				$this->blog=str_replace('http://','',$this->app->blog_url);				
				
				// get site info

				$site = WPCOM_REST_Object_Site::initWithId( $this->blog, $this->client );
				
				//$site_details = $site->get();
				
				$request = $site->get_posts(array(
				
					'fields' => 'ID,featured_image',
					'number' => 100,
				));
				
				if( !empty($request->posts) ){
					
					foreach($request->posts as $post){
						
						if(!empty($post->featured_image)){
							
							$img_url	= $post->featured_image;
							$img_title	= basename($img_url);
							
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
				
				//echo '<pre>';
				//var_dump($posts);
				//exit;
			}
		}
	}
	
	public function appUploadImg( $app_id, $image_url){

		if( $this->app = LTPLE_Client_Apps::getAppData( $app_id, $this->parent->user->ID, false ) ){
			
			$this->client->set_auth_token($this->app->access_token);

			$this->blog=str_replace('http://','',$this->app->blog_url);				
				
			// post new image
			
			$post_data=[];
			$post_data['title']			= 'Image';
			$post_data['content']		= 'image';
			$post_data['media_urls']	= $image_url;
			$post_data['i_like']		= false;
			$post_data['is_reblogged']	= false;
			$post_data['publicize']		= false;
			$post_data['status']		= 'auto-draft';
			$post_data['format']		= 'image';

			$post = WPCOM_REST_Object_Post::initAsNew($post_data, $this->blog, $this->client);
			
			$post_data = $post->get();
			
			// TODO store media url
			if( !empty($post_data->attachments) ){
				
				foreach($post_data->attachments as $image){
					
					$img_url	= $image->URL;
					$img_title	= $_SESSION['file'];
					
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
						else{
							
							return false;
						}
					}
				}
			}
			
			// move to trash
			
			$post->delete();
			
			// get post from trash
			
			$post_data = $post->get();
			
			if(!empty($post_data->URL)&&strpos($post_data->URL,'__trashed')!==false){
				
				// delete permanently
				
				$post->delete();
			}
			
			return true;
		}

		return false;
	}
	
	public function appConnect(){
		
		if( isset($_REQUEST['action']) ){
			
			if(!isset($_SESSION['token'])){

				$_SESSION['app'] 				= 'wordpress';
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');
				
				$this->oauth_url = $this->client->get_blog_auth_url( '', OAUTH_CALLBACK, [] );

				wp_redirect($this->oauth_url);
				echo 'Redirecting wordpress oauth...';
				exit;	
			}			
		}
		elseif( isset($_SESSION['action']) ){
			
			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				if(isset($_REQUEST['code'])){
					
					//get access_token
					
					try {
						
						$this->access_token = $this->client->request_access_token( $_REQUEST['code'], OAUTH_CALLBACK );
					} 
					catch ( WP_REST_Exception $e ) {

						var_dump($e);
						exit;
					}
					
					//flush session
					session_destroy();					
					
					//store access_token in session					
					$_SESSION['access_token'] = $this->access_token;

					// get blog name	
					
					$blog_name = str_replace(array('http://','https://','.wordpress.com'),'',$this->access_token->blog_url);

					// store access_token in database		
					
					$app_title = wp_strip_all_tags( 'wordpress - ' . $blog_name );
					
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
					
					if(!empty($_SESSION['ref'])){
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting wordpress callback...';
						exit;	
					}
					else{
						
						// store success message

						$_SESSION['message'] = '<div class="alert alert-success">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected a Wordpress account!';
								
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
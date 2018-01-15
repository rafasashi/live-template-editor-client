<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Integrator_Bookmark {
	
	var $parent;
	var $apps;
	var $term;
	var $parameters;
	var $data;
	var $resourceUrl;
	var $message;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent, $apps ) {

		$this->parent 		= $parent;
		$this->parent->apps = $apps;

		// get app term

		$this->term = get_term_by('slug',$app_slug,'app-type');
		
		// get app parameters
		
		$this->parameters = get_option('parameters_'.$app_slug);

		if( isset($this->parameters['key']) ){

			foreach($this->parameters['key'] as $i => $key){
				
				if( $key == 'resource' ){
					
					// get app resource
					
					$this->resourceUrl = $this->parameters['value'][$i];	
				}
			}
			
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
	
	public function appDeleteBookmark(){
	
		if( isset($_GET['id']) ){
			
			$bookmark = get_post($_GET['id']);

			if( !empty($bookmark->post_author) && intval($bookmark->post_author) == $this->parent->user->ID ){
			
				//--------delete bookmark--------
				
				wp_delete_post( $bookmark->ID, true );
				
				$this->message ='<div class="alert alert-success">';

					$this->message .= 'Bookmark url successfully deleted!';

				$this->message .='</div>';
			}
		}
	}
	
	public function appAddBookmark(){

		if(!empty($_REQUEST['id'])){

			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID, true ) ){
							
				// get bookmark url
					
				$bookmark_url = trim(urldecode($this->app['resource']));			
			
				$args 		= [];
				$filename 	= '';

				if( isset($this->parameters['key']) ){

					foreach($this->parameters['key'] as $i => $key){
						
						if(isset($_REQUEST[$key])){
						
							if( $this->parameters['input'][$i] == 'parameter' ){
								
								$args[$key] = trim(esc_html($_REQUEST[$key]));
							}
							elseif($this->parameters['input'][$i] == 'folder'){
								
								$bookmark_url .= '/' . trim(esc_html($_REQUEST[$key])) . '/';
							}
							elseif($this->parameters['input'][$i] == 'filename'){
								
								$filename = '/' . trim(esc_html($_REQUEST[$key]));
							}
						}
					}
				}
				
				$bookmark_url = add_query_arg( $args, $bookmark_url.$filename );
			
				// check bookmark exists

				$ch = curl_init($bookmark_url);
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				
				$result 	= curl_exec($ch);
				$httpcode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
				
				curl_close($ch);
				
				if( $httpcode >= 300 ){
					
					$this->message .= '<div class="alert alert-warning">';
						
						$this->message .= 'Resource not reachable...';
							
					$this->message .= '</div>';						
				}
				else{
				
					// get bookmark title
					
					if( !empty($_POST['bookmarkTitle']) ){
						
						$bookmark_title	= sanitize_text_field($_POST['bookmarkTitle']);
					}
					else{
						
						$bookmark_title	= basename($bookmark_url);
					}
					
					// insert bookmark
					
					if(!get_page_by_title( $bookmark_title, OBJECT, 'user-bookmark' )){
						
						if($bookmark_id = wp_insert_post(array(
					
							'post_author' 	=> $this->parent->user->ID,
							'post_title' 	=> $bookmark_title,
							'post_content' 	=> $bookmark_url,
							'post_type' 	=> 'user-bookmark',
							'post_status' 	=> 'publish'
						))){
							
							wp_set_object_terms( $bookmark_id, $this->term->term_id, 'app-type' );
						}
					}
				}
			}
		}
	}
	
	public function appConnect(){

		$fields = $this->parent->apps->parse_url_fields($this->resourceUrl,'bookmark_' . $this->term->slug . '_');
		
		if( isset($_POST['bookmark_is_admin']) && $_POST['bookmark_is_admin'] == 'on' ){

			$terms = array();
			
			foreach($fields as $k => $field){
				
				if( !empty($_POST[$field['id']]) ){
					
					$value = $_POST[$field['id']];
					
					$terms[$k] 		= $value;
					$this->data[$k] = $value;
				}
				else{
					
					$terms = array();
					
					$this->message .= '<div class="alert alert-warning">';
						
						$this->message .= 'A field is missing...';
							
					$this->message .= '</div>';	
			
					break;
				}
			}
		}
		elseif(!empty($_POST)){
			
			$terms = array();
			
			$this->message .= '<div class="alert alert-warning">';
				
				$this->message .= 'You must be the admin of this resource...';
					
			$this->message .= '</div>';					
		}
		
		$outputForm = true;
			
		if( !empty($terms) ){

			// check is valid resource
			
			$resourceUrl = $this->resourceUrl;
			
			foreach($terms as $k => $v){	
			
				$resourceUrl = str_replace('{'.$k.'}',$v,$resourceUrl);
			}
			
			$ch = curl_init($resourceUrl);
			curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
			curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT,10);
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$output = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if( $httpcode >= 300 ){
				
				$this->message .= '<div class="alert alert-warning">';
					
					$this->message .= 'This resource couldn\'t be found...';
						
				$this->message .= '</div>';						
			}
			else{
				
				$outputForm = false;
				
				$this->data['resource'] = urlencode($resourceUrl);
			
				$app_title = $this->term->slug . ' - ' . implode('_',$terms);	

				$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
				
				if( empty($app_item) ){

					// create app item
					
					$app_id = wp_insert_post(array(
					
						'post_title'   	 	=> $app_title,
						'post_status'   	=> 'publish',
						'post_type'  	 	=> 'user-app',
						'post_author'   	=> $this->parent->user->ID
					));
					
					if(is_numeric($app_id)){
						
						wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );
						
						// hook connected app
						
						do_action( 'ltple_' . str_replace( '-', '_', $this->term->slug ) . '_account_connected');
						
						$this->parent->apps->newAppConnected();
						
						$_SESSION['message'] = '<div class="alert alert-success" style="margin-bottom:0;">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected your ' . $this->term->name . ' account!';
								
						$_SESSION['message'] .= '</div>';
					}
					else{

						$_SESSION['message'] = '<div class="alert alert-warning" style="margin-bottom:0;">';
							
							$_SESSION['message'] .= 'Something went wrong...';
								
						$_SESSION['message'] .= '</div>';						
					}	
				}
				else{

					$app_id = $app_item->ID;
					
					$_SESSION['message'] = '<div class="alert alert-info" style="margin-bottom:0;">';
						
						$_SESSION['message'] .= 'This app is already connected...';
							
					$_SESSION['message'] .= '</div>';
				}

				// update app item
					
				update_post_meta( $app_id, 'appData', json_encode($this->data,JSON_PRETTY_PRINT));
			}				
		}
		
		if( $outputForm ){
			
			// output form
			
			$input = $this->resourceUrl;
			
			foreach($fields as $k => $field){			
				
				$input = str_replace('{'.$k.'}',' '.$this->parent->admin->display_field( $field, false, false ).' ',$input);				
			}

			$this->message .= '<form action="' . $this->parent->urls->current . '" method="post">';
				
				$this->message .= '<div class="col-xs-8">';
				
					$this->message .= '<h2>Add '.ucfirst($this->term->name).' account</h2>';
				
					$this->message .= '<div class="well form-group">';
				
						$this->message .= '<label class="input-group">';
						
							$this->message .= 'Account url';
						
						$this->message .= '</label>';					
				
						$this->message .= $input;
						
						$this->message .= '<div class="row">';
							
							$this->message .= '<div class="col-xs-6 text-left" style="margin-top:10px;">';
								
								$this->message .= $this->parent->admin->display_field( array(
								
									'type'				=> 'checkbox',
									'id'				=> 'bookmark_is_admin',
									'style'				=> 'width:15px;height:15px;float:left;',
									'description'		=> '',
									
						
								), false, false );
								
								$this->message .= 'I am the admin of this resource';
								
							$this->message .= '</div>';
							
							$this->message .= '<div class="col-xs-6 text-right" style="margin-top:10px;">';
							
								$this->message .= '<button class="btn btn-sm btn-primary" type="submit">Connect</button>';
							
							$this->message .= '</div>';
							
						$this->message .= '</div>';
						
					$this->message .= '</div>';
					
				$this->message .= '</div>';
				
			$this->message .= '</form>';				
		}
	}
} 
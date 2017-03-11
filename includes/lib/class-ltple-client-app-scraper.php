<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_App_Scraper {
	
	var $parent;
	var $apps;
	var $term;
	var $parameters;
	var $data;
	var $resourceUrl;
	var $xpath;
	var $attr;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent, $apps ) {

		$this->parent 		= $parent;
		$this->parent->apps = $apps;
		
		$this->parent->dialog->scraper = '';

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
				elseif( $key == 'xpath' ){
					
					// get app resource
					
					$this->xpath = $this->parameters['value'][$i];	
				}
				elseif( $key == 'attr' ){
					
					// get app resource
					
					$this->attr = $this->parameters['value'][$i];	
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
	
	public static function extractEmails( $string, $first_only = false ){
		
		$emails = ( $first_only ? '' : [] );
		
		// this regex handles more email address formats like a+b@google.com.sg, and the i makes it case insensitive
		$pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';

		// preg_match_all returns an associative array
		preg_match_all($pattern, $string, $matches);

		if(!empty($matches[0])){
			
			if($first_only){
				
				$emails = $matches[0][0];
			}
			else{
				
				$emails = $matches[0];
			}
		}
		
		return $emails;		
	}

	public function appImportImg(){
		
		if(!empty($_REQUEST['id'])){

			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID, true ) ){

				$resourceUrl = urldecode($this->app['resource']);

				$ch = curl_init($resourceUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$result = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if( $httpcode >= 300 ){
					
					$this->parent->dialog->scraper .= '<div class="alert alert-warning">';
						
						$this->parent->dialog->scraper .= 'Resource not reachable...';
							
					$this->parent->dialog->scraper .= '</div>';						
				}
				else{
					
					libxml_use_internal_errors(true);

					$dom = new DOMDocument();
					$dom->loadHTML($result);
					
					$xp = new DOMXPath($dom);

					$urls = array();

					$xpath = str_replace('\\\'','\'',$this->xpath);
					
					$images = $xp->query($xpath);

					if(!empty($images)){

						foreach($images as $image) {

							$src = $image->getAttribute($this->attr);
							
							if(!empty($src)){
								
								$urls[] = $src;
							}
						}
					}

					if(!empty($urls)){
						
						foreach($urls as $img_url){
							
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
			}
		}
	}
	
	public function appConnect(){

		$fields = $this->parent->apps->parse_url_fields($this->resourceUrl,'scraper_' . $this->term->slug . '_');

		if(!empty($fields)){

			if( isset($_POST['scraper_is_admin']) && $_POST['scraper_is_admin'] == 'on' ){

				$terms = array();
				
				foreach($fields as $k => $field){
					
					if( !empty($_POST[$field['id']]) ){
						
						$value = $_POST[$field['id']];
						
						$terms[$k] 		= $value;
						$this->data[$k] = $value;
					}
					else{
						
						$terms = array();
						
						$this->parent->dialog->scraper .= '<div class="alert alert-warning">';
							
							$this->parent->dialog->scraper .= 'A field is missing...';
								
						$this->parent->dialog->scraper .= '</div>';	
				
						break;
					}
				}
			}
			elseif(!empty($_POST)){
				
				$terms = array();
				
				$this->parent->dialog->scraper .= '<div class="alert alert-warning">';
					
					$this->parent->dialog->scraper .= 'You must be the admin of this resource...';
						
				$this->parent->dialog->scraper .= '</div>';					
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
					
					$this->parent->dialog->scraper .= '<div class="alert alert-warning">';
						
						$this->parent->dialog->scraper .= 'This resource couldn\'t be found...';
							
					$this->parent->dialog->scraper .= '</div>';						
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
						
						wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );
						
						// hook connected app
						
						do_action( 'ltple_' . str_replace( '-', '_', $this->slug ) . '_account_connected');
						
						$this->parent->apps->newAppConnected();
					}
					else{

						$app_id = $app_item->ID;
					}

					// update app item
						
					update_post_meta( $app_id, 'appData', json_encode($this->data,JSON_PRETTY_PRINT));
					
					// store success message

					$_SESSION['message'] = '<div class="alert alert-success">';
						
						$_SESSION['message'] .= 'Congratulations, you have successfully connected your ' . $this->term->name . ' account!';
							
					$_SESSION['message'] .= '</div>';
				}				
			}
			
			if( $outputForm ){
				
				// output form
				
				$input = $this->resourceUrl;
				
				foreach($fields as $k => $field){			
					
					$input = str_replace('{'.$k.'}',' '.$this->parent->admin->display_field( $field, false, false ).' ',$input);				
				}
				
				$this->parent->dialog->scraper .= '<form action="' . $this->parent->urls->current . '" method="post">';
					
					$this->parent->dialog->scraper .= '<div class="col-xs-8">';
					
						$this->parent->dialog->scraper .= '<h2>Add '.ucfirst($this->term->name).' account</h2>';
					
						$this->parent->dialog->scraper .= '<div class="well form-group">';
					
							$this->parent->dialog->scraper .= '<label class="input-group">';
							
								$this->parent->dialog->scraper .= 'Account url';
							
							$this->parent->dialog->scraper .= '</label>';					
					
							$this->parent->dialog->scraper .= $input;
							
							$this->parent->dialog->scraper .= '<div class="row">';
								
								$this->parent->dialog->scraper .= '<div class="col-xs-6 text-left" style="margin-top:10px;">';
									
									$this->parent->dialog->scraper .= $this->parent->admin->display_field( array(
									
										'type'				=> 'checkbox',
										'id'				=> 'scraper_is_admin',
										'style'				=> 'width:15px;height:15px;float:left;',
										'description'		=> '',
										
							
									), false, false );
									
									$this->parent->dialog->scraper .= 'I am the admin of this resource';
									
								$this->parent->dialog->scraper .= '</div>';
								
								$this->parent->dialog->scraper .= '<div class="col-xs-6 text-right" style="margin-top:10px;">';
								
									$this->parent->dialog->scraper .= '<button class="btn btn-sm btn-primary" type="submit">Connect</button>';
								
								$this->parent->dialog->scraper .= '</div>';
								
							$this->parent->dialog->scraper .= '</div>';
							
						$this->parent->dialog->scraper .= '</div>';
						
					$this->parent->dialog->scraper .= '</div>';
					
				$this->parent->dialog->scraper .= '</form>';				
			}
		}
		else{
			
			$this->parent->dialog->scraper .= 'Error getting app fields...';
		}
	}
} 
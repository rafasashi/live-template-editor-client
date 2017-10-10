<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Image extends LTPLE_Client_Object {
	
	public $parent;
	public $id		= -1;
	public $uri		= '';
	public $slug	= '';
	public $type	= '';
	public $types	= '';
	public $url		= '';
	public $dir		= '';
	
	public $isHosted = false;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->parent->register_post_type( 'default-image', __( 'Default Images', 'live-template-editor-client' ), __( 'Default Image', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'default-image',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports'			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title', 'editor'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_post_type( 'user-image', __( 'User Images', 'live-template-editor-client' ), __( 'User Image', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-image',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title', 'editor', 'author'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_taxonomy( 'image-type', __( 'Image Type', 'live-template-editor-client' ), __( 'Image Type', 'live-template-editor-client' ),  array('default-image'), array(
			
			'hierarchical' 			=> false,
			'public' 				=> true,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> true,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort'					=> '',
		));

		add_action( 'add_meta_boxes', function(){
			 
			$this->parent->admin->add_meta_box (
			
				'tagsdiv-image-type',
				__( 'Image Type', 'live-template-editor-client' ), 
				array("default-image"),
				'side'
			);
		});		
	
		add_filter("default-image_custom_fields", array( $this, 'get_fields' ));	
		
		add_filter('init', array( $this, 'init_image' ));
		
		add_action('wp_loaded', array($this,'get_images_types'));
		
		add_action( 'before_delete_post', array($this,'delete_static_images'), 10, 3 );
	}
	
	public function get_images_types(){

		$this->types = $this->get_terms( 'image-type', array(
			
			'backgrounds' 	=> 'Backgrounds',
			'buttons' 		=> 'Buttons',
			'dividers' 		=> 'Dividers',
			'headers' 		=> 'Headers',
			'icons' 		=> 'Icons',
			'footers' 		=> 'Footers',
		));
	}
	
	public function init_image(){
		
		if( isset($this->parent->layer->layerOutput) && $this->parent->layer->layerOutput == 'hosted-page' && $this->parent->layer->type == 'user-layer' ){
			
			$this->isHosted = true;
		}
		
		if( $this->isHosted ){
			
			$this->url = str_replace( 'https://', 'http://', dirname($this->parent->layer->layerStaticUrl) ) . '/assets/images/';
			$this->dir = dirname($this->parent->layer->layerStaticPath) . '/assets/images/';
		}
		else{
			
			$this->url = ( defined('LTPLE_IMAGE_URL') ? LTPLE_IMAGE_URL : str_replace( 'https://', 'http://', $this->parent->urls->home ) . '/i/');
			$this->dir = ( defined('LTPLE_IMAGE_DIR') ? LTPLE_IMAGE_DIR : ABSPATH . 'i/');
		}

		if( !is_admin() ) {
			
			add_action( 'rest_api_init', function () {
				
				register_rest_route( 'ltple-images/v1', '/list', array(
					
					'methods' 	=> 'GET',
					'callback' 	=> array($this,'list_user_images'),
				) );
			} );
				
			if(!empty($_GET['uri'])){
				
				if( $this->uri = intval($_GET['uri']) ){
					
					if( $q = get_post($this->uri) ){	

						if( $q->post_type == 'default-image' || $q->post_type == 'user-image' ){
						
							$this->id 		= $q->ID;
							$this->content 	= $q->post_content;
							$this->type 	= $q->post_type;
							$this->slug 	= $q->post_name;
						}
					}
				}
			}
			elseif( isset($_REQUEST['my-profile']) ){
				
				add_action( 'ltple_user_loaded', array( $this, 'upload_avatar_image' ), 0 );
				add_action( 'ltple_user_loaded', array( $this, 'upload_banner_image' ), 0 );
			}
		}
	}
	
	public function list_user_images( $rest = NULL ) {
		
		$images = [];
		$images['directory'] = $this->dir;
		$images['url'] 		 = $this->url;
		$images['path'] 	 = [];
		 
		$users = [];
		$posts = [];
		 
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir)) as $path => $iterator){
			
			list(,$path) = explode('/i/',$path);
			
			if( substr($path,-4) == '.png' ){
				
				list($user_id,$filename) = explode('/',$path);
				list($post_id,$element)  = explode('_',$filename,2);
				
				if( is_numeric($user_id) ){
				
					if( !isset($users[$user_id]) ){
						
						$users[$user_id] = get_userdata($user_id);
					}
					
					if( $user = $users[$user_id]->data ){
						
						if($user->user_email){
							
							$folder = md5($user->user_email);
						
							$images['path'][$folder][] = $path;
							
							$posts[$post_id . '/' . $folder][] = $path;
						}
					}
				}
			}
		}
		
		return $images;
	}
	
	public function get_fields(){

		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get image types
		
		$image_types=[];
		
		foreach($this->types as $term){
			
			$image_types[$term->slug]=$term->name;
		}
		
		//get current image type
		
		$terms = wp_get_post_terms( $post_id, 'image-type' );
		
		$default_image_type='';

		if(isset($terms[0]->slug)){
			
			$default_image_type=$terms[0]->slug;
		}
		
		$fields[]=array(
			"metabox" =>
				array('name'	=> "tagsdiv-image-type"),
				'id'			=> "new-tag-image-type",
				'name'			=> 'tax_input[image-type]',
				'label'			=> "",
				'type'			=> 'select',
				'options'		=> $image_types,
				'selected'		=> $default_image_type,
				'description'	=> ''
		);
		
		return $fields;
	}
	
	public function update_user_image(){	
		
		if( $this->parent->user->loggedin ){
			
			if( isset($_GET['imgAction']) && $_GET['imgAction']=='delete' ){
				
				//--------delete image--------
				
				wp_delete_post( $this->id, true );
				
				$this->id = -1;
					
				$this->parent->message ='<div class="alert alert-success">';

					$this->parent->message .= 'Image url successfully deleted!';

				$this->parent->message .='</div>';
				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='upload' && isset($_POST['imgHost'])){
				
				// valid host
				
				$app_item = get_post( $_POST['imgHost'], 'user-app' );
				
				$app_title = wp_strip_all_tags( $app_item->post_title );
				
				if( empty($app_item) || ( intval( $app_item->post_author ) != $this->parent->user->ID && !in_array_field($app_item->ID, 'ID', $this->parent->apps->mainApps)) ){
					
					echo 'This image host doesn\'t exists...';
					exit;
				}
				elseif(!empty($_FILES)) {
					
					foreach ($_FILES as $file => $array) {
						
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK ) {
							
							if( intval($_FILES[$file]['error']) != 4 ){
								
								echo "upload error : " . $_FILES[$file]['error'];
								exit;
							}
						}
						else{
							
							$mime = explode('/',$_FILES[$file]['type']);
							
							if($mime[0] !== 'image') {
								
								echo 'This is not a valid image type...';
								exit;							
							}
							
							if( $data = file_get_contents($_FILES[$file]['tmp_name'])){
								
								// rename file
								
								$_FILES[$file]['name'] = md5($data) . '.' . $mime[1];

								// get current app
								
								$app = explode(' - ', $app_title );
								
								// set session
								
								$_SESSION['app'] 	= $app[0];
								$_SESSION['action'] = 'upload';
								$_SESSION['file'] 	= $_FILES[$file]['name'];
																		
								//check if image exists
								
								$img_exists = false;
								
								$q = new WP_Query(array(
									
									'post_author' => $this->parent->user->ID,
									'post_type' => 'user-image',
									'numberposts' => -1,
								));

								while ( $q->have_posts() ) : $q->the_post(); 
							
									global $post;
									
									if( $post->post_title == $_FILES[$file]['name'] ){
										
										$img_exists = true;
										break;
									}
									
								endwhile; wp_reset_query();
								
								if( !$img_exists ){
									
									//require the needed files
									
									require_once(ABSPATH . "wp-admin" . '/includes/image.php');
									require_once(ABSPATH . "wp-admin" . '/includes/file.php');
									require_once(ABSPATH . "wp-admin" . '/includes/media.php');
									
									//upload image
									
									$attach_id = media_handle_upload( $file, 0 );
									
									if(is_numeric($attach_id)){
									
										// get image url
										
										$image_url = wp_get_attachment_url( $attach_id );
										
										// upload image to host
										
										$appSlug = $app[0];
										
										if( !isset( $this->parent->apps->{$appSlug} ) ){
											
											$this->parent->apps->includeApp($appSlug);
										}

										if( $image_id = $this->parent->apps->{$appSlug}->appUploadImg( $app_item->ID, $image_url )){
											
											// mark image as uploaded
											 
											update_post_meta($image_id, 'imageUploaded', 'true');
											
											// output success message
											
											$this->parent->message ='<div class="alert alert-success">';
													
												$this->parent->message .= 'Congratulations! Image succefully uploaded to your library.';

											$this->parent->message .='</div>';											
										}
										else{
											
											// output error message
											
											$this->parent->message ='<div class="alert alert-danger">';
													
												$this->parent->message .= 'Oops, something went wrong...';

											$this->parent->message .='</div>';													
										}
										
										// remove image from local library
										
										wp_delete_attachment( $attach_id, $force_delete = true );
									}
									else{
										
										echo 'Error handling upload...';
										exit;											
									}
								}
								else{
									
									// output warning message
									
									$this->parent->message ='<div class="alert alert-warning">';
											
										$this->parent->message .= 'This image already exists...';

									$this->parent->message .='</div>';										
								}
							}
							else{
								
								echo 'Error uploading your image...';
								exit;									
							}
						}
					}   
				}				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='save' && isset($_POST['imgTitle']) && isset($_POST['imgUrl']) ){
				
				//-------- save image --------
				
				$img_id = $img_title = $img_name = $img_content = '';
				
				if($_POST['imgTitle']!=''){

					$img_title = $img_name = wp_strip_all_tags( $_POST['imgTitle'] );
				}
				else{ 
					
					$img_title = $img_name = 'image_' . time();
				}

				if($_POST['imgUrl']!=''){
				
					$img_content=wp_strip_all_tags( $_POST['imgUrl'] );
				}
				else{
					
					echo 'Empty image url...';
					exit;
				}
				
				if( $img_title!='' && $img_content!=''){
					
					$img_valid = true;
					
					if($img_valid === true){
						
						// check if is valid url
						
						if (filter_var($img_content, FILTER_VALIDATE_URL) === FALSE) {
							
							$img_valid = false;
						}
					}
					
					if($img_valid === true){
						
						// check if image exists
						
						$q = new WP_Query(array(
							
							'post_author' => $this->parent->user->ID,
							'post_type' => 'user-image',
							'numberposts' => -1,
						));
						
						//var_dump($q);exit;
						
						while ( $q->have_posts() ) : $q->the_post(); 
					
							global $post;
							
							if( $post->post_content == $img_content ){
								
								$img_valid = false;
								break;
							}
							
						endwhile; wp_reset_query();	
					}
					
					if( $img_valid === true ){
					
						if($post_id = wp_insert_post( array(
							
							'post_author' 	=> $this->parent->user->ID,
							'post_title' 	=> $img_title,
							'post_name' 	=> $img_name,
							'post_content' 	=> $img_content,
							'post_type'		=> 'user-image',
							'post_status' 	=> 'publish'
						))){
							
							$this->parent->message ='<div class="alert alert-success">';
									
								$this->parent->message .= 'Congratulations! Image url succefully added to your library.';

							$this->parent->message .='</div>';						
						}						
					}
					else{

						$this->parent->message ='<div class="alert alert-danger">';
								
							$this->parent->message .= 'This image url already exists...';

						$this->parent->message .='</div>';
					}
				}
				else{
					
					$this->parent->message ='<div class="alert alert-danger">';
							
						$this->parent->message .= 'Error saving user image...';

					$this->parent->message .='</div>';
				}
			}			
		}
	}
	
	public function upload_cropped_image($name,$base64){
		
		if( !empty($this->parent->user->ID) ){
		
			list(,$img) = explode('image/png;base64,',$base64);

			if($img = base64_decode($img)){
				
				//http_response_code(404);exit;

				if ($img = imagecreatefromstring($img)) {

					// create image directory
					
					if (!file_exists($this->dir)) {
						
						mkdir($this->dir, 0755, true);
						
						//file_put_contents( $this->dir . 'index.html', '');
					}
					
					// get user image path
					
					if( $this->isHosted ){
						
						$path = $this->dir;
					}
					else{
						
						$path = $this->dir . $this->parent->user->ID . '/';
					}
					
					// create user image path
				
					if( !file_exists($path) ) {
						
						mkdir($path, 0755, true);
						
						//file_put_contents($path . 'index.html', '');
					}
					
					// set transparency
					
					imagealphablending($img, false);
					imagesavealpha($img, true);					
					
					// put user image

					imagepng($img, $path.$name);
					
					$info = getimagesize($path.$name);

					if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
						
						if( $this->isHosted ){
							
							return $this->url . $name . '?ltple-time=' . time();
						}
						else{
							
							return $this->url . $this->parent->user->ID . '/' . $name . '?ltple-time=' . time();
						}
					}
					else{
						
						unlink($path.$name);
					}
				}
			}
		}
		
		return false;
	}
	
	public function get_avatar_path($user_id){
		
		if( is_numeric($user_id) ){
			
			return $this->dir . $user_id . '/avatar.png';
		}

		return false;
	}
	
	public function get_avatar_url($user_id){
		
		if( is_numeric($user_id) ){
			
			return $this->url . $user_id . '/avatar.png';
		}

		return false;
	}
	
	public function get_banner_path($user_id){
		
		if( is_numeric($user_id) ){
			
			return $this->dir . $user_id . '/banner.png';
		}

		return false;
	}
	
	public function get_banner_url($user_id){
		
		if( is_numeric($user_id) ){
			
			if( file_exists($this->get_banner_path($user_id)) ){
				
				return $this->url . $user_id . '/banner.png';
			}
			else{
				
				return plugins_url() . '/' . $this->parent->settings->plugin->slug . '/assets/images/profile_header.jpg';
			}
		}

		return false;
	}
	
	public function upload_avatar_image(){
		
		if( !empty($this->parent->user->ID) && !empty($_FILES['avatar']) ){
				
			if($_FILES['avatar']['error'] !== UPLOAD_ERR_OK ) {
				
				if( intval($_FILES['avatar']['error']) != 4 ){
					
					echo "upload error : " . $_FILES['avatar']['error'];
					exit;
				}
			}
			else{
				
				$mime = explode('/',$_FILES['avatar']['type']);
				
				if($mime[0] !== 'image') {
					
					echo 'This is not a valid image type...';
					exit;							
				}
				
				$image = wp_get_image_editor( $_FILES['avatar']['tmp_name'] );
				
				if ( !is_wp_error( $image ) ){
					
					// resize image
					
					//$image->rotate( 90 );
					$image->resize( 50, 50, true );
					$image->save( $this->get_avatar_path( $this->parent->user->ID ) );

					return true;
				}
			}
		}
		
		return false;
	}
	
	public function upload_banner_image(){
		
		if( !empty($this->parent->user->ID) && !empty($_FILES['banner']) ){
				
			if($_FILES['banner']['error'] !== UPLOAD_ERR_OK ) {
				
				if( intval($_FILES['banner']['error']) != 4 ){
					
					echo "upload error : " . $_FILES['banner']['error'];
					exit;
				}
			}
			else{
				
				$mime = explode('/',$_FILES['banner']['type']);
				
				if($mime[0] !== 'image') {
					
					echo 'This is not a valid image type...';
					exit;							
				}
				
				$image = wp_get_image_editor( $_FILES['banner']['tmp_name'] );
	
				if ( !is_wp_error( $image ) ){
					
					// resize image
					
					//$image->rotate( 90 );
					$image->resize( 1920, 1080, true );
					$image->save( $this->get_banner_path( $this->parent->user->ID ) );

					return true;
				}
			}
		}
		
		return false;
	}
	
	public function delete_static_images($post_id){
		
		$image_dir = $this->dir . get_post_field( 'post_author', $post_id ) . '/';

		$images = glob( $image_dir . $post_id . '_*.png');

		foreach ($images as $image) {
			
			unlink($image);
		}		
		
		return true;
	}
}

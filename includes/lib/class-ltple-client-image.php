<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Image extends LTPLE_Client_Object {
	
	public $parent;
	public $id		= -1;
	public $att		= -1;
	public $uri		= '';
	public $slug	= '';
	public $type	= '';
	public $types	= '';
	public $url		= '';
	public $dir		= '';
	
	public $banners	= array();
	
	public $isDownloadable = false;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent = $parent;
	
		$this->parent->register_post_type( 'default-image','Default Images','Default Image', '', array(

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
		
		$this->parent->register_post_type( 'user-image','User Images','User Image', '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
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

		$this->parent->register_taxonomy( 'image-type','Image Sections','Image Section',  array('default-image'), array(
			
			'hierarchical' 			=> false,
			'public' 				=> true,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> true,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
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
	
		add_filter('default-image_custom_fields', array( $this, 'get_fields' ));	
		
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

		$this->url = defined('LTPLE_IMAGE_URL') ? LTPLE_IMAGE_URL : $this->parent->urls->home . '/i/';
		
		$this->dir = defined('LTPLE_IMAGE_DIR') ? LTPLE_IMAGE_DIR : ABSPATH . 'i/';
		
		if( !is_admin() ) {
			
			add_action( 'rest_api_init', function () {
				
				if( $this->parent->user->is_admin ){
				
					register_rest_route( 'ltple-images/v1', '/list/', array(
						
						'methods' 	=> 'GET',
						'callback' 	=> array($this,'list_user_images'),
						'permission_callback' => '__return_true',
					) );
				}
				
			} );
			
			if( !empty($_GET['uri']) ){
				
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
			
			add_action( 'ltple_update_profile', array( $this, 'upload_avatar_image' ), 0 );
			add_action( 'ltple_update_profile', array( $this, 'upload_banner_image' ), 0 );
		}
	}
	
	public function list_user_images( $rest = NULL ) {
		
		$images = [];
		$images['directory'] = $this->dir;
		$images['url'] 		 = $this->url;
		$images['path'] 	 = [];
		 
		$users = [];
		$posts = [];
		 
		if( $this->parent->user->is_admin ){
		 
			foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir)) as $path => $iterator){
				
				list(,$path) = explode('/i/',$path);
				
				if( substr($path,-4) == '.png' ){
					
					list($user_id,$filename) = explode('/',$path);
					
					list($post_id)  = explode('_',$filename,1);
					
					if( is_numeric($user_id) ){
					
						if( !isset($users[$user_id]) ){
							
							$users[$user_id] = get_userdata($user_id);
						}

						if( isset($users[$user_id]->data) ){
							
							$user = $users[$user_id]->data;
							
							if( !empty($user->user_email) ){
								
								$folder = md5($user->user_email);
							
								$images['path'][$folder][] = $path;
								
								$posts[$post_id . '/' . $folder][] = $path;
							}
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
			
			// perform action		
			
			if( isset($_GET['imgAction']) && $_GET['imgAction']=='delete' ){
				
				// get attribute id
				
				if(!empty($_GET['att'])){

					if( $image = get_post( intval($_GET['att']) ) ){

						if( $image->post_type == 'attachment' && intval($image->post_author) == $this->parent->user->ID ){
							
							if( $image->post_parent == 0 ){
							
								$this->att = $image->ID;
							}
							else{
								
								$this->parent->exit_message('Image attached to project id: ' . $image->post_parent,404);
							}
						}
						else{
						
							$this->parent->exit_message('You don\'t have access to this image',404);
						}
					}
				}				
				
				if( $this->att > 0 ){
				
					//--------delete image--------
				
					wp_delete_attachment( $this->att );
					
					$this->att = -1;
					
					$this->parent->exit_message('Image successfully deleted!',200);
				}
				elseif( $this->id > 0 ){
						
					//--------delete url--------
					
					wp_delete_post( $this->id, true );
					
					$this->id = -1;
					
					$this->parent->exit_message('Image url successfully deleted!',200);
				}
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='upload' ){
				
				$app_title = 'image - uploaded';
				
				if(!empty($_FILES)) {
					
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
								
								$message = '';
								
								// rename file
								
								$md5 = md5($data);
								
								$_FILES[$file]['name'] = $md5 . '.' . $mime[1];
								
								$user_id = $this->parent->user->ID;
								
								// set session
								
								$this->parent->session->update_user_data('app','image',$user_id);
								$this->parent->session->update_user_data('action','upload',$user_id);
								$this->parent->session->update_user_data('file',$_FILES[$file]['name'],$user_id);
																		
								//check if image exists
								
								if( !get_posts(array(
									
									's' 			=> $md5,
									'author' 		=> $user_id,
									'post_type' 	=> 'attachment',
									'posts_per_page'=> -1,
								)) ){
									
									//require the needed files
									
									require_once(ABSPATH . "wp-admin" . '/includes/image.php');
									require_once(ABSPATH . "wp-admin" . '/includes/file.php');
									require_once(ABSPATH . "wp-admin" . '/includes/media.php');
									
									//upload image
									
									if( $attach_id = media_handle_upload( $file, 0 ) ){
				
										// set source meta
										
										update_post_meta($attach_id,$this->parent->_base . 'upload_source','upload');
				
										// output message
										
										if( !$this->parent->inWidget ){
											
											$message ='<div class="alert alert-success">';
													
												$message .= 'Image successfully uploaded to your library.';

											$message .='</div>';	
										}										
									}
									else{
										
										$message ='<div class="alert alert-danger">';
												
											$message .= 'Error uploading image...';

										$message .='</div>';										
									}
								}
								else{
									
									// output warning message
									
									$message ='<div class="alert alert-warning">';
											
										$message .= 'This image already exists...';

									$message .='</div>';										
								}
								
								if( !empty($message) ){
									
									$this->parent->session->update_user_data('message',$message,$user_id);
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
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='save' && isset($_POST['imgUrl']) ){
				
				//-------- save image --------
				
				$img_id = $img_title = $img_name = $img_content = '';
				
				$img_title = $img_name = 'image_' . time();

				if($_POST['imgUrl']!=''){
				
					$img_content=wp_strip_all_tags( $_POST['imgUrl'] );
				}
				else{
					
					echo 'Empty image url...';
					exit;
				}
				
				$message = '';
				
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
							
							'post_author' 	=> $this->parent->user->ID,
							'post_type' 	=> 'user-image',
							'numberposts' 	=> -1,
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
							
							if( !$this->parent->inWidget ){
							
								$message ='<div class="alert alert-success">';
									
									$message .= 'Congratulations! Image url successfully added to your library.';

								$message .='</div>';	
							}
						}						
					}
					else{

						$message ='<div class="alert alert-danger">';
								
							$message .= 'This image url already exists...';

						$message .='</div>';
					}
				}
				else{
					
					$message ='<div class="alert alert-danger">';
							
						$message .= 'Error saving user image...';

					$message .='</div>';
				}
				
				if( !empty($message) )
				
					$this->parent->session->update_user_data('message',$message);
			}			
		}
	}
	
	public function upload_post_image($image_url,$post_id,$source=''){
		
		if( !empty($this->parent->user->ID) ){
			
			if ( !function_exists('media_handle_upload') ) {
				
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			}			
			
			if( $tmp = download_url( $image_url ) ){
				
				list($type,$ext) = explode('/',mime_content_type($tmp));
				
				if( $type == 'image' ){
					
					if( $data = file_get_contents($tmp) ){
					
						if( !empty($source) ){
							
							$source .= '_';
						}
					
						$md5 = $source . md5($data);

						//check if image exists
						
						$q = new WP_Query(array(
							
							'name' 			=> $md5,
							'post_author' 	=> $this->parent->user->ID,
							'post_type' 	=> 'attachment',
							'posts_per_page'=> -1,
						));

						if( $q->post_count == 0 ){					
							
							$file_array = array(
							
								'name' 		=> $md5 . '.' . $ext,
								'tmp_name' 	=> $tmp,
							);
							
							$post_data = array(
							
								'post_title' => $md5,
							);

							if ( $attach_id = media_handle_sideload( $file_array, null, null, $post_data ) ) {
								
								set_post_thumbnail($post_id, $attach_id);
								
								if( !empty($source) ){
								
									update_post_meta($attach_id,$this->parent->_base . 'upload_source',$source);
								}
							}
						}
						else{
							
							set_post_thumbnail($post_id, $q->posts[0]->ID);
						}
					}
				}
			}

			@unlink($file_array['tmp_name']);
		}
	}
	
	public function get_avatar_path($user_id,$md5=''){
		
		if( is_numeric($user_id) ){
			
			if( !empty($md5) ){
				
				$md5 = '_' . $md5;
			}
			
			return $this->dir . $user_id . '/avatar'.$md5.'.png';
		}
		
		return false;
	}
	
	public function get_avatar_url($user_id){
		
		$url = $this->parent->assets_url . 'images/avatar.png';
		
		if( defined('REW_DEV_ENV') && REW_DEV_ENV === true ){
			
			$u = parse_url($url);
			
			$url = $u['scheme'] . '://' . REW_SITE . $u['path'];
		}

		if( is_numeric($user_id) ){
			
			$path = $this->get_avatar_path($user_id);

			if( file_exists($path) ){
			
				$url = add_query_arg( array(
				
					'_' => time(),
				
				),$this->url . $user_id . '/avatar.png');
			}
			elseif( $user_id > 0 ){
				
				$gravatar_url = get_avatar_url( $user_id, array(
					
					'size'		=> 125,
					'default' 	=> $url,
				));
				
				$image = wp_get_image_editor( $gravatar_url );
				
				if ( !is_wp_error( $image ) ){
					
					$image->save($path);

					$url = $this->url . $user_id . '/avatar.png';
				}
			}
		}
		
		return $url;
	}
	
	public function get_banner_path($user_id){
		
		if( is_numeric($user_id) ){
			
			return $this->dir . $user_id . '/banner.png';
		}

		return false;
	}
	
	public function get_banner_url($user_id){
		
		if( is_numeric($user_id) ){
		
			if(!isset($this->banners[$user_id])){
				
				if( file_exists($this->get_banner_path($user_id)) ){
					
					$this->banners[$user_id] = $this->url . $user_id . '/banner.png';
				}
				else{
					 
					$this->banners[$user_id] = $this->parent->settings->options->profile_header;
				}
			}
			
			return $this->banners[$user_id];
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
					$image->resize( 125, 125, true );
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

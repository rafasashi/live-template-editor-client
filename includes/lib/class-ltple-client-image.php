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
		
		$this->parent->register_post_type( 'default-image', __( 'Default images', 'live-template-editor-client' ), __( 'Default image', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'default-image',
			'show_in_nav_menus' 	=> true,
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
		
		$this->parent->register_post_type( 'user-image', __( 'User images', 'live-template-editor-client' ), __( 'User image', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-image',
			'show_in_nav_menus' 	=> true,
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
			'show_in_nav_menus' 	=> true,
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
	
	public function upload_cropped_image($name,$base64){
		
		if( !empty($this->parent->user->ID) ){
		
			list(,$img) = explode('image/png;base64,',$base64);

			if($img = base64_decode($img)){
				
				//http_response_code(404);exit;

				if ($img = imagecreatefromstring($img)) {

					// create image directory
					
					if (!file_exists($this->dir)) {
						
						mkdir($this->dir, 0755, true);
						
						file_put_contents( $this->dir . 'index.html', '');
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
						
						/*
						if( !$this->isHosted ){
						
							file_put_contents($path . 'index.html', '');
						}
						*/
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
	
	public function delete_static_images($post_id){
		
		$image_dir = $this->dir . get_post_field( 'post_author', $post_id ) . '/';

		$images = glob( $image_dir . $post_id . '_*.png');

		foreach ($images as $image) {
			
			unlink($image);
		}		
		
		return true;
	}
}

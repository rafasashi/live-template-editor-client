<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Update {

	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;

		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-update/v1', '/layers', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'update_layers'),
				'permission_callback' => '__return_true',
			) );
			
			register_rest_route( 'rew-export/v1', '/post_type/(?P<type>[\S]+)/(?P<key>[\S]+)', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'export_post_type'),
				'permission_callback' => '__return_true',
			));
			
		} );
		
		add_action( 'admin_post_update', array($this,'filter_update_settings'));
	}
	
	public function filter_update_settings(){
		
		if( !empty($_REQUEST['ltple_data']) ){
			
			if( !empty($_REQUEST['ltple_data']['import']) && !empty($_REQUEST['ltple_data']['key']) ){
				
				$source = sanitize_url($_REQUEST['ltple_data']['import']);
				
				$key = sanitize_text_field($_REQUEST['ltple_data']['key']);
				
				// import data
				
				$response = wp_remote_get($source);
				
				if ( is_array( $response ) ) {
								
					$data = $response['body'];
					
					$content_type = wp_remote_retrieve_header($response,'content-type');
									
					if( strpos($content_type,';') !== false ){
					
						$type = strtok($content_type,';');
					}
					else{
						
						$type = $content_type;
					}		
				}
				
				if( $type == 'application/json'){
					
					$data = json_decode($data,true);
					
					if( isset($data['data']) ){
						
						if( $data = $this->parent->ltple_decrypt_str($data['data'],$key) ){
							
							$data = json_decode($data,true);
							
							if( !empty($data['terms']) ){
								
								dump($data['terms']);
							}
						}
					}
					
					dump($data);
				}
			}
			
			$url = admin_url('admin.php?page=ltple-settings&tab=data');
			
			wp_redirect($url);
			exit;
		}
	}
	
	public function update_layers($rest = NULL){
		
		$layers = [];
		
		if( $q = get_posts(array( 
	
			'post_type' 	=> 'cb-default-layer', 
			'posts_per_page'=> -1				
		))){
		
			foreach($q as $layer){
				
				$layers[$layer->ID] = add_post_meta( $layer->ID, 'layerContent', $layer->post_content, true);
			}
		}
		
		if( $q = get_posts(array( 
	
			'post_type' 	=> 'user-layer', 
			'posts_per_page'=> -1				
		))){
		
			foreach($q as $layer){
				
				$layers[$layer->ID] = add_post_meta( $layer->ID, 'layerContent', $layer->post_content, true);
			}
		}
		
		return $layers;
	}
	
	public function export_post_type($rest=null){
		
		//$referer = $rest->get_header('referer');
		
		$type = sanitize_title($rest['type']);
		$key = sanitize_title($rest['key']);
		
		$export = array();
		
		if( $post_type = get_post_type_object($type) ){
			
			if( $posts = get_posts(array(
			
				'post_type'		=> $post_type->name,
				'numberposts'	=> 1000,
			
			))){
				
				$taxonomies = get_object_taxonomies($post_type->name);
	
				foreach( $posts as $post ){
					
					$post_id = $post->ID;
					
					$export['posts'][$post_id] = $post;
					
					if( !isset($export['posts_meta'][$post_id]) ){
						
						$export['posts_meta'][$post_id] = get_post_meta($post_id);
					}
					
					if( !empty($taxonomies) ){
					
						foreach( $taxonomies as $taxonomy ){
						
							if( $terms = wp_get_post_terms($post_id,$taxonomy) ){
								
								foreach( $terms as $term ){
									
									$term_id = $term->term_id;
									
									$export['terms'][$term_id] = $term;
									
									if( !isset($export['terms_meta'][$term_id]) ){
									
										$export['terms_meta'][$term_id] = get_term_meta($term_id);
									}
								}
							}
						}
					}
				}
			}
		}
		
		return array( 'data' => $this->parent->ltple_encrypt_str(json_encode($export),$key) );
	}
	
	
	/**
	 * Main LTPLE_Client_Update Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Update is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Update instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}
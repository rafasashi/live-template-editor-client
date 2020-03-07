<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Editor { 
	
	public $parent;

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;
	
		add_filter('ltple_loaded', array( $this, 'init_editor' ));
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('editor',$query_vars)){
				
				$query_vars[] = 'editor';
			}
			
			return $query_vars;
			
		}, 1);

		add_filter( 'template_redirect', array( $this, 'get_editor' ),1);	
				
	}
	
	public function init_editor(){
		
		// add rewrite rules
		
		add_rewrite_rule(
		
			'edit/?$',
			'index.php?edit=editor',
			'top'
		);
	}
	
	public function get_editor(){
		
		if( $slug = get_query_var('edit') ){

			// get layer range
			
			$terms = wp_get_object_terms( $this->parent->layer->id, 'layer-range' );
			
			$this->parent->layer->range = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');
			
			// get layer price
			
			$this->parent->layer->price = ( !empty($this->parent->layer->range) ? $this->parent->layer->get_plan_amount($this->parent->layer->range,'price') : 0 );
			
			// Custom default layer post
			
			if( $this->parent->layer->defaultId > 0 ){
				
				remove_all_filters('content_save_pre');
				//remove_filter( 'the_content', 'wpautop' ); // remove line breaks from post_content
			}

			// get editor iframe
			
			if( !empty($this->parent->layer->key) ){
				
				if( $this->parent->user->loggedin === true && $this->parent->layer->type!='' && $this->parent->server->url!==false ){
					
					if( $this->parent->layer->key == md5( 'layer' . $this->parent->layer->id . $this->parent->_time )){
						
						if( !empty($_POST['base64']) && !empty($_POST['domId']) ){
							
							// handle cropped image upload
							
							echo $this->parent->image->upload_editor_image($this->parent->layer->id . '_' . $_POST['domId'] . '.png' ,$_POST['base64']);
						}
						elseif( !empty($_FILES) && !empty($_POST['location']) && $_POST['location'] == 'media' ){
								
							// handle canvas image upload
							
							echo $this->parent->image->upload_collage_image();
						}
						else{
							
							include( $this->parent->views . '/editor-proxy.php' );
						}
					}
					else{
						
						echo 'Malformed iframe request...';				
					}
				}
				else{
					
					echo 'Error starting editor...';
				}
			}
			elseif( $this->parent->user->has_layer ){
				
				if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ){
					
					include( $this->parent->views . '/editor-panel.php' );
				}
				else{
					
					include( $this->parent->views . '/editor-starter.php' );
				}
			}
			
			exit;
		}
	}
}

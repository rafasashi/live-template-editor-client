<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer {
	
	public $id			= -1;
	public $defaultId	= -1;
	public $uri			= '';
	public $key			= ''; // gives the server proxy access to the layer
	public $slug		= '';
	public $type		= '';
	public $outputMode	= '';	
	
	/**
	 * Constructor function
	 */
	public function __construct () {
		
		if(isset($_GET['lk'])){
			
			$this->key = sanitize_text_field($_GET['lk']);
		}			
		
		if(isset($_GET['uri'])){
			
			$this->uri = sanitize_text_field($_GET['uri']);
			
			$args=explode('/',$_GET['uri']);
			
			if(isset($args[1])&&($args[0]=='default-layer'||$args[0]=='user-layer')){

				$this->type = $args[0];
				$this->slug = $args[1];
	
				$layer_type=$this->type;
				if($layer_type == 'default-layer'){
					
					$layer_type = 'cb-' . $layer_type;
				}
	
				$q = get_posts(array(
					'post_type'      => $layer_type,
					'posts_per_page' => 1,
					'post_name__in'  => [ $this->slug ],
					//'fields'         => 'ids' 
				));
				
				//var_dump($q);exit;
				
				if(isset($q[0])){
					
					$this->id = $q[0]->ID;

					if( $this->type == 'user-layer' ){
					
						$this->content 	 = $q[0]->post_content;
						$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
					}
					else{
						
						$this->defaultId = $this->id;
					}

					$this->outputMode 	= get_post_meta( $this->defaultId, 'layerOutput', true );
					
					// recalled in layer template...
					//$this->margin 		= get_post_meta( $this->defaultId, 'layerMargin', true );
					//$this->options 		= get_post_meta( $this->defaultId, 'layerOptions', true );					
				}
				else{
					
					echo 'Cannot find layer...';
					exit;
				}
			}
		}
	}
}
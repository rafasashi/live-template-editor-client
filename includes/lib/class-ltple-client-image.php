<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Image {
	
	public $id		= -1;
	public $uri		= '';
	public $slug	= '';
	public $type	= '';
	
	/**
	 * Constructor function
	 */
	public function __construct () {
		
		if(isset($_GET['uri'])){
			
			$this->uri = sanitize_text_field($_GET['uri']);
			
			$args=explode('/',$_GET['uri']);
			
			if(isset($args[1])&&($args[0]=='default-image'||$args[0]=='user-image')){

				$this->type = $args[0];
				$this->slug = $args[1];

				$q = get_posts(array(
					'post_type'      => $this->type,
					'posts_per_page' => 1,
					'post_name__in'  => [ urlencode($this->slug) ],
					//'fields'       => 'ids' 
				));
				
				//var_dump($q);exit;
				
				if(isset($q[0])){
					
					$this->id = $q[0]->ID;
					$this->content = $q[0]->post_content;
				}
			}
		}	
	}
}
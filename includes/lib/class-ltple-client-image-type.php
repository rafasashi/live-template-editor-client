<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Image_Type extends LTPLE_Client_Object {
	
	var $parent;
	var $taxonomy;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->taxonomy = 'image-type';
		
		add_action( 'add_meta_boxes', function(){
			 
			$this->parent->admin->add_meta_box (
			
				'tagsdiv-image-type',
				__( 'Image Type', 'live-template-editor-client' ), 
				array("default-image"),
				'side'
			);
		});
		
		add_action( 'wp_loaded', array($this,'get_images_types'));
	}
	
	public function get_images_types(){

		$this->list = $this->get_terms( $this->taxonomy, array(
			
			'backgrounds' 	=> 'Backgrounds',
			'buttons' 		=> 'Buttons',
			'dividers' 		=> 'Dividers',
			'headers' 		=> 'Headers',
			'icons' 		=> 'Icons',
			'footers' 		=> 'Footers',
		));
	}
}  
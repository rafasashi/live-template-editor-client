<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Object {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
	}
	
	public function get_terms( $taxonomy, $default = [], $order = 'ASC', $hide_empty = false ){

		$list =  get_terms(array(
		
			'taxonomy' 		=> $taxonomy, 
			'order' 		=> $order, 
			'hide_empty'	=> $hide_empty,
		));

		if( !empty($default) ){
			
			// insert default terms
		
			foreach($default as $slug => $data){
				
				if( !in_array_field( $slug, 'slug', $list ) ){
					
					$name= '';
					
					if( is_string($data) ){
						
						$name = $data;
					}
					elseif( !empty($data['name']) ){
						
						$name = $data['name'];
					}
				
					if( !empty($name) ){
						
						$term = wp_insert_term($name, $taxonomy, array('slug' => $slug));

						if( !empty($data['options']) ){
							
							// insert options
							
							foreach( $data['options'] as $option => $value){
								
								update_option($option . '_' . $slug, $value);	
							}
						}
				
						$list[] = get_term_by( 'id', $term['term_id'], $taxonomy );
					}
				}			
			}
		}
		
		return $list;
	}
}  
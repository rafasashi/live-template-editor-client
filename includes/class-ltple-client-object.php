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
	
	public function get_terms( $taxonomy, $default = [], $order = 'ASC', $hide_empty = false, $parent = 0 ){

		$list =  get_terms(array(
		
			'taxonomy' 		=> $taxonomy, 
			'order' 		=> $order, 
			'hide_empty'	=> $hide_empty,
			//'update_term_meta_cache' => false,
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
						
						$term = wp_insert_term($name, $taxonomy, array( 'slug' => $slug, 'parent' => $parent ));

						if( !empty($data['options']) ){
							
							// insert options
							
							foreach( $data['options'] as $option => $value){
								
								update_option( $option . ( !empty($data['separator']) ? $data['separator'] : '_' ) . $slug, $value, false);	
							}
						}
				
						$list[] = get_term_by( 'id', $term['term_id'], $taxonomy );
					}
				}
				
				// insert children

				foreach($list as $term){
					
					if( !empty($default[$term->slug]['children']) ){
						
						$list = array_merge( $list, $this->get_terms($taxonomy, $default[$term->slug]['children'], $order, $hide_empty, $term->term_id) );
					}
				}			
			}
		}

		return $list;
	}
	
	public function get_meta( $term, $key ){
		
		if( is_numeric($term) ){
			
			$term = get_term_by('id',$term);
		}
		
		$meta = null;
		
		if( !empty($term->term_id) ){
		
			if( !$meta = get_term_meta( $term->term_id, $key, true ) ){
				
				// get value from options (deprecated schema)
				
				$option = get_option( $key . '_' . $term->slug, null );
			
				if( !is_null($option) ){
					
					$meta = $option;

					// migrate data  
					
					update_term_meta( $term->term_id, $key, $meta);	
				}
			}
			
			// normalize contents
			
			if( $key == 'js_content' ){
				
				$b64 = base64_decode($meta,true);
				
				$meta = ( base64_encode($b64) === $meta ) ? $b64 : $meta;
			}
		}
		
		return $meta;
	}
	
	public function index_keys($fields = array()){
		
		$index = array();

		if( !empty($fields) ){
		
			foreach( $fields as $field ){
				
				if( !empty($field) ){

					foreach( $field as $name => $value ){
						
						$index[$name][] = $value;
					}
				}
			}
		}
		
		return $index;
	}
	
	public function group_keys($fields = array()){
		
		$group = array();

		if( !empty($fields) ){
		
			foreach( $fields as $key => $values ){
				
				if( !empty($values) && is_array($values) ){
				
					foreach( $values as $i => $value ){
						
						$group[$i][$key] = $value;
					}
				}
			}
		}
		
		return $group;
	}

	public function get_modal($modal_url,$modal_title=null){
		
		if( $modal_url = apply_filters('ltple_modal_url',add_query_arg(array('output'=>'widget'),$modal_url)) ){
			
			$modal_id = 'modal_'.md5($modal_url);

			$content = '<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog">'.PHP_EOL;
				
				$content .= '<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
					
					$content .= '<div class="modal-content">'.PHP_EOL;
					
                        if( !empty($modal_title) ){
                            
                            $content .= '<div class="modal-header">'.PHP_EOL;
                                
                                $content .= '<h4 class="modal-title text-left">'.$modal_title.'</h4>'.PHP_EOL;
                            
                                $content .= '<button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
                                
                            $content .= '</div>'.PHP_EOL;
                        }
                        else{
                            
                            $content .= '<button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close" style="position:absolute;top:5px;right:5px;z-index:999999;">';
                                
                                $content .= '<span aria-hidden="true" style="background:#eee;display:block;width:30px;height:30px;border-radius:25px;font-size:26px;font-weight:bold;">&times;</span>';
                            
                            $content .= '</button>';
                        }
						
						$content .= '<iframe data-src="'.$modal_url.'" style="width:100%;position:relative;bottom:0;border:0;height:' . ( !empty($modal_title) ? 'calc( 100vh - 50px)' : '100vh' ) .';overflow:hidden;"></iframe>';											

					$content .= '</div>'.PHP_EOL;
					
				$content .= '</div>'.PHP_EOL;
				
			$content .= '</div>'.PHP_EOL;
			
			return array(
				
				'id' 		=> $modal_id,
				'content' 	=> $content,
			);
		}
		
		return false;
	}
}  
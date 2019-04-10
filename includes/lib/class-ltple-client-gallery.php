<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Gallery {
	
	var $all_sections 	= null;
	var $all_types 		= null;
	var $all_ranges 	= null;
	var $max_num_pages;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		$this->parent->register_taxonomy( 'gallery-section', __( 'Gallery Sections', 'live-template-editor-client' ), __( 'Gallery Section', 'live-template-editor-client' ),  array(), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
			'rewrite' 				=> false,
			'sort' 					=> '',
		));
	}
	
	public function get_meta_key($key){
		
		$keys = array(
			
			'output' => 'layerOutput',
		);
		
		if( !empty($keys[$key]) ){
			
			return $keys[$key];
		}
		
		return false;
	}
	
	public function get_meta_query(){
		
		$meta_query = [];
		
		if( !$this->parent->user->is_editor ){
			
			
		}

		return $meta_query;
	}
	
	public function get_all_types(){
		
		if( is_null($this->all_types) ){
			
			// get all layer types
			
			$meta_query = array();
			
			if( !empty($_REQUEST['layer']) && is_array($_REQUEST['layer']) ){

				$meta = $_REQUEST['layer'];
				
				foreach( $meta as $key => $value ){
					
					$meta_query[] = array(
								
						array(
						
							'key' 		=> $key,
							'value' 	=> $value,
							'compare' 	=> '='
						),
					);			
				}
			}
				
			if( $all_types = get_terms( array(
					
				'taxonomy' 		=> 'layer-type',
				'orderby' 		=> 'count',
				'order' 		=> 'DESC',
				'hide_empty' 	=> true,
				'meta_query' 	=> $meta_query,
			))){
				
				foreach( $all_types as $key => $term ){
				
					$term->visibility = get_option('visibility_'.$term->slug,'anyone');
					
					if( $term->visibility == 'anyone' || $this->parent->user->is_editor ){
						
						$tax_query = array('relation'=>'AND');
						
						$tax_query[0] = array('relation'=>'AND');
						
						$tax_query[0][] = array(
					
							'taxonomy' 	=> $term->taxonomy,
							'terms' 	=> $term,
							'field' 	=> 'slug'
						);

						$tax_query[0][] = array(
					
							'taxonomy' 			=> 'layer-range',
							'operator'			=> 'EXISTS'
						);
						
						$tax_query[1] = array('relation'=>'OR');

						$tax_query[1][] = array(
						
							'taxonomy' 			=> 'user-contact',
							'field' 			=> 'slug',
							'terms' 			=> $this->parent->user->user_email,
							'include_children' 	=> false,
							'operator'			=> 'IN'
						);
						
						$tax_query[1][] = array(
						
							'taxonomy' 			=> 'user-contact',
							'operator'			=> 'NOT EXISTS'
						);
						
						// count posts in term
						
						$q = new WP_Query([
							'posts_per_page' 	=> 0,
							'post_type' 		=> 'cb-default-layer',
							'tax_query' 		=> $tax_query,
						]);
						
						if( $q->found_posts > 0 ){
						
							$term->count = $q->found_posts; // replace term count by real post type count
						}
						else{
							
							unset($all_types[$key]);
						}
					}
					else{
						
						unset($all_types[$key]);
					}
				}
			}
			
			if( !empty($all_types) ){
			
				// order by count
				
				$counts = array();
				
				foreach( $all_types as $key => $type ){
					
					$counts[$key] = $type->count;
				}
				
				array_multisort($counts, SORT_DESC, $all_types);
				
				foreach( $all_types as $type ){
					
					$this->all_types[$type->term_id] = $type;
				}
			}
		}
		
		return $this->all_types;
	}
	
	public function get_all_sections(){
		
		if( is_null($this->all_sections) ){
			
			$this->all_sections = array();
			
			if( $all_types = $this->get_all_types() ){
			
				foreach( $all_types as $term ){
					
					// get section name
					
					$section = 'Templates';
					
					if( $section_id = get_term_meta($term->term_id,'gallery_section',true)){
						
						$sections = $this->parent->layer->get_gallery_sections();
						
						if( !empty($sections[$section_id]) ){
							
							$section = $sections[$section_id]->name;
						}
					}
					
					$this->all_sections[$section][] = $term->term_id;
				}
			}
		}

		return $this->all_sections;
	}
	
	public function get_all_ranges(){
		
		if( is_null($this->all_ranges) ){
			
			$all_ranges = get_terms( array(
					
				'taxonomy' 		=> 'layer-range',
				'orderby' 		=> 'count',
				'order' 		=> 'DESC',
				'hide_empty'	=> true, 
			));
			
			$this->all_ranges = $all_ranges;
		}
		
		return $this->all_ranges;
	}
	
	public function get_type_addon_range($term){
		
		$term_id = 0;
		
		if( is_object($term) && !empty($term->term_id) ){
			
			$term_id = $term->term_id;
		}
		elseif( is_numeric($term) ){
			
			$term_id = intval($term);
		}
		elseif($term = get_term_by('slug',$term,'layer-type')){
			
			$term_id = $term->term_id;
		}
		
		$addon_range = null;
		
		if( $term_id > 0 ){
		
			$id = intval(get_term_meta($term_id,'addon_range',true));
			
			if( $id > 0 ){
				
				$addon_range = get_term_by('id',$id,'layer-range');
			}
		}
		
		return $addon_range;
	}
	
	public function get_type_ranges($layer_type,$addon_range=null){
		
		$ranges = [];
		
		// get layer ranges
		
		$meta_query = $this->get_meta_query();
		
		$tax_query = array('relation'=>'AND');
		
		$tax_query[] = array(
		
			'taxonomy' 			=> 'layer-type',
			'field' 			=> 'slug',
			'terms' 			=> $layer_type,
			'include_children' 	=> false,
			'operator'			=> 'IN'
		);
		
		$tax_query[] = array(
		
			'taxonomy' 			=> 'layer-range',
			'operator'			=> 'EXISTS'
		);
		
		if( !empty($addon_range) ){
			
			$tax_query[] = array(
			
				'taxonomy' 			=> 'layer-range',
				'field' 			=> 'slug',
				'terms' 			=> $addon_range->slug,
				'include_children' 	=> false,
				'operator'			=> 'NOT IN'
			);			
		}
		
		$args = array( 
			'post_type' 		=> 'cb-default-layer', 
			'posts_per_page'	=> -1,
			'fields'		 	=> 'ids',
			'tax_query' 		=> $tax_query,
			'meta_query' 		=> $meta_query,
		);		

		$query = new WP_Query($args);

		if( !empty($query->posts) ){
		
			foreach( $query->posts as $post_id ){
				
				if( $layer_range = wp_get_post_terms( $post_id, 'layer-range' ) ){
					
					foreach( $layer_range as $range ){
						
						if( !isset($ranges[$range->slug]) ){
							
							$ranges[$range->slug]['name'] 	= $range->name;
							$ranges[$range->slug]['slug'] 	= $range->slug;
							$ranges[$range->slug]['count'] 	= 1;
						}
						else{
							
							++$ranges[$range->slug]['count'];
						}
					}					
				}
			}
		}

		if( !empty($addon_range) ){
			
			// get addon range

			$tax_query = array('relation'=>'AND');
			
			$tax_query[] = array(
			
				'taxonomy' 			=> 'user-contact',
				'field' 			=> 'slug',
				'terms' 			=> $this->parent->user->user_email,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$args = array( 
				'post_type' 		=> 'cb-default-layer', 
				'posts_per_page'	=> -1,
				'fields'		 	=> 'ids',
				'tax_query' 		=> $tax_query,
				'meta_query' 		=> $meta_query,
			);		

			$query = new WP_Query($args);
		
			if( !empty($query->posts) ){

				foreach( $query->posts as $post_id ){
					
					if( !isset($ranges[$addon_range->slug]) ){
						
						$ranges[$addon_range->slug]['name'] 	= $addon_range->name;
						$ranges[$addon_range->slug]['slug'] 	= $addon_range->slug;
						$ranges[$addon_range->slug]['count'] 	= 1;
					}
					else{
						
						++$ranges[$addon_range->slug]['count'];
					}
				}
			}
		}

		// sort ranges
		
		if( !empty($ranges) ){
		
			// order by count
			
			$counts = array();
			
			foreach( $ranges as $key => $range ){
				
				$counts[$key] = $range['count'];
			}
			
			array_multisort($counts, SORT_DESC, $ranges);
		}
		
		return $ranges;
	}
	
	public function get_range_items($layer_type,$layer_range,$addon_range=null){
		
		$items =[];
		
		if( !empty($layer_range) ){
			
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				
			$meta_query = $this->get_meta_query();
			
			$tax_query = array('relation'=>'AND');

			$tax_query[] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			if( !empty($addon_range->slug) && $addon_range->slug == $layer_range ){
				
				$tax_query[] = array(
			
					'taxonomy' 			=> 'user-contact',
					'field' 			=> 'slug',
					'terms' 			=> $this->parent->user->user_email,
					'include_children' 	=> false,
					'operator'			=> 'IN'
				);					
			}
			else{
				
				$tax_query[] = array(
				
					'taxonomy' 			=> 'layer-range',
					'field' 			=> 'slug',
					'terms' 			=> $layer_range,
					'include_children' 	=> false,
					'operator'			=> 'IN'
				);				
			}
			
			if( $query = new WP_Query(array( 
			
				'post_type' 	=> 'cb-default-layer', 
				'posts_per_page'=> 15,
				'paged' 		=> $paged,
				'tax_query' 	=> $tax_query,
				'meta_query' 	=> $meta_query,
				
			))){
				
				$this->max_num_pages = $query->max_num_pages;
				
				$all_types = $this->get_all_types($addon_range);
				
				foreach($all_types as $term){
					
					if( $term->slug == $layer_type ){
						
						while ( $query->have_posts() ) : $query->the_post(); 
							
							global $post;
													
							if( $term->visibility == 'anyone' || $this->parent->user->is_editor ){
								
								//get layer_range
								
								$layer_range = null;
								
								$terms = wp_get_object_terms( $post->ID, 'layer-range' );
								
								if(!empty($terms[0]->slug)){
									
									$layer_range = $terms[0]->slug;
								}				
								
								if( !empty($layer_range) ){
								
									//get item
									
									$item = $this->get_item($post);

									//merge item
									
									$items[$layer_range][]=$item;
								}
							}
							
						endwhile; wp_reset_query();						
					}
				}
			}
		}

		return $items;		
	}
	
	public function get_item($post){
							
		$item = '';
		
		if( !empty($post) ){
			
			$permalink = get_permalink($post) . '?preview';

			//get editor_url

			$editor_url = $this->parent->urls->editor . '?uri='.$post->ID;
		
			//get post_title
			
			$post_title = the_title('','',false);
			
			// get item

			$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$post->ID) ) . '" id="post-' . $post->ID . '">';
				
				$item.='<div class="panel panel-default">';

					$item.='<div class="thumb_wrapper" style="background:url(' . $this->parent->layer->get_thumbnail_url($post) . ');background-size:cover;background-repeat:no-repeat;background-position:top center;"></div>'; //thumb_wrapper					
					
					$item.='<div class="panel-body">';
						
						$item.= apply_filters('ltple_gallery_item_title','<b>' . $post_title . '</b>',$post);
						 
					$item.='</div>';
					
					$item.='<div style="background:#fff;border:none;" class="panel-footer text-right">';
						
						if( $this->parent->inWidget === true ){
							
							if($this->parent->plan->user_has_layer( $post->ID ) === true){
								
								$item.='<a target="_blank" class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
							}
							else{
								
								$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
							
									$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
						
								$item.='</button>'.PHP_EOL;
							}												
						}
						else{
							
							// info button
							
							$item.='<a class="btn btn-sm btn-info" style="margin-right:4px;" href="'. $this->parent->urls->product . $post->ID . '/" title="More info about '. $post_title .' template">Info</a>';
						
							// preview button
							
							$modal_id='modal_'.md5($permalink);
							
							$item.='<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
								
								$item.='Preview'.PHP_EOL;
							
							$item.='</button>'.PHP_EOL;

							$item.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
								
								$item.='<div class="modal-dialog modal-lg" style="width:95% !important;" role="document">'.PHP_EOL;
									
									$item.='<div class="modal-content">'.PHP_EOL;
									
										$item.='<div class="modal-header">'.PHP_EOL;
											
											$item.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											$item.='<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
										
										$item.='</div>'.PHP_EOL;
									  
										$item.='<div class="modal-body">'.PHP_EOL;
											
											if( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $post->ID ) === true ){
												
												$item.= '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

												$item.= '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 450px;overflow: hidden;"></iframe>';											
											}
											else{
												
												$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
											}

										$item.='</div>'.PHP_EOL;

										$item.='<div class="modal-footer">'.PHP_EOL;
										
											if($this->parent->user->loggedin){

												$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
											}
											else{
												
												$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
												
													$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
											
												$item.='</button>'.PHP_EOL;								
											}
											
										$item.='</div>'.PHP_EOL;
									  
									$item.='</div>'.PHP_EOL;
									
								$item.='</div>'.PHP_EOL;
								
							$item.='</div>'.PHP_EOL;						
						
							if($this->parent->user->loggedin){
								
								if($this->parent->plan->user_has_layer( $post->ID ) === true){
									
									$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
								}
								else{
									
									$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
								
										$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
							
									$item.='</button>'.PHP_EOL;
								}
							}
							else{
								
								$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
								
									$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
							
								$item.='</button>'.PHP_EOL;								
							}
						}
						
					$item.='</div>';
				
				$item.='</div>';
				
			$item.='</div>';
		}

		return $item;
	}
	
	/**
	 * Main LTPLE_Client_Gallery Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Gallery is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Gallery instance
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

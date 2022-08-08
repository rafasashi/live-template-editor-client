<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Gallery {
	
	var $all_sections 	= null;
	var $current_types 	= null;
	var $all_ranges 	= null;
	var $per_page 		= 50;
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
		
		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-template/v1', '/list', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_gallery_items'),
				'permission_callback' => '__return_true',
			) );
			
		} );
		
		add_filter( 'ltple_gallery_item_title', array( $this, 'filter_gallery_item_title' ),10,2);
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

	public function get_current_types(){
		
		if( is_null($this->current_types) ){
			
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
			
			$current_types = array();
			
			if( $ids = get_terms( array(
					
				'taxonomy' 		=> 'layer-type',
				'orderby' 		=> 'count',
				'order' 		=> 'DESC',
				'hide_empty' 	=> true,
				'meta_query' 	=> $meta_query,
				'fields'		=> 'ids'
			))){
				
				$layer_types = $this->parent->layer->get_layer_types();
				
				foreach( $ids as $id ){
					
					if( !isset($layer_types[$id]) )
						continue;
					
					$term = $layer_types[$id];
					
					if( $term->visibility == 'anyone' || $this->parent->user->can_edit ){
						
						$tax_query = array('relation'=>'AND');
						
						$tax_query[] = array(
					
							'taxonomy' 	=> $term->taxonomy,
							'terms' 	=> $term->slug,
							'field' 	=> 'slug'
						);
						
						$tax_query[] = array(
					
							'taxonomy' 			=> 'layer-range',
							'operator'			=> 'EXISTS'
						);
						
						if( !empty($term->addon) ){
			
							$tax_query[] = array(
							
								'taxonomy' 			=> 'layer-range',
								'field' 			=> 'slug',
								'terms' 			=> $term->addon->slug,
								'include_children' 	=> false,
								'operator'			=> 'NOT IN'
							);			
						}
						
						// count posts in term
						
						$q = new WP_Query([
							'posts_per_page' 	=> 1,
							'post_type' 		=> 'cb-default-layer',
							'tax_query' 		=> $tax_query,
							'no_found_rows'		=> false,
						]);
						
						if( $q->found_posts > 0 ){
						
							$term->count = $q->found_posts; // replace term count by real post type count
						
							$current_types[] = $term;
						}
					}
				}
			}
			
			if( !empty($current_types) ){
			
				// order by count
				
				$counts = array();
				
				foreach( $current_types as $key => $type ){
					
					$counts[$key] = $type->count;
				}
				
				array_multisort($counts, SORT_DESC, $current_types);
				
				foreach( $current_types as $type ){
					
					$this->current_types[$type->term_id] = $type;
				}
			}
		}
		
		return $this->current_types;
	}
	
	public function get_all_sections(){
		
		if( is_null($this->all_sections) ){
			
			$this->all_sections = array();
			
			if( $current_types = $this->get_current_types() ){
			
				foreach( $current_types as $term ){
					
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
	 
	public function get_badge_count($count){
		
		//return $count;
		
		if( $round_count = round($count,-1,PHP_ROUND_HALF_DOWN) ){
			
			if( $round_count > $count ){
				
				$round_count -= 5;
			}
		}
		
		if( $round_count > 0 ){
			
			if( $round_count > 1000 ) {

				$x = round($round_count);
				$x_number_format = number_format($x);
				$x_array = explode(',', $x_number_format);
				$x_parts = array('k', 'm', 'b', 't');
				$x_count_parts = count($x_array) - 1;
				
				$round_count = $x;
				$round_count = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
				$round_count .= $x_parts[$x_count_parts - 1];
			}
			
			$count = $round_count;
		
			if( is_numeric($round_count) ){
				
				$count .= '+';
			}
		}
		
		return $count;
	}
	
	public function get_range_items($layer_type,$layer_range,$referer){

		$items =[];
		
		if( !empty($layer_range) ){
			
			$addon = !empty($layer_type->addon) ? $layer_type->addon : null; 

			$tax_query = array('relation'=>'AND');
			
			$tax_query[0] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type->slug,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$tax_query[1] = array('relation'=>'AND'); // important to keep AND for addons
			
			$tax_query[1][] = array(
			
				'taxonomy' 			=> 'layer-range',
				'field' 			=> 'slug',
				'terms' 			=> $layer_range,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);			
			
			if( !empty($addon->slug) && $addon->slug == $layer_range ){

				$tax_query[1][] = array(
			
					'taxonomy' 			=> 'user-contact',
					'field' 			=> 'slug',
					'terms' 			=> $this->parent->user->user_email,
					'include_children' 	=> false,
					'operator'			=> 'IN'
				);					
			}
			
			$args = array( 
			
				'post_type' 	=> 'cb-default-layer', 
				'tax_query' 	=> apply_filters('ltple_gallery_' . $layer_type->storage . '_tax_query',$tax_query),
				'orderby' 		=> apply_filters('ltple_gallery_' . $layer_type->storage . '_orderby',array('date'=>'DESC')),
				'posts_per_page'=> $this->per_page,
				'paged'			=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 ),
			);
			
			if( !empty($_GET['s']) ){
				
				$args['s'] = $_GET['s'];
			}
			
			$args = apply_filters('ltple_gallery_' . $layer_type->storage . '_query',$args);
			
			if( $query = new WP_Query($args)){
				
				$this->max_num_pages = $query->max_num_pages;
				
				$current_types = $this->get_current_types($addon);
				
				foreach($current_types as $term){
					
					if( $term->slug == $layer_type->slug ){
						
						while ( $query->have_posts() ) : $query->the_post(); 
							
							global $post;
												
							if( $term->visibility == 'anyone' || $this->parent->user->can_edit ){
								
								//get item
								
								$item = $this->get_item($post,$layer_type,$referer);

								//merge item
								
								$items[]=$item;
							}
							
						endwhile; wp_reset_query();						
					}
				}
			}
		}

		return $items;		
	}
	
	function get_gallery_items( $rest = null ){
		
		//get user images
		
		$items = [];
		
		$referer = $rest->get_header( 'referer' );
		
		$gallery = (!empty($_GET['gallery']) ? sanitize_title($_GET['gallery']) : false );
		
		if( $layer_type = $this->get_layer_type_info($gallery) ){
			
			//get layer range
			
			$layer_range = ( !empty($_GET['range']) ? sanitize_title($_GET['range']) : key($layer_type->ranges) );
			
			// get gallery items 
			
			if( $range_items = $this->get_range_items($layer_type,$layer_range,$referer) ){
				
				//get layer range name
				
				$layer_range_name = ( !empty($layer_type->ranges[$layer_range]['name']) ? $layer_type->ranges[$layer_range]['name'] : '' );
								
				$options = array(
					
					$layer_range
				);
				
				$is_addon = !empty($layer_type->addon->slug) && $layer_type->addon->slug == $layer_range ? true : false;
				
				$has_options = $this->parent->plan->user_has_options( $options );
				
				$plans = $this->parent->plan->get_plans_by_options( $options );
				
				if( !$is_addon && !$has_options && !empty($plans) && ( empty($this->parent->user->plan['holder']) || $this->parent->user->plan['holder'] == $this->parent->user->ID ) ){

					$item ='<div class="panel panel-default bs-callout bs-callout-primary" style="min-height:315px;margin:0px;padding:7%;border:none !important;">';
						
						$item .='<div style="padding-bottom:35px;">';
						
							$item .='<h4 style="margin-bottom:10px;">' . ucfirst($layer_type->name) .  ' > ' . ucfirst($layer_range_name) .  '</h4>';
							
							$item .='<p style="line-height:30px;">';
							
								if( $has_options === true ){
									
									$item .='Edit any template from ' . ucfirst($layer_range_name) .  ' gallery';
								}
								elseif( !empty($plans) ){
									
									$item .='You need the <span class="label label-success">'.$plans[0]['title'].'</span> plan'.( count($plans) > 1 ? ' or higher ' : ' ').'to <span class="label label-default">unlock all</span> the items from this gallery';
								}
								else{
								
									$item .='No plan available to unlock this gallery';
								}
							
							$item .='</p>';
						
						$item .='</div>';
														
						$item .='<div>';
										
							$item .='<button type="button" class="btn btn-sm" data-toggle="modal" data-target="#upgrade_plan" style="width:100%;font-size:17px;background:' . $this->parent->settings->mainColor . '99;color:#fff;padding: 15px 0;border:1px solid ' . $this->parent->settings->mainColor . ';">';
							
								$item .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> ' . ( !empty($this->parent->user->plan) && $this->parent->user->plan['info']['total_price_amount'] > 0 ? 'upgrade' : 'start' );
								
								$item .= '<br>';
								
								$item .= '<span style="font-size:10px;">from '.$plans[0]['price_tag'].'</span>';
								
							$item .='</button>';

						$item .='</div>';

						
					$item .='</div>';
					
					$items[] = array(
						
						'item' => $item,
					);						
				}
			
				foreach( $range_items as $item ){
					
					$items[] = array(
						
						'item' => $item,
					);
				}
			}
		}

		return $items;		
	}
	
	public function get_layer_type_info( $slug = false ){
		
		$layer_type = null;
		
		if( $current_types = $this->get_current_types() ){
		
			foreach($current_types as $term){
				
				if( !$slug ){
				
					if( $term->visibility == 'anyone' || $this->parent->user->can_edit ){
						
						$layer_type = $term;
						
						break; 
					}
				}
				elseif( $slug ==  $term->slug ){
					
					$layer_type = $term;
						
					break; 
				}
			}
		}

		return 	$layer_type;	
	}
	
	public function get_item($post,$layer_type,$referer=null){
		
		$item = '';
		
		if( !empty($post) ){
			
			// get info url
			
			$info_url = get_permalink($post);
			
			// get preview url
			
			$preview_url = $this->parent->urls->home . '/preview/' . $post->post_name . '/';
			
			//get editor_url

			$editor_url = $this->parent->urls->edit . '?uri='.$post->ID;
		
			//get post_title
			
			$post_title = the_title('','',false);
			
			// get item

			$item.='<div class="' . implode( ' ', get_post_class('',$post->ID) ) . '" id="post-' . $post->ID . '">';
				
				$item.='<div class="panel panel-default">';

					$item.='<div class="thumb_wrapper" style="background:url(' . $this->parent->layer->get_thumbnail_url($post) . ');background-size:cover;background-repeat:no-repeat;background-position:center center;"></div>'; //thumb_wrapper					
					
					$item.='<div class="panel-body" style="padding-bottom:0;position:relative;">';
						
						$item.= apply_filters('ltple_gallery_item_title','<b>' . $post_title . '</b>',$post);
						 
					$item.='</div>';
					
					$item.='<div style="background:#fff;border:none;" class="panel-footer text-right">';
						
						if( $this->parent->inWidget === true ){
							
							if( $this->parent->plan->user_has_layer( $post ) === true ){
								
								$action = '<a target="_parent" class="btn btn-sm btn-success" href="'. $editor_url .'" title="Start editing this template">Start</a>';
							}
							elseif( $this->parent->user->plan['holder'] == $this->parent->user->ID ){
								
								$action =  '<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
							
									$action .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL; 
						
								$action .= '</button>'.PHP_EOL;
							}
							
							$item.= apply_filters('ltple_widget_gallery_item_action',$action,$post,$referer);
						}
						else{
							
							// get visibility
			
							$visibility = $this->parent->layer->get_layer_visibility($post);
							
							$show_preview = ( $visibility == 'anyone' || $visibility == 'registered' || ( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $post ) === true )) ? true : false;
							
							// info button
							
							$item.='<a target="_parent" class="btn btn-sm btn-info" style="margin-right:4px;" href="'. $info_url . '" title="More info about '. $post_title .' template">Info</a>';
							
							// preview button
							
							$modal_id='modal_'.md5($preview_url);
							
							$item.='<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
								
								$item.='Preview'.PHP_EOL;
							
							$item.='</button>'.PHP_EOL;

							$item.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
								
								$item.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
									
									$item.='<div class="modal-content">'.PHP_EOL;
									
										$item.='<div class="modal-header">'.PHP_EOL;
											
											$item.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											$item.='<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
										
										$item.='</div>'.PHP_EOL;
										
										if( $show_preview === true ){
											
											$item.= '<iframe data-src="'.$preview_url.'" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 110px);overflow: hidden;"></iframe>';											
										}
										elseif( $image = get_the_post_thumbnail($post->ID, 'full') ){
											
											$item.= '<div class="modal-image-wrapper" style="width:100%;position:relative;bottom:0;border:0;height:calc( 100vh - 110px);overflow: auto;">';
											
												$item.= $image;
											
											$item.= '</div>';
										}
										else{
											
											$item.= '<div class="modal-image-wrapper" style="width:100%;position:relative;bottom:0;border:0;height:calc( 100vh - 110px);overflow: auto;">';

												$item.= '<img loading="lazy" src="' . $this->parent->layer->get_thumbnail_url($post) . '">';
												
											$item.= '</div>';
										}


										$item.='<div class="modal-footer">'.PHP_EOL;
										
											// get actions
			
											if( $this->parent->user->loggedin ){

												$actions ='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Start editing this template">Start</a>';
											}
											else{
												
												$actions ='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
												
													$actions.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
											
												$actions.='</button>'.PHP_EOL;								
											}
											
											$item.= apply_filters('ltple_layer_preview_actions',$actions,$post,$show_preview);
										
										$item.='</div>'.PHP_EOL;
									  
									$item.='</div>'.PHP_EOL;
									
								$item.='</div>'.PHP_EOL;
								
							$item.='</div>'.PHP_EOL;

							if($this->parent->plan->user_has_layer( $post ) === true){
								
								$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_parent" title="Start editing this template">Start</a>';
							}
							elseif( empty($this->parent->user->ID) || ( !empty($this->parent->user->plan['holder']) && $this->parent->user->plan['holder'] == $this->parent->user->ID ) ){
								
								$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
							
									$item.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
						
								$item.='</button>'.PHP_EOL;
							}
						}
						
					$item.='</div>';
				
				$item.='</div>';
				
			$item.='</div>';
		}

		return $item;
	}
	
	public function get_gallery_table(){

		//output Tab panes
		  
		echo'<div class="tab-content" style="margin-top:20px;">';
			
			echo'<div role="tabpanel" class="tab-pane active" id="gallery">';
				
				// get table fields
				
				echo'<div class="row" style="margin:-20px 0px -15px 0px;">';
					
					$fields = array(
						
						array(

							'field' 	=> 'item',
							'sortable' 	=> 'false',
							'content' 	=> '',
						),					
					);
				
					// get table of results
					
					$api_url = $this->parent->urls->api . 'ltple-template/v1/list/';
					
					if( !empty($_REQUEST) ){
						
						$api_url .= '?' . http_build_query($_REQUEST, '', '&amp;');
					}
					
					$this->parent->api->get_table(
					
						$api_url, 
						$fields, 
						$trash		= false,
						$export		= false,
						$search		= true,
						$toggle		= false,
						$columns	= false,
						$header		= true,
						$pagination	= 'scroll',
						$form		= false,
						$toolbar 	= 'toolbar',
						$card		= true,
						$itemHeight	= 335, 
						$fixedHeight= true, 
						$echo		= true,
						$pageSize	= $this->per_page
						
					);

				echo'</div>';
				
			echo'</div>';
					
		echo'</div>';		
	}
	
	public function filter_gallery_item_title($content,$post){
		
		$nickname = get_the_author_meta( 'nickname', $post->post_author );
			
		$item_title='<a href="' . $this->parent->profile->get_user_url($post->post_author) . '" style="position:absolute;top:-25px;">';
			
			$item_title.='<img loading="lazy" src="'.$this->parent->image->get_avatar_url($post->post_author).'" style="height:50px;width:50px;border: 5px solid #fff;background:#fff;border-radius:250px;">';
			
		$item_title.='</a>';
		
		$item_title.='<div class="gallery-item" style="margin-top:10px;line-height:25px;height:30px;overflow:hidden;font-size:15px;font-weight:bold;">';
		
			$item_title.= $content;
		
		$item_title.='</div>';
		
		if( !empty($nickname) ){
		
			$item_title.='<div style="font-size: 11px;">';
				
				if( $this->parent->inWidget === true ){
					
					$item_title.='by <span>' . $nickname . '</span>';
				}
				else{
					
					$item_title.='by <a href="' . $this->parent->profile->get_user_url($post->post_author) . '/">' . $nickname . '</a>';
				}
			
			$item_title.='</div>';
		}
		
		return $item_title;
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

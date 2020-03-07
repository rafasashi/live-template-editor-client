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
			'show_ui' 				=> false,
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
	
	public function get_meta_query($addon_range){
		
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
						
						$tax_query = array('relation'=>'OR');
						
						$tax_query[0][] = array(
					
							'taxonomy' 	=> $term->taxonomy,
							'terms' 	=> $term,
							'field' 	=> 'slug'
						);
						
						$tax_query[0][] = array(
					
							'taxonomy' 			=> 'layer-range',
							'operator'			=> 'EXISTS'
						);
						
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
	
	public function get_type_ranges($layer_type,$addon_range=null){
		
		$ranges = [];
		
		// get layer ranges
		
		$meta_query = $this->get_meta_query($addon_range);
		
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
		
		$query = new WP_Query( array( 
			
			'post_type' 		=> 'cb-default-layer', 
			'posts_per_page'	=> -1,
			'fields'		 	=> 'ids',
			'tax_query' 		=> $tax_query,
			'meta_query' 		=> $meta_query,
		
		));

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
						
						$ranges[$range->slug]['ids'][] = $post_id;
					}					
				}
			}
		}

		if( !empty($addon_range) ){
			
			// get addon range

			$tax_query = array('relation'=>'AND');

			$tax_query[] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$tax_query[] = array(
			
				'taxonomy' 			=> 'user-contact',
				'field' 			=> 'slug',
				'terms' 			=> $this->parent->user->user_email,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$query = new WP_Query( array( 
				
				'post_type' 		=> 'cb-default-layer', 
				'posts_per_page'	=> -1,
				'fields'		 	=> 'ids',
				'tax_query' 		=> $tax_query,
				'meta_query' 		=> $meta_query,
			
			));
		
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
					
					$ranges[$addon_range->slug]['ids'][] = $post_id;
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
	
	public function get_range_items($layer_type,$layer_range,$addon_range=null,$paginated=true,$referer){
		
		$items =[];
		
		if( !empty($layer_range) ){
			
			$meta_query = $this->get_meta_query($addon_range);
			
			$tax_query = array('relation'=>'AND');

			$tax_query[0] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type->slug,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);
			
			$tax_query[1] = array('relation'=>'OR');
			
			$tax_query[1] = array(
			
				'taxonomy' 			=> 'layer-range',
				'field' 			=> 'slug',
				'terms' 			=> $layer_range,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);			
			
			if( !empty($addon_range->slug) && $addon_range->slug == $layer_range ){

				$tax_query[1] = array(
			
					'taxonomy' 			=> 'user-contact',
					'field' 			=> 'slug',
					'terms' 			=> $this->parent->user->user_email,
					'include_children' 	=> false,
					'operator'			=> 'IN'
				);					
			}

			$args = array( 
			
				'post_type' 	=> 'cb-default-layer', 
				'tax_query' 	=> $tax_query,
				'meta_query' 	=> $meta_query,
			);
			
			//dump($args);

			if( $paginated ){
				
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['paged']) ? intval($_GET['paged']) : 1 );
				
				$args['posts_per_page'] = 20;
				$args['paged'] 			= $paged;
			}
			else{
				
				$args['posts_per_page'] = -1;
			}

			if( $query = new WP_Query($args)){
				
				$this->max_num_pages = $query->max_num_pages;
				
				$all_types = $this->get_all_types($addon_range);
			
				foreach($all_types as $term){
					
					if( $term->slug == $layer_type->slug ){
						
						while ( $query->have_posts() ) : $query->the_post(); 
							
							global $post;
													
							if( $term->visibility == 'anyone' || $this->parent->user->is_editor ){
								
								//get item
								
								$item = $this->get_item($post,$layer_type,$referer);

								//merge item
								
								$items[$layer_range][]=$item;
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

		if( $layer_type = $this->get_layer_type_info((!empty($_GET['gallery']) ? $_GET['gallery'] : false )) ){
			
			//get layer range
			
			$layer_range = ( !empty($_GET['range']) ? $_GET['range'] : key($layer_type->ranges) );
			
			//get layer range name
			
			$layer_range_name = ( !empty($layer_type->ranges[$layer_range]['name']) ? $layer_type->ranges[$layer_range]['name'] : '' );
			
			// get gallery items 
			
			$range_items = $this->get_range_items($layer_type,$layer_range,$layer_type->addon,false,$referer);
			
			if( !empty($range_items[$layer_range]) ){
				
				$this->parent->plan->options = array($layer_range);
								
				$has_options = $this->parent->plan->user_has_options($this->parent->plan->options);
				
				$plans = $this->parent->plan->get_plans_by_options( $this->parent->plan->options );
				
				if( !$has_options && !empty($plans) && $this->parent->user->plan['holder'] == $this->parent->user->ID ){

					$item ='<div class="panel panel-default bs-callout bs-callout-primary" style="min-height:287px;margin:0px;padding:7%;border:none !important;">';
						
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
										
							$item .='<button type="button" class="btn btn-sm" data-toggle="modal" data-target="'.( $this->parent->user->loggedin  === true ? '#upgrade_plan' : '#login_first').'" style="width:100%;font-size:17px;background:' . $this->parent->settings->mainColor . '99;color:#fff;padding: 15px 0;border:1px solid ' . $this->parent->settings->mainColor . ';">';
							
								$item .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> ' . ( $this->parent->user->plan['info']['total_price_amount'] > 0 ? 'upgrade' : 'start' );
								
								$item .= '<br>';
								
								$item .= '<span style="font-size:10px;">from '.$plans[0]['price_tag'].'</span>';
								
							$item .='</button>';

						$item .='</div>';

						
					$item .='</div>';
					
					$items[] = array(
						
						'item' => $item,
					);						
				}
			
				foreach( $range_items[$layer_range] as $item ){
					
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
		
		if( $all_types = $this->get_all_types() ){
		
			if( !$slug ){
				
				foreach($all_types as $term){
								
					if( $term->visibility == 'anyone' || $this->parent->user->is_editor ){
						
						$layer_type = $term;
						
						break; 
					}
				}		
			}
			else{
				
				$layer_type = get_term_by('slug',$slug,'layer-type');
			}
		}
		
		if( !empty($layer_type) ){

			//get addon range

			$layer_type->addon = $this->parent->layer->get_type_addon_range($layer_type);

			//get item ranges
			
			$layer_type->ranges = $this->get_type_ranges($layer_type->slug,$layer_type->addon);	
			
			//get item output
			
			$layer_type->output = $this->parent->layer->get_type_output($layer_type);
		}

		return 	$layer_type;	
	}
	
	public function get_item($post,$layer_type,$referer=null){
		
		$item = '';
		
		if( !empty($post) ){
			
			$permalink 	= $this->parent->urls->home . '/preview/' . $post->post_name . '/';

			//get editor_url

			$editor_url = $this->parent->urls->edit . '?uri='.$post->ID;
		
			//get post_title
			
			$post_title = the_title('','',false);
			
			// get item

			$item.='<div class="' . implode( ' ', get_post_class('',$post->ID) ) . '" id="post-' . $post->ID . '">';
				
				$item.='<div class="panel panel-default">';

					$item.='<div class="thumb_wrapper" style="background:url(' . $this->parent->layer->get_thumbnail_url($post) . ');background-size:cover;background-repeat:no-repeat;background-position:center center;"></div>'; //thumb_wrapper					
					
					$item.='<div class="panel-body">';
						
						$item.= apply_filters('ltple_gallery_item_title','<b>' . $post_title . '</b>',$post);
						 
					$item.='</div>';
					
					$item.='<div style="background:#fff;border:none;" class="panel-footer text-right">';
						
						if( $this->parent->inWidget === true ){
							
							if( $this->parent->plan->user_has_layer( $post ) === true ){
								
								$action = '<a target="_parent" class="btn btn-sm btn-success" href="'. $editor_url .'" title="Start editting this template">Start</a>';
							}
							elseif( $this->parent->user->plan['holder'] == $this->parent->user->ID ){
								
								$action =  '<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
							
									$action .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Buy'.PHP_EOL; 
						
								$action .= '</button>'.PHP_EOL;
							}
							
							$item.= apply_filters('ltple_widget_gallery_item_action',$action,$post,$referer);
						}
						else{
							
							// info button
							
							$item.='<a target="_parent" class="btn btn-sm btn-info" style="margin-right:4px;" href="'. $this->parent->urls->product . $post->ID . '/" title="More info about '. $post_title .' template">Info</a>';
							
							// preview button
							
							$modal_id='modal_'.md5($permalink);
							
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
									  
										$item.='<div class="modal-body">'.PHP_EOL;
											
											if( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $post ) === true ){
												
												$item.= '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

												$item.= '<iframe data-src="'.$permalink.'" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 145px);overflow: hidden;"></iframe>';											
											}
											else{
												
												$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
											}

										$item.='</div>'.PHP_EOL;

										$item.='<div class="modal-footer">'.PHP_EOL;
										
											if( $this->parent->user->loggedin ){

												$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Start editting this template">Start</a>';
											}
											else{
												
												$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
												
													$item.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Buy'.PHP_EOL;
											
												$item.='</button>'.PHP_EOL;								
											}
											
										$item.='</div>'.PHP_EOL;
									  
									$item.='</div>'.PHP_EOL;
									
								$item.='</div>'.PHP_EOL;
								
							$item.='</div>'.PHP_EOL;

						
							if($this->parent->user->loggedin){
								
								if($this->parent->plan->user_has_layer( $post ) === true){
									
									$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_parent" title="Start editting this template">Start</a>';
								}
								elseif( $this->parent->user->plan['holder'] == $this->parent->user->ID ){
									
									$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#upgrade_plan">'.PHP_EOL;
								
										$item.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Buy'.PHP_EOL;
							
									$item.='</button>'.PHP_EOL;
								}
							}
							else{
								
								$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
								
									$item.='<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Buy'.PHP_EOL;
							
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
				
				echo'<div class="row" style="margin:-20px -15px 0px -15px;">';
					
					$fields = array(
						
						array(

							'field' 	=> 'item',
							'sortable' 	=> 'false',
							'content' 	=> '',
						),					
					);
				
					// get table of results

					$this->parent->api->get_table(
					
						$this->parent->urls->api . 'ltple-template/v1/list?' . http_build_query($_REQUEST, '', '&amp;'), 
						$fields, 
						$trash		= false,
						$export		= false,
						$search		= true,
						$toggle		= false,
						$columns	= false,
						$header		= true,
						$pagination	= true,
						$form		= false,
						$toolbar 	= 'toolbar',
						$card		= true,
						$itemHeight	= 300
					);

				echo'</div>';
				
			echo'</div>';
					
		echo'</div>';		
	}
	
	public function filter_gallery_item_title($content,$post){
		
		$nickname = get_the_author_meta( 'nickname', $post->post_author );
			
		$item_title='<a href="' . $this->parent->urls->profile . $post->post_author . '/" style="position: absolute;top: 145px;">';
			
			$item_title.='<img src="'.$this->parent->image->get_avatar_url($post->post_author).'" style="height:50px;width:50px;border: 5px solid #fff;background:#fff;border-radius:250px;">';
			
		$item_title.='</a>';
		
		$item_title.='<div style="margin-top:10px;">';
		
			$item_title.= $content;
		
		$item_title.='</div>';
		
		$item_title.='<div style="font-size: 11px;">';
			
			if( $this->parent->inWidget === true ){
				
				$item_title.='by <span>' . $nickname . '</span>';
			}
			else{
				
				$item_title.='by <a href="' . $this->parent->urls->profile . $post->post_author . '/">' . $nickname . '</a>';
			}
		
		$item_title.='</div>';
		
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

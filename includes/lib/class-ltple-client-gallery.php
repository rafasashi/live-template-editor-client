<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Gallery extends LTPLE_Client_Object {
	
	var $all_sections 	= null;
	var $current_types 	= null;
	var $all_ranges 	= null;
	var $per_page 		= 30;
	var $max_num_pages;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		$this->parent->register_taxonomy( 'gallery-section','Gallery Sections','Gallery Section',  array(), array(
			
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
        
		$this->parent->register_taxonomy( 'image-gallery','Galleries','Image Gallery',  array('attachment'), array(
			
            'hierarchical' 			=> false,
			'public' 				=> true,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> array('slug'=>'images'),
			'sort' 					=> '',
		));
             
        add_action('pre_get_posts', function( $query ) {
            
            if ( !is_admin() && $query->is_main_query() && is_tax( 'image-gallery' ) ) {
                
                $query->set('post_status','inherit');
                
                $query->set('posts_per_page', 20);
                
				add_filter('ltple_css_framework',function($framework){
					
					return 'bootstrap-5';
					
				},99999999,1);
            }
        });
        
		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-template/v1', '/list/', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_gallery_items'),
				'permission_callback' => '__return_true',
			) );
			
		} );
				
		add_action( 'ltple_urls', array( $this, 'init_navbar'));

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
	
	public function get_current_type(){
		
		if( !empty($_GET['gallery']) ){
			
			$layer_type = $this->get_layer_type_info(sanitize_title($_GET['gallery']));
		}
		elseif( $default_range = intval(get_option('ltple_default_range_id',false)) ){
			
			$current_types = $this->get_current_types();
			
			foreach($current_types as $type){
				
				foreach( $type->ranges as $range ){
					
					if( $range['term_id'] == $default_range ){
						
						$layer_type = $type;
						
						break;
					}
				}
			}
		}
		else{
			
			$layer_type = $this->get_layer_type_info(false);
		}
		
		return $layer_type;
	}
	 
	public function get_current_range(){
		
		$range = !empty($_GET['range']) ? sanitize_title($_GET['range']) : null;
	
        if( $range == 'all' ){
            
            $range = null;
        }
    
        return $range;
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
    
    public function get_query_args($layer_type,$layer_range,$user,$search,$per_page,$page=1){
        
        if( !empty($layer_type) ){
            
            $addon = !empty($layer_type->addon) ? $layer_type->addon : null; 

			if( !empty($layer_range) ){
				
				$tax_query = array('relation'=>'AND'); // important to keep AND for addons
				
				$tax_query[] = array(
				
					'taxonomy' 			=> 'layer-range',
					'field' 			=> 'slug',
					'terms' 			=> $layer_range,
					'include_children' 	=> false,
					'operator'			=> 'IN'
				);			
				
				if( !empty($addon->slug) && $addon->slug == $layer_range ){

					$tax_query[] = array(
				
						'taxonomy' 			=> 'user-contact',
						'field' 			=> 'slug',
						'terms' 			=> $user->user_email,
						'include_children' 	=> false,
						'operator'			=> 'IN'
					);					
				}
			}
			elseif( $layer_ranges = $this->parent->layer->get_type_ranges($layer_type,$addon) ){
				
                $tax_query = array('relation'=>'AND');
                    
                $range_ids = array();
                
                foreach( $layer_ranges as $layer_range ){
                    
                    $range_ids[] = $layer_range['term_id'];
                }
                
                $tax_query[0][] = array(
                
                    'taxonomy' 			=> 'layer-range',
                    'field' 			=> 'term_id',
                    'include_children' 	=> false,
                    'operator'			=> 'IN',
                    'terms' 			=> $range_ids,
                );

				if( !empty($addon->slug) ){
					
                    $tax_query[1] = array('relation'=>'OR');
                    
                    $tax_query[1][] = array(
				
                        'taxonomy' 			=> 'layer-range',
                        'field' 			=> 'slug',
                        'terms' 			=> array($addon->slug),
                        'include_children' 	=> false,
                        'operator'			=> 'NOT IN'
                    );			
				
					$tax_query[1][] = array(
				
						'taxonomy' 			=> 'user-contact',
						'field' 			=> 'slug',
						'terms' 			=> array($user->user_email),
						'include_children' 	=> false,
						'operator'			=> 'IN'
					);
				}
			}
            else{
                
                return false;
            }
			
			$args = array( 
			
				'post_type' 	=> 'cb-default-layer', 
				'tax_query' 	=> apply_filters('ltple_gallery_' . $layer_type->storage . '_tax_query',$tax_query),
				'orderby' 		=> apply_filters('ltple_gallery_' . $layer_type->storage . '_orderby',array('date'=>'DESC')),
				'posts_per_page'=> $per_page,
                'no_found_rows' => true,
				'paged'			=> $page,
			);
			
			if( !empty($search) ){
				
				$args['s'] = $search;
			}
			
			return apply_filters('ltple_gallery_' . $layer_type->storage . '_query',$args);
        }
    }
    
	public function get_range_items($layer_type,$layer_range,$referer){

		$items =[];

        $search = !empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        $page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 );
        
		if( $args = $this->get_query_args($layer_type,$layer_range,$this->parent->user,$search,$this->per_page,$page) ){
			
			if( $query = new WP_Query($args)){
				
				$this->max_num_pages = $query->max_num_pages;
				
				$current_types = $this->get_current_types();
				
				foreach( $current_types as $term ){
					
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
		
		if( $layer_type = $this->get_current_type() ){
			
			//get layer range
			
			$layer_range = $this->get_current_range();
			
			// get gallery items 
			
            if( $range_items = $this->get_range_items($layer_type,$layer_range,$referer) ){
				
				if( !empty($layer_range) ){
					
					//get layer range name
					
					$layer_range_name = ( !empty($layer_type->ranges[$layer_range]['name']) ? $layer_type->ranges[$layer_range]['name'] : '' );
									
					$options = array(
						
						$layer_range
					);
					
					$is_addon = !empty($layer_type->addon->slug) && $layer_type->addon->slug == $layer_range ? true : false;
					
					$has_options = $this->parent->plan->user_has_options( $options );
					
					$plans = $this->parent->plan->get_plans_by_options( $options );
					
					if( !$is_addon && !$has_options && !empty($plans) && ( empty($this->parent->user->plan['holder']) || $this->parent->user->plan['holder'] == $this->parent->user->ID ) ){

						$item ='<div class="panel panel-default bs-callout bs-callout-primary" style="min-height:284px;margin:0px;padding:7%;border:none !important;">';
							
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
											
								$item .='<button type="button" class="btn btn-sm" data-toggle="modal" data-target="#upgrade_plan_'.$layer_range.'" style="width:100%;font-size:17px;background:' . $this->parent->settings->mainColor . '99;color:#fff;padding: 15px 0;border:1px solid ' . $this->parent->settings->mainColor . ';">';
								
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
		
        $in_widget = $this->parent->inWidget;
        
        $in_editor = $this->parent->layer->in_editor('ltple');
        
		$item = '';
       
		if( $layer = LTPLE_Editor::instance()->get_layer($post) ){
			
			//get post_title
			
			$post_title = the_title('','',false);
			
			// get info modal
            
            $info_modal = $this->get_modal(get_permalink($post),$post_title);
            
			//get start url

			$quick_start_url = apply_filters('ltple_quick_start_url',$this->parent->urls->edit . '?uri='.$post->ID,$post);
			
			// get layer range
			
			$layer_range = $this->parent->layer->get_layer_range($post);
			
			// get item

			$item .= '<div class="' . implode( ' ', get_post_class('',$post->ID) ) . '" id="post-' . $post->ID . '">';
				
				$item .= '<div class="panel panel-default">';
					
					$alt_url = $this->parent->layer->get_preview_image_url($post,'blogindex-thumb',$this->parent->assets_url . 'images/default_item.png');
					
                    $thumb_url = $this->parent->layer->get_thumbnail_url($post,'blogindex-thumb',$alt_url);
                    
                    if( !$in_widget ){
                    
                        $item .= '<a type="button" data-toggle="modal" data-target="#'.$info_modal['id'].'">';
                    }
                    
                    $item .= '<div class="thumb_wrapper">';
                        
                        if( $in_editor ){
                        
                            $image_url = $this->parent->layer->get_thumbnail_url($post,'full',$alt_url);
                    
                            $item.= '<img loading="lazy" class="lazy" data-original="' . ( !empty($thumb_url) ? $thumb_url : $image_url ) . '" data-image="' . $image_url . '" />';
                        }
                        else{
                            
                            $item .= '<img loading="lazy" class="lazy" src="'.$thumb_url.'">';
                        }
                        
                    $item .= '</div>';
                    
                    if( !$in_widget ){
                    
                        $item .= '</a>';
                    }
                    
					$item .='<div class="panel-body" style="padding-bottom:0;position:relative;">';
						
						$item .='<span class="item-range" style="color:'.$this->parent->settings->mainColor.';">'.$layer_range->shortname.'</span>';
						
                        $title = '';
                        
                        if( !$in_widget ){
                        
                            $title .='<a type="button" data-toggle="modal" data-target="#'.$info_modal['id'].'" style="color:#566674;">';
                        }
                        
                        $title  .='<b>' . $post_title . '</b>';
                        
                        if( !$in_widget ){
                        
                            $title .= '</a>';
                        }
                        
						$item.= apply_filters('ltple_gallery_item_title',$title,$post);

					$item .= '</div>';
					
					$item .= '<div class="panel-footer" style="padding:0;margin-top:15px;">';
						
                        $item .= '<div class="btn-group btn-group-justified">';
                            
                            if( $in_widget === true ){
                                
                                $action = '';
                                
                                if( $this->parent->plan->user_has_layer( $post ) === true ){
                                    
                                    if( $in_editor ){
                                    
                                        $action .= '<a href="#item_'.$post->ID.'" class="btn insert_media" title="Select this template" data-src="'.apply_filters('ltple_gallery_item_data_src','',$layer,$layer_type).'">Select</a>';
                                    }
                                    else{
                                        
                                        $action .= '<a target="_parent" class="btn" href="'. $quick_start_url .'" title="Start editing this template">Start</a>';
                                    }
                                }
                                elseif( !empty($this->parent->user->plan['holder']) && $this->parent->user->plan['holder'] == $this->parent->user->ID ){
                                    
                                    $action .=  '<a type="button" class="btn" data-toggle="modal" data-target="#upgrade_plan_'.$layer_range->slug.'">'.PHP_EOL;
                                
                                        $action .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL; 
                            
                                    $action .= '</a>'.PHP_EOL;
                                }
                                
                                $item .= apply_filters('ltple_widget_gallery_item_action',$action,$post,$referer);
                            }
                            else{

                                $item .= '<a type="button" class="btn" data-toggle="modal" data-target="#'.$info_modal['id'].'" title="More info about '. $post_title .' template">Info</a>';
                                
                                $item .= $info_modal['content'];
                                
                                if( $preview_modal = $this->parent->layer->get_modal($layer,'Preview') ){
                                    
                                    $item .= '<a type="button" class="btn" data-toggle="modal" data-target="#'.$preview_modal['id'].'">'.PHP_EOL;
                                        
                                        $item .= 'Preview'.PHP_EOL;
                                    
                                    $item .= '</a>'.PHP_EOL;
                                    
                                    $item .= $preview_modal['content'].PHP_EOL;
                                }
                                
                                if($this->parent->plan->user_has_layer( $post ) === true){
                                    
                                    $item .= '<a class="btn" href="'. $quick_start_url .'" target="_parent" title="Start editing this template">Start</a>';
                                }
                                elseif( empty($this->parent->user->ID) || ( !empty($this->parent->user->plan['holder']) && $this->parent->user->plan['holder'] == $this->parent->user->ID ) ){
                                    
                                    $item .= '<a type="button" class="btn" data-toggle="modal" data-target="#upgrade_plan_'.$layer_range->slug.'" href="#upgrade_plan_'.$layer_range->slug.'">'.PHP_EOL;
                                
                                        $item .= '<span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Unlock'.PHP_EOL;
                            
                                    $item .= '</a>'.PHP_EOL;
                                }
                            }

						$item .= '</div>';
                        
					$item .= '</div>';
				
				$item .= '</div>';
				
			$item .= '</div>';
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
						$card		= 4,
						$itemHeight	= 295, 
						$fixedHeight= true, 
						$echo		= true,
						$pageSize	= $this->per_page
						
					);

				echo'</div>';
				
			echo'</div>';
					
		echo'</div>';		
	}
	
	public function init_navbar(){
		
		$menu_name = __( 'Navigation Bar', 'live-template-editor-client' );
		
		$location = 'ltple_navbar';
		
		register_nav_menus( array(
		
			$location => $menu_name,
		));
		
		if( !wp_get_nav_menu_object($menu_name) ){
			
			$menu_id = wp_create_nav_menu($menu_name);
			
			wp_update_nav_menu_item($menu_id, 0, array(
				
				'menu-item-title'	=>  __('Dashboard'),
				'menu-item-classes' => '',
				'menu-item-url' 	=> apply_filters('rew_prod_url',$this->parent->urls->dashboard), 
				'menu-item-status' 	=> 'publish',
			));
			
			wp_update_nav_menu_item($menu_id, 0, array(
				
				'menu-item-title'	=>  __('Templates'),
				'menu-item-classes' => '',
				'menu-item-url' 	=> apply_filters('rew_prod_url',$this->parent->urls->gallery), 
				'menu-item-status' 	=> 'publish',
			));
			
			wp_update_nav_menu_item($menu_id, 0, array(
				
				'menu-item-title'	=>  __('Media'),
				'menu-item-classes' => '',
				'menu-item-url' 	=> apply_filters('rew_prod_url',$this->parent->urls->media), 
				'menu-item-status' 	=> 'publish',
			));
					
			if( !has_nav_menu($location) ){
				
				$locations = get_theme_mod('nav_menu_locations');
				
				$locations[$location] = $menu_id;
				
				set_theme_mod( 'nav_menu_locations', $locations );
			}
		}
	}
	
	public function filter_gallery_item_title($content,$post){
		
		$nickname = get_the_author_meta('nickname', $post->post_author );
			
		$item_title = '<a class="product-logo" href="' . $this->parent->profile->get_user_url($post->post_author) . '" target="_blank" style="position:absolute;top:-25px;">';
			
			$item_title .= '<img loading="lazy" src="'.$this->parent->image->get_avatar_url($post->post_author).'" style="height:45px;width:45px;border: 5px solid #fff;background:#fff;border-radius:250px;">';
			
		$item_title .= '</a>';
		
		$item_title .= '<div style="margin-top:10px;line-height:25px;height:30px;overflow:hidden;font-size:15px;font-weight:bold;">';
		
			$item_title .= $content;
		
		$item_title .= '</div>';
		
		if( !empty($nickname) ){
		
			$item_title .= '<div style="font-size: 11px;">';
				
				if( $this->parent->inWidget === true ){
					
					$item_title .= ' by <span>' . $nickname . '</span>';
				}
				else{
					
					$item_title .= ' by <a href="' . $this->parent->profile->get_user_url($post->post_author) . '/" target="_blank">' . $nickname . '</a>';
				}
			
			$item_title .= ' </div>';
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

class LTPLE_Client_Menu_Navbar extends Walker_Nav_Menu {

	function display_element ($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
		
		// check, whether there are children for the given ID and append it to the element with a (new) ID
		
		$element->hasChildren = isset($children_elements[$element->ID]) && !empty($children_elements[$element->ID]);
 
		return parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}
 
	function start_lvl(&$output, $depth = 0, $args = array()) {
	  
		$indent = str_repeat("\t", $depth);
	  
		$output .= "\n$indent<ul class=\"dropdown-menu\">\n";
	}
 
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
	  
		$item_html = '';
		
		array_push($item->classes,'pull-left','hidden-xs');
		
		$item->url = apply_filters('rew_server_url',$item->url);
		
		parent::start_el($item_html,$item,$depth,$args);
		
		$item_html = str_replace('<li', '<li style="list-style:none;"', $item_html);
		
		$item_html = str_replace('<a', '<a style="color:#566674;background:#f5f5f5;border:none;margin-left:6px;" class="btn btn-sm"', $item_html);
	 
		$output .= $item_html;
	}
}
  
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Media extends LTPLE_Client_Object {
	
	var $parent;
	var $type;
	var $slug;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
	
		add_action( 'init', array($this,'init_media'));
		
		// add query vars
		
		add_filter('query_vars', array( $this, 'add_query_vars'), 1);		
		
		// add media url
		
		add_filter( 'ltple_urls', array( $this, 'get_panel_url'));
		
		// add url parameters
		
		add_filter( 'template_redirect', array( $this, 'get_url_parameters'));		
					
		// add media shortcode
		
		add_shortcode('ltple-client-media', array( $this , 'get_media_shortcode' ) );
	}
	
	public function init_media(){
		
		if( empty( $this->slug ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Media',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-media]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$this->slug = update_option( $this->parent->_base . 'mediaSlug', get_post($post_id)->post_name );
		}
		
		$this->parent->urls->media = $this->parent->urls->home . '/' . $this->slug . '/';		
	}
	
	public function add_query_vars( $query_vars ){
		
		if(!in_array('media',$query_vars)){
		
			$query_vars[] = 'media';
		}

		return $query_vars;	
	}
	
	public function get_panel_url(){
		
		$this->slug = get_option( $this->parent->_base . 'mediaSlug' );
		
		// add rewrite rules

		add_rewrite_rule(
			
			$this->slug . '/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&media=$matches[1]',
			'top'
		);
	}
	
	
	public function get_url_parameters(){

		// get media type

		if( !$this->type = get_query_var('media') ){
			
			$this->type = 'image-library';
		}
	}
	
	public function get_media_shortcode(){
		
		// vertical tab styling
		
		echo '<style>';
			echo '.pgheadertitle{display:none;}.tabs-left,.tabs-right{border-bottom:none;padding-top:2px}.tabs-left{border-right:0px solid #ddd}.tabs-right{border-left:0px solid #ddd}.tabs-left>li,.tabs-right>li{float:none;margin-bottom:2px}.tabs-left>li{margin-right:-1px}.tabs-right>li{margin-left:-1px}.tabs-left>li.active>a,.tabs-left>li.active>a:focus,.tabs-left>li.active>a:hover{border-left: 5px solid #F86D18;border-top:0;border-right:0;border-bottom:0; }.tabs-right>li.active>a,.tabs-right>li.active>a:focus,.tabs-right>li.active>a:hover{border-bottom:0px solid #ddd;border-left-color:transparent}.tabs-left>li>a{border-radius:4px 0 0 4px;margin-right:0;display:block}.tabs-right>li>a{border-radius:0 4px 4px 0;margin-right:0}.sideways{margin-top:50px;border:none;position:relative}.sideways>li{height:20px;width:120px;margin-bottom:100px}.sideways>li>a{border-bottom:0px solid #ddd;border-right-color:transparent;text-align:center;border-radius:4px 4px 0 0}.sideways>li.active>a,.sideways>li.active>a:focus,.sideways>li.active>a:hover{border-bottom-color:transparent;border-right-color:#ddd;border-left-color:#ddd}.sideways.tabs-left{left:-50px}.sideways.tabs-right{right:-50px}.sideways.tabs-right>li{-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg)}.sideways.tabs-left>li{-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg)}';
			echo 'span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {background-color: #F86D18;border: 1px solid #FF5722;}';
		echo '</style>';

		include($this->parent->views . '/navbar.php');
		
		if($this->parent->user->loggedin){

			include($this->parent->views . '/media.php');

			do_action('ltple_media');
		}
	}
	
	public function get_app_list(){
						
		$apps = [];

		$item = new stdClass();
		$item->name 	= 'Upload';
		$item->slug 	= 'upload';
		$item->types 	= ['images'];
		$item->pro 		= true;

		$apps[] = $item;

		$item = new stdClass();
		$item->name 	= 'Canvas';
		$item->slug 	= 'canvas';
		$item->types 	= ['images'];
		$item->pro 		= true;
		
		$apps[] = $item;					

		$item = new stdClass();
		$item->name 	= 'Urls';
		$item->slug 	= 'url';
		$item->types 	= ['images'];
		$item->pro 		= false;
		
		$apps[] = $item;

		if( !empty($this->parent->apps->list) ){
			
			$apps = array_merge($apps,$this->parent->apps->list);
		}

		return $apps;		
	}
	
	public function get_default_images($tab){
		
		$default_images = [];

		foreach( $this->parent->image->types as $term ){
			
			$default_images[$term->slug] = [];
		}
			
		$loop = new WP_Query( array( 
		
			'post_type' 		=> 'default-image',
			'posts_per_page' 	=> -1,
			'tax_query' 		=> array(
			
				array(
				
				  'taxonomy' 	=> 'image-type',
				  'field' 		=> 'slug',
				  'terms' 		=> $tab
				)
			)			
		));

		while ( $loop->have_posts() ) : $loop->the_post(); 
			
			global $post;
			$image = $post;

			$media_url = $this->parent->urls->media . '?uri=' . $image->ID;

			//get permalink
			
			$permalink = get_permalink($image);
			
			//get post_title
			
			$image_title = the_title('','',false);
			
			//get image_type
			
			$image_type = $tab;
			
			//get item
			
			$item='';
			
			$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$image->ID) ) . '" id="post-' . $image->ID . '">';
				
				$item.='<div class="panel panel-default">';
					
					/*
					$item.='<div class="panel-heading">';

						$item.='<b style="overflow:hidden;width:90%;display:block;">' . $image_title . '</b>';
						
					$item.='</div>';
					*/

					$item.='<div class="thumb_wrapper" style="">';
					
						$item.= '<img class="lazy" data-original="'.$image->post_content . '" />';
					
					$item.='</div>'; //thumb_wrapper

					$item.='<div class="panel-body">';
						
						$item.='<b style="overflow:hidden;width:100%;height:25px;display:block;">' . $image_title . '</b>';
						
						$item.='<div class="text-right">';

							if($this->parent->inWidget){
								
								$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="' . $image->post_content . '">Insert</a>';							
							}
							else{

								$item.='<input style="width:100%;padding: 2px;" type="text" value="' . $image->post_content .'" />';
							}
							
						$item.='</div>';
						
					$item.='</div>'; //panel-body

				$item.='</div>';
				
			$item.='</div>';
			
			//merge item
			
			$default_images[$image_type][]=$item;
			
		endwhile; wp_reset_query();	
		
		return $default_images;
	}
	
	public function get_user_images($user_id,$tab){
		
		//get user images
		
		$user_images = [];
		
		if( $user_id  > 0 ){
			
			//-----------------get images from core library-------------------
			
			$args = array(
			
				'post_type'      	=> 'attachment',
				'post_mime_type' 	=> 'image',
				'post_status'    	=> 'inherit',
				'posts_per_page' 	=> -1,
				'author' 			=> $user_id,
			);
			
			$meta_key = $this->parent->_base . 'upload_source';
			
			if( $tab == 'upload' ){
				
				$args['meta_query'] = array(
				
					'relation' => 'OR',
					array(
						'key' 		=> $meta_key,
						'value' 	=> $tab,
						'compare' 	=> '='
					),
					array(
						'key' 		=> $meta_key,
						'compare' 	=> 'NOT EXISTS'
					),
				);					
			}
			else{
				
				$args['meta_query'] = array(
				
					array(
						'key' 		=> $meta_key,
						'value' 	=> $tab,
						'compare' 	=> '='
					),
				);				
			}
			
			$query_images = new WP_Query( $args );

			$images = array();
			
			foreach ( $query_images->posts as $image ){
				
				$image_url = wp_get_attachment_url( $image->ID );
				
				$image_title = get_the_title( $image->ID );
				
				//get item
				
				$item='';
				
				$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$image->ID) ) . '" id="post-' . $image->ID . '">';
					
					$item.='<div class="panel panel-default">';
						
						if(!$this->parent->inWidget){
						
							$item.='<a class="btn-xs btn-danger" href="' . $this->parent->urls->media . 'user-images/?att=' . $image->ID . '&imgAction=delete&tab='.$tab.'" style="padding: 0px 5px;position: absolute;top: 11px;right: 25px;font-weight: bold;">x</a>';
						}						
						
						$item.='<div class="thumb_wrapper">';
						
							$item.= '<img class="lazy" data-original="' . $image_url . '" />';
						
						$item.='</div>'; //thumb_wrapper						

						$item.='<div class="panel-body">';
							
							$item.='<b style="overflow:hidden;width:100%;height:25px;display:block;">' . $image_title . '</b>';

							$item.='<div class="text-right">';

								if($this->parent->inWidget){

									$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="' . $image_url . '">Insert</a>';
								}
								else{
									
									$item.='<input style="width:100%;padding: 2px;" type="text" value="' . $image_url . '" />';
								}
								
							$item.='</div>';							
							
						$item.='</div>'; //panel-body

					$item.='</div>';
					
				$item.='</div>';
				
				//merge item
				
				$user_images[$tab][]=$item;				
			}
			
			//-------------------get images from apps------------------------
			
			$args =  array(
			
				'post_type' 		=> 'user-image', 
				'posts_per_page' 	=> -1, 
				'author' 			=> $user_id,
			);
			
			if( $tab == 'url' ){
				
				$args['tax_query'] = array(
			
					array(
					
						'taxonomy' 	=> 'app-type',
						'operator' 	=> 'NOT EXISTS',
					)
				);
			}
			else{
				
				$args['tax_query'] = array(
			
					array(
					
					  'taxonomy' 	=> 'app-type',
					  'field' 		=> 'slug',
					  'terms' 		=> $tab
					)
				);
			}
			
			$loop = new WP_Query($args);
			
			while ( $loop->have_posts() ) : $loop->the_post(); 
				
				global $post;
				$image = $post;

				$media_url = $this->parent->urls->media . '?uri=' . $image->ID;

				//get permalink
				
				$permalink = get_permalink($image);
				
				//get post_title
				
				$image_title = the_title('','',false);
				
				//get terms
				
				$terms = wp_get_object_terms( $image->ID, 'app-type' );
				
				//get image_provider
				
				$image_provider = 'url';
				
				if( !isset($terms->errors) && isset($terms[0]->slug) ){
					
					$image_provider = $terms[0]->slug;
				}
				
				//get item
				
				$item='';
				
				$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$image->ID) ) . '" id="post-' . $image->ID . '">';
					
					$item.='<div class="panel panel-default">';
						
						$item.='<div class="panel-heading">';
							
							$item.='<b style="overflow:hidden;width:100%;height:25px;display:block;">' . $image_title . '</b>';
							
							if(!$this->parent->inWidget){
							
								$item.='<a class="btn-xs btn-danger" href="' . $this->parent->urls->media . 'user-images/?uri=' . $image->ID . '&imgAction=delete&tab='.$tab.'" style="padding: 0px 5px;position: absolute;top: 11px;right: 25px;font-weight: bold;">x</a>';
							}
							
						$item.='</div>';

						$item.='<div class="panel-body">';
							
							$item.='<div class="thumb_wrapper">';
							
								$item.= '<img class="lazy" data-original="'.$image->post_content.'" />';
									
							$item.='</div>'; //thumb_wrapper

							$item.='<div class="text-right">';

								if($this->parent->inWidget){

									$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="'.$image->post_content.'">Insert</a>';
								}
								else{
									
									$item.='<input style="width:100%;padding: 2px;" type="text" value="'. $image->post_content .'" />';
								}
								
							$item.='</div>';							
							
						$item.='</div>'; //panel-body

					$item.='</div>';
					
				$item.='</div>';
				
				//merge item
				
				$user_images[$image_provider][]=$item;
				
			endwhile; wp_reset_query();					
		}

		return $user_images;
	}
	
	public function get_user_bookmarks($user_id){
		
		$bookmarks = [];
		
		if( $user_id  > 0 ){
			
			//get user apps
			
			$loop = new WP_Query( array( 
				
				'post_type' 		=> 'user-bookmark', 
				'posts_per_page' 	=> -1, 
				'author' 			=> $user_id 
			));
			
			while ( $loop->have_posts() ) : $loop->the_post(); 
				
				global $post;
				$bookmark = $post;

				$media_url = $this->parent->urls->media . '?uri=' . $bookmark->ID;

				//get permalink
				
				$permalink = get_permalink($bookmark);
				
				//get post_title
				
				$bookmark_title = the_title('','',false);
				
				//get terms
				
				$terms = wp_get_object_terms( $bookmark->ID, 'app-type' );
				
				//get bookmark_provider
				
				$bookmark_provider = $terms[0]->slug;

				//get item
				
				$item='';
				
				$item.='<div class="col-xs-2 col-sm-2 col-lg-1">';

					$item.='<img class="lazy" data-original="' . $this->parent->assets_url . '/images/payment.png" />';
						
				$item.='</div>';

				$item.='<div class="col-xs-8 col-sm-8 col-lg-9">';

					$item.='<b style="overflow:hidden;width:100%;height:25px;display:block;">' . $bookmark_title . '</b>';
					$item.='<br>';
					$item.='<input style="width:100%;padding: 2px;" type="text" value="'. $bookmark->post_content .'" />';

				$item.='</div>';
				
				$item.='<div class="col-xs-2 col-sm-2 col-lg-2">';
				
					if($this->parent->inWidget){

						$item.='<a style="display:block;margin-top:11px;" class="btn-sm btn-primary insert_media" href="#" data-src="'.$bookmark->post_content.'">Insert</a>';
					}
					else{
						
						$item.='<a class="btn-xs btn-danger" href="' . $this->parent->urls->media . 'user-payment-urls/?id='. $bookmark->ID . '&action=deleteBookmark&app='.$bookmark_provider.'" style="padding: 0px 5px;position: absolute;top: 11px;right: 25px;font-weight: bold;">x</a>';
					}
				
				$item.='</div>';
				
				//merge item
				
				$bookmarks[$bookmark_provider][]=$item;
				
			endwhile; wp_reset_query();					
		}		
		
		return $bookmarks;
	}
}  
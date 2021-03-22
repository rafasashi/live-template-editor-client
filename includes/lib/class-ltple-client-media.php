<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Media extends LTPLE_Client_Object {
	
	var $parent;
	var $type;
	var $slug;
	var $per_page = 50;
	
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
		
		// add style
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		
		// add scripts
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );	
		
		// add media shortcode
		
		add_shortcode('ltple-client-media', array( $this , 'get_media_shortcode' ) );

		add_action( 'rest_api_init', function () {
			
			register_rest_route( 'ltple-media/v1', '/user-images', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_uploaded_images'),
			) );
			
			register_rest_route( 'ltple-media/v1', '/external-images', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_external_images'),
			) );
			
			register_rest_route( 'ltple-media/v1', '/image-library', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_default_images'),
			) );
		} );			
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
			
			$this->type = 'user-images';
		}
	}
	
	public function get_style(){
		
		// vertical tab styling

		$style = '.pgheadertitle{display:none;}.tabs-left,.tabs-right{border-bottom:none;padding-top:2px}.tabs-left{border-right:0px solid #ddd}.tabs-right{border-left:0px solid #ddd}.tabs-left>li,.tabs-right>li{float:none;margin-bottom:2px}.tabs-left>li{margin-right:-1px}.tabs-right>li{margin-left:-1px}.tabs-left>li.active>a,.tabs-left>li.active>a:focus,.tabs-left>li.active>a:hover{border-left: 5px solid #F86D18;border-top:0;border-right:0;border-bottom:0; }.tabs-right>li.active>a,.tabs-right>li.active>a:focus,.tabs-right>li.active>a:hover{border-bottom:0px solid #ddd;border-left-color:transparent}.tabs-left>li>a{border-radius:4px 0 0 4px;margin-right:0;display:block}.tabs-right>li>a{border-radius:0 4px 4px 0;margin-right:0}.sideways{margin-top:50px;border:none;position:relative}.sideways>li{height:20px;width:120px;margin-bottom:100px}.sideways>li>a{border-bottom:0px solid #ddd;border-right-color:transparent;text-align:center;border-radius:4px 4px 0 0}.sideways>li.active>a,.sideways>li.active>a:focus,.sideways>li.active>a:hover{border-bottom-color:transparent;border-right-color:#ddd;border-left-color:#ddd}.sideways.tabs-left{left:-50px}.sideways.tabs-right{right:-50px}.sideways.tabs-right>li{-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg)}.sideways.tabs-left>li{-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg)}';
		
		$style .= 'span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {background-color: #F86D18;border: 1px solid #FF5722;}';
	
		$style .= '
			
			.fixed-table-body{
				
				background-color:#142635!important;
			}			
			
			.table {
				
				float:left!important;
			}
			
			.table {
				
				width:60%!important;
			}
			
			.table td {
			
				padding: 4px!important;
			}

			.table .panel {
			
				cursor: pointer!important;
			}
			
			.table .panel.selectedItem {
				
				outline: 1px solid rgb(86, 180, 239)!important;
				box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.05) inset, 0px 0px 7px rgba(82, 168, 236, 0.6)!important;
			}
			
			#previewContainer {
				
				width:40%;
				float:right;
				text-align: center;
			}
			
			#previewWrapper {
				
				padding:30px;
			}
			
			#previewToolbar{
				
				position: absolute;
				bottom: 0;
				width: 100%;
				color: #eee;
				text-align: left;
				padding: 10px;
				background: #142635d9;
			}
			
			#previewContainer img {
				
				width: auto;
				max-height: calc(100vh - ' . ( $this->parent->inWidget ? 190 : 250 ) . 'px);
			}
		';
		
		return $style;
	}
	
	public function get_script(){
		
		$script = '
		
			;(function($){

				function set_image_preview($previewItem){
					
					if( typeof $previewItem == typeof undefined ){
						
						$previewItem = $(".table .panel:first");
					}
					
					if( typeof $previewItem != typeof undefined ){
					
						var previewSrc = $previewItem.contents().find("img").attr("data-original");
						
						$(".selectedItem").removeClass("selectedItem");
						
						$previewItem.addClass("selectedItem");
						
						if( $("#previewImg").length == 0 ){

							var html = "<div id=\"previewContainer\">";
							
								html += "<div id=\"previewWrapper\">";
								
									html += "<img id=\"previewImg\" />";
								
								html += "</div>";
								
								/*
								html += "<div id=\"previewToolbar\">";
								
									html += "tool";
								
								html += "</div>";
								*/
								
							html += "</div>";
							
							$(html).insertAfter(".table");
						}
						
						$("#previewImg").attr("src",previewSrc).attr("data-selector",$previewItem.closest(".hentry").attr("id"));
					}
					else if( $("#previewImg").length > 0 ){
						
						var previewSelector = $("#previewImg").attr("data-selector");
					
						$(".selectedItem").removeClass("selectedItem");
						
						$("#" + previewSelector + " .panel").addClass("selectedItem");						
					}
				}
			
				$(document).ready(function(){
			
					$( "#saveImageForm button" ).click(function() {
						
						this.closest( "form" ).submit();
					});
			
					$(".table").on("load-success.bs.table", function(e) {
						
						set_image_preview();
						
						$(".table .panel").on("click",function(){
							
							set_image_preview($(this));
						});
					});
					
					$(".table").on("page-change.bs.table", function(e) {
						
						set_image_preview();
						
						$(".table .panel").on("click",function(){
							
							set_image_preview($(this));
						});
					});
					
					$(".table").on("refresh.bs.table", function(e) {
						
						set_image_preview();
						
						$(".table .panel").on("click",function(){
							
							set_image_preview($(this));
						});
					});
				});
					
			})(jQuery);			
		';
		
		
		return $script;
	}
	
	public function enqueue_styles () {
		
		if( strpos($this->parent->urls->current, $this->parent->urls->media ) === 0 ){
			
			wp_register_style( $this->parent->_token . '-media', false, array());
			wp_enqueue_style( $this->parent->_token . '-media' );
		
			wp_add_inline_style( $this->parent->_token . '-media', $this->get_style());
		}
	}
	
	public function enqueue_scripts () {
		
		if( strpos($this->parent->urls->current, $this->parent->urls->media ) === 0 ){
			
			wp_register_script( $this->parent->_token . '-media', '', array( 'jquery', $this->parent->_token . '-bootstrap-table' ) );
			wp_enqueue_script( $this->parent->_token . '-media' );
		
			wp_add_inline_script( $this->parent->_token . '-media', $this->get_script());
		}
	}
	
	public function get_media_shortcode(){
		
		include($this->parent->views . '/navbar.php');
		
		if($this->parent->user->loggedin){

			include($this->parent->views . '/media.php');

			do_action('ltple_media');
		}
		else{
			
			echo $this->parent->login->get_form();
		}
	}
	
	public function get_external_providers(){
						
		$apps = [];				

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
	
	public function get_default_images($rest = NULL){
		
		$default_images = [];
		
		$slug = 'image-library';
		
		$args =  array( 
		
			'post_type' 		=> 'default-image',
			'posts_per_page' 	=> $this->per_page,
			'paged'				=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 ),		
		);
		
		if( !empty($_GET['filter']) ){
			
			parse_str($_GET['filter'],$filter);
			
			if(!empty($filter['image-type'])){
				
				$args['tax_query'] = array(
					
					'relation' => 'AND',
				);
				
				foreach( $filter as $taxonomy => $terms ){
					
					$args['tax_query'][] = array(
					
						'taxonomy' 	=> $taxonomy,
						'field' 	=> 'slug',
						'terms' 	=> $terms,
					);
				}
			}
		}
		
		$q = new WP_Query($args);

		while ( $q->have_posts() ) : $q->the_post(); 
			
			global $post;

			$image_type = wp_get_object_terms($post->ID,'image-type');
			
			$image_type = isset( $image_type[0]->slug ) ? $image_type[0]->slug : '';
			
			$item = array(
				
				'item' => $this->get_image_item($post,$slug),
				'type' => $image_type,
			);
				
			$default_images[] = $item;

		endwhile; wp_reset_query();	
		
		return $default_images;
	}
	
	public function get_uploaded_images($rest = NULL){
		
		//get user images
		
		$user_images = [];
		
		$user_id = $this->parent->user->ID;
		
		if( $user_id  > 0 ){
			
			//-----------------get images from core library-------------------
			
			$args = array(
			
				'post_type'      	=> 'attachment',
				'post_mime_type' 	=> 'image',
				'post_status'    	=> 'inherit',
				'posts_per_page' 	=> $this->per_page,
				'paged'				=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 ),
				'author' 			=> $user_id,
			);
			
			$meta_key = $this->parent->_base . 'upload_source';
			
			$slug = 'user-images';
			
			$source = 'upload';
			
			$args['meta_query'] = array(
			
				'relation' => 'OR',
				array(
					'key' 		=> $meta_key,
					'value' 	=> $source,
					'compare' 	=> '='
				),
				array(
					'key' 		=> $meta_key,
					'compare' 	=> 'NOT EXISTS'
				),
			);

			$query_images = new WP_Query( $args );

			$images = array();
			
			foreach ( $query_images->posts as $image ){
				
				if( $image->post_mime_type != 'image/vnd.adobe.photoshop' ){
					
					$user_images[]['item']= $this->get_image_item($image,$slug);
				}
			}			
		}

		return $user_images;
	}
	
	public function get_external_images($rest = NULL){
		
		//get user images
		
		$user_images = [];
		
		$user_id = $this->parent->user->ID;
		
		$slug = 'external-images';
		
		if( $user_id  > 0 ){
			
			//-------------------get images from apps------------------------
			
			$args =  array(
			
				'post_type' 		=> 'user-image', 
				'posts_per_page' 	=> $this->per_page,
				'paged'				=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 ),
				'author' 			=> $user_id,
			);

			$q = new WP_Query($args);
			
			while ( $q->have_posts() ) : $q->the_post(); 
				
				global $post;

				$item = array(
					
					'item' => $this->get_image_item($post,$slug),
				);
				
				$user_images[]= $item;
				
			endwhile; wp_reset_query();					
		}

		return $user_images;
	}	
	
	public function get_image_item($image,$slug){
		
		if( $slug == 'user-images' ){
		
			$image_url = wp_get_attachment_url( $image->ID );
		}
		elseif( $slug == 'image-library' ){
			
			$image_url = $image->post_content;
		}
		else{
			
			$image_url = $image->post_content;
		}
		
		//get item
		
		$item='';
		
		$item.='<div class="' . implode( ' ', get_post_class("",$image->ID) ) . '" id="post-' . $image->ID . '">';
			
			$item.='<div class="panel panel-default">';
				
				if(!$this->parent->inWidget ){
					
					if( $slug != 'image-library' ){
					
						$item.='<a data-toggle="action" data-refresh="self" class="btn-xs btn-danger" href="' . $this->parent->urls->media . $slug . '/?'.( $slug == 'user-images' ? 'att' : 'uri' ).'=' . $image->ID . '&imgAction=delete" style="padding: 0px 5px;position: absolute;top: 12px;right: 12px;font-weight: bold;">x</a>';
					}
				}						
				
				$item.='<div class="media_wrapper">';
				
					$item.= '<img loading="lazy" class="lazy" data-original="' . $image_url . '" />';
				
				$item.='</div>'; //thumb_wrapper						
				
				if($this->parent->inWidget){
					
					$item.='<div class="panel-body">';
						
						$item.='<div class="text-right">';

							$item.='<a class="btn-sm btn-primary insert_media" href="#" data-src="' . $image_url . '">Insert</a>';

						$item.='</div>';							
						
					$item.='</div>'; //panel-body
				}
				else{
					
					$item.='<div class="panel-body" style="padding:15px 0 15px 15px;">';
						
						$item.='<div class="pull-left" style="width: calc(100% - 40px) !important;">';

							$item.='<input style="width:100%;padding:4px;background:#fbfbfb;" type="text" value="' . $image_url . '" disabled="disabled" />';

						$item.='</div>';
						
						$item.='<div class="pull-right" style="padding:3px 0px;width:40px;text-align:center;">';
							
							$item.='<div class="dropup">';
							
								$item.='<button class="glyphicon glyphicon-option-vertical dropdown-toggle" style="border:none;background:transparent;padding:5px;border-radius:30px;" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" aria-hidden="true"></button>';

								$item.='<ul class="dropdown-menu dropdown-menu-right" style="background:#fff;">';
									
									// effects button
									
									$editor_url = $this->parent->server->url . '/c/p/live-effect-editor-dependencies/?url=' . urlencode($image_url);
									
									$modal_id='modal_'.md5($editor_url);
									
									$btnStyle='color:#182f42;border-bottom: 1px solid #eee;border-right: none;border-left: none;border-top: none;width: 100%;text-align: left;padding: 10px 20px;background: #fff;';
									
									$item.='<li style="position:relative;">';

										$item.='<button style="'.$btnStyle.'" type="button" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
											
											$item.='<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> '.PHP_EOL;
											
											$item.='Effects'.PHP_EOL;
										
										$item.='</button>'.PHP_EOL;

										$item.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
											
											$item.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
												
												$item.='<div class="modal-content">'.PHP_EOL;
												
													$item.='<div class="modal-header">'.PHP_EOL;
														
														$item.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
														
														$item.='<h4 class="modal-title text-left" id="myModalLabel">Image Effects</h4>'.PHP_EOL;
													
													$item.='</div>'.PHP_EOL;

													$item.= '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->assets_url . '/loader.gif\');"></div>';

													$item.= '<iframe data-src="'.$editor_url.'" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';

												$item.='</div>'.PHP_EOL;
												
											$item.='</div>'.PHP_EOL;
											
										$item.='</div>'.PHP_EOL;

									$item.='</li>';
									
									$item.='<li style="position:relative;">';
									
										$item.='<a href="' . $this->parent->urls->edit . '?uri=' . $image->ID . '&quick" style="color:#182f42;border-bottom:1px solid #eee;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit <span class="label label-primary" style="top:8px;position:absolute;right:15px;">advanced</span></a>';
									
									$item.='</li>';
									
								$item.='</ul>';

							$item.='</div>';
							
						$item.='</div>';	

					$item.='</div>'; //panel-body					
					
				}

			$item.='</div>';
			
			// get keywords
			
			$taxonomies = array();
			
			if( $slug == 'user-images' ){
			
				//$taxonomies[] = 'image-tag';
			}
			elseif( $slug == 'image-library' ){
				
				$taxonomies[] = 'image-type';
			}
			elseif( $slug == 'external-images' ){
				
				$taxonomies[] = 'app-type';
			}
			
			if( !empty($taxonomies) ){
				
				$item.='<div class="item-keywords" style="display:hidden;">';
								
					if( $terms = wp_get_object_terms($image->ID,$taxonomies) ){
						
						foreach( $terms as $term ){
							
							$item.= '<span>' . $term->name . '</span> ';
						}
					}
					
				$item.='</div>';
			}

		$item.='</div>';
		
		return $item;	
	}
	
	public function get_user_bookmarks($user_id){
		
		$bookmarks = [];
		
		if( $user_id  > 0 ){
			
			//get user apps
			
			$q = new WP_Query( array( 
				
				'post_type' 		=> 'user-bookmark', 
				'posts_per_page' 	=> $this->per_page,
				'paged'				=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( !empty($_GET['page']) ? intval($_GET['page']) : 1 ), 
				'author' 			=> $user_id 
			));
			
			while ( $q->have_posts() ) : $q->the_post(); 
				
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

					$item.='<img loading="lazy" class="lazy" data-original="' . $this->parent->assets_url . '/images/payment.png" />';
						
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
	
	public function get_image_table($type){

		//output Tab panes
		  
		echo'<div class="tab-content" style="margin-top:20px;">';
			
			echo'<div role="tabpanel" class="tab-pane active" id="' . $type . '">';
				
				// get table fields
				
				echo'<div style="margin:-20px 0px -15px 0px;">';
					
					$fields = array(
						
						array(

							'field' 	=> 'item',
							'sortable' 	=> 'false',
							'content' 	=> '',
						),					
					);
				
					// get table of results

					$this->parent->api->get_table(
					
						$this->parent->urls->api . 'ltple-media/v1/' . $type . '?' . http_build_query($_REQUEST, '', '&amp;'), 
						apply_filters('ltple_media_' . $type . '_fields',$fields), 
						$trash		= false,
						$export		= false,
						$search		= false,
						$toggle		= false,
						$columns	= false,
						$header		= true,
						$pagination	= 'scroll',
						$form		= false,
						$toolbar 	= 'toolbar',
						$card		= true,
						$itemHeight	= 280, 
						$fixedHeight= true, 
						$echo		= true,
						$pageSize	= $this->per_page
					);

				echo'</div>';
				
			echo'</div>';
					
		echo'</div>';		
	}
}  
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Element extends LTPLE_Client_Object { 
	
	public $parent;
	
	public $list = array();

	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;
	
		$this->parent->register_post_type( 'default-element', __( 'Default Elements', 'live-template-editor-client' ), __( 'Default Element', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> false,
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports'			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title','thumbnail'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_taxonomy( 'element-library', __( 'Element Libraries', 'live-template-editor-client' ), __( 'Element Library', 'live-template-editor-client' ),array('cb-default-layer','default-element'), 
	
			array(
				'hierarchical' 			=> true,
				'public' 				=> false,
				'show_ui' 				=> true,
				'show_in_nav_menus' 	=> false,
				'show_tagcloud' 		=> false,
				'meta_box_cb' 			=> null,
				'show_admin_column' 	=> false,
				'update_count_callback' => '',
				'show_in_rest'          => false,
				'rewrite' 				=> false,
				'sort' 					=> '',
			)
		);
		
		add_filter('admin_init', array( $this, 'init_element_backend' ));

		add_action('wp_loaded', array($this,'set_default_elements'));	
		
		add_action('ltple_element_types', array($this,'filter_element_types'));	
		
		add_shortcode('ltple-element-site', array( $this , 'get_element_site' ) );
	}
	
	public function get_element_site($content){
		
		if( defined('REW_SITE') )
			
			return REW_SITE;
			
		return $_SERVER['SERVER_NAME'];
	}
	
	public function filter_element_types($types=array()){
		
		$types['default-element'] = 'Default Element';
		
		return $types;
	}
	
	public function get_default_sections(){
		
		$types = [
						
			'headers'	=>	__( 'Headers', 'live-template-editor-client' ),
			'sections'	=>	__( 'Sections', 'live-template-editor-client' ),
			'actions'	=>	__( 'Actions', 'live-template-editor-client' ),
			'contents'	=>	__( 'Contents', 'live-template-editor-client' ),
			'components'=>	__( 'Components', 'live-template-editor-client' ),
			'buttons'	=>	__( 'Buttons', 'live-template-editor-client' ),
			'features'	=>	__( 'Features', 'live-template-editor-client' ),
			'blogs'		=>	__( 'Blogs', 'live-template-editor-client' ),
			'teams'		=>	__( 'Teams', 'live-template-editor-client' ),
			'profiles'	=>	__( 'Profiles', 'live-template-editor-client' ),
			'projects'	=>	__( 'Projects', 'live-template-editor-client' ),
			'products'	=>	__( 'Products', 'live-template-editor-client' ),
			'pricing'	=>	__( 'Pricing', 'live-template-editor-client' ),
			'testimonials'	=>	__( 'Testimonials', 'live-template-editor-client' ),
			'contact'	=>	__( 'Contact', 'live-template-editor-client' ),
			'images'	=>	__( 'Images', 'live-template-editor-client' ),
			'videos'	=>	__( 'Videos', 'live-template-editor-client' ),
			'widgets'	=>	__( 'Widgets', 'live-template-editor-client' ),
			'menus'		=>	__( 'Menus', 'live-template-editor-client' ),
			'forms'		=>	__( 'Forms', 'live-template-editor-client' ),
			'footers'	=>	__( 'Footers', 'live-template-editor-client' ),
		];
		
		return $types;		
	}
	
	public function set_default_elements(){

		$libraries = $this->get_terms( 'element-library', array(
			
			/*
			'bootstrap-3-grid' => array(
			
				'name' 	=> 'Bootstrap 3 - Grid',
			),
			*/
		));
	}

	public function init_element_backend(){
	
		add_filter('manage_edit-element-library_columns', array( $this, 'filter_element_library_columns' ) );
		add_filter('manage_element-library_custom_column', array( $this, 'filter_element_library_column_content' ),10,3);
	
		add_filter('manage_default-element_posts_columns', 	array( $this, 'filter_default_element_columns' ),999 );
		add_filter('manage_default-element_posts_custom_column', array( $this, 'filter_default_element_column_content' ),10,3);
	
		add_filter('admin_enqueue_scripts',array( $this, 'add_action_scripts' ) );
	}
	
	public function get_library_elements($term){
		
		if( is_numeric($term) ){
			
			$term = get_term($term);
		}
		
		if( isset($term->term_id) ){
			
			$term_id = $term->term_id;
			
			if( !isset($this->list[$term_id]) ){
				
				$elements = array();
				
				// get elements
				
				if( $elems = get_posts( array(
				
					'post_type' 	=> 'default-element',
					'numberposts' 	=> -1,
					'orderby'		=> 'id',
					'order' 		=> 'ASC',
					'tax_query' 	=> array(
						
						array(
						
							'taxonomy' 		=> 'element-library',
							'field' 		=> 'term_id', 
							'terms' 		=> $term->term_id,
							'include_children' => false
						)
					)
					
				))){
					
					foreach( $elems as $elem ){
						
						$meta = get_post_meta($elem->ID);
						
						$content 	= isset($meta['layerContent'][0]) ? $meta['layerContent'][0] : '';
						$type 		= isset($meta['elementType'][0])  ? $meta['elementType'][0]	 : 'sections';
						$drop 		= isset($meta['elementDrop'][0])  ? $meta['elementDrop'][0]  : 'out';
						$image 	 	= get_the_post_thumbnail_url($elem->ID,'post-thumbnail');
						
						$elements['name'][] 	= $elem->post_title;
						$elements['content'][] 	= $content;
						$elements['type'][] 	= $type;
						$elements['drop'][] 	= $drop;
						$elements['image'][] 	= $image;
					}
				}
				
				if( empty($elements) ){
					
					// migrate old content from meta
					
					if( $elements = $this->get_meta($term,'elements') ){
						
						// normalize content
						
						foreach( $elements['content'] as $i => $content ){
							
							// normalize content
							
							$content = str_replace( array(
								
								'wordpress.recuweb.com',
							
							),'[ltple-element-site]',$content);
							
							$elements['content'][$i] = $content;
							
							$name =	$elements['name'][$i];
							
							if( !empty($content) && !empty($name) ){
							
								// migrate to default element
								
								$element = null;
								
								$slug = sanitize_title($name);
								
								if( $elems = get_posts( array(
									
									'name' 			=> $slug,
									'post_type' 	=> 'default-element',
									'post_status' 	=> 'publish',
									'numberposts' 	=> -1,
									
								)) ){
									
									foreach( $elems as $elem ){
										
										if( $elem->post_name == $slug ){
											
											$element = $elem;
											
											break;
										}
									}
								}
								
								if( empty($element) ){
									
									$element_id = wp_insert_post( array(
										
										'post_title' 	=> $name,
										'post_name' 	=> $slug,
										'post_type' 	=> 'default-element',
										'post_status' 	=> 'publish',
									));
									
									wp_set_object_terms($element_id,$term->term_id,$term->taxonomy,true);
									
									update_post_meta($element_id,'layerContent',$content);
									update_post_meta($element_id,'elementType',$elements['type'][$i]);
									update_post_meta($element_id,'elementDrop',$elements['drop'][$i]);
									
									if( REW_SITE == 'himalayas.life' ){
										
										wp_set_object_terms($element_id,6750,'css-library',true);
										wp_set_object_terms($element_id,6782,'font-library',true);
									}
								}
								else{
									
									$element_id = $element->ID;
								}
							}
						}
					}
				}
				
				if( !empty($elements) ){
				
					// do shortcodes
				
					foreach( $elements['content'] as $i => $content ){
						
						// normalize content

						$elements['content'][$i] = do_shortcode($content);
					}
				}
				
				$this->list[$term_id] = $elements;
			}
			
			return $this->list[$term_id];
		}
		
		return false;
	}

	public function filter_element_library_columns($columns){
		
		$columns = [];
		
		$columns['cb'] 				= '<input type="checkbox" />';
		$columns['name'] 			= 'Name';
		//$columns['description'] 	= 'Description';
		$columns['slug'] 			= 'Slug';
		$columns['elements'] 		= 'Elements';

		return $columns;
	}
	
	public function filter_element_library_column_content($content, $column_name, $term_id){
		
		if( $column_name == 'elements' ){
			
			$content = $this->count_elements($term_id);
		}
		
		return $content;
	}
	
	public function count_elements($term){
		
		$count = 0;
		
		if( is_numeric($term) ){
			
			$term = get_term($term);
		}
		
		if( isset($term->term_id) ){
			
			$term_id = $term->term_id;
			
			if( $elements = $this->get_library_elements($term) ){
				
				if( isset($elements['name']) ){
					
					foreach( $elements['name'] as $i => $name ){
						
						if( !empty($name) && !empty($elements['content'][$i]) )
						
							$count++;
					}
				}
			}
			
			if( $term->parent == 0 ){
				
				if( $children = get_term_children($term->term_id,$term->taxonomy) ){

					foreach( $children as $child ){
						
						$count += $this->count_elements($child);
					}
				}
			}
		}
		
		return $count;
	}
	
	public function filter_default_element_columns($columns){
		
		// Remove description, posts, wpseo columns
		
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 		= '<input type="checkbox" />';
		$columns['thumb'] 	= 'Preview';
		$columns['title'] 	= 'Title';
		
		$columns['taxonomy-element-library'] = 'Libraries';
		
		if( current_user_can('administrator') ){
			
			$columns['actions'] = 'Actions';
		}
		
		return $columns;
	}

	public function filter_default_element_column_content($column_name, $post_id){
	
		if( $column_name === 'thumb' ){

			if( !$url = get_the_post_thumbnail_url($post_id,'post-thumbnail')){
				
				$url = $this->parent->assets_url . 'images/default-element.jpg';
			}
			
			echo '<a class="preview-' . $post_id . '" target="_blank" href="'.$url.'">';
			
				echo '<img style="width:150px;" src="'.$url.'">';
			
			echo '</a>';
		}
		elseif( $column_name === 'actions' ){
				
			echo '<div id="action-buttons-' . $post_id . '" class="action-buttons">';
				
					$source = get_permalink($post_id);

					echo '<button data-id="' . $post_id . '" data-title="' . get_the_title($post_id) . '" data-source="' . $source . '" data-toggle="dialog" data-target="#actionConsole" class="action-button button button-default button-small">';
						
						echo 'Refresh Preview';
						
					echo '</button>';

			echo '</div>';
			
			echo '<div id="meter-'.$post_id.'" class="action-meter" style="display:none;">';
				
				echo '<span class="progress" style="width:0%;"></span>';
				
			echo '</div>';
			
			echo '<div id="message-'.$post_id.'" class="action-message">';
			
				echo '<span class="completed" style="display:none;">Completed!</span>';
		
			echo '</div>';
		}
		
		return $column_name;
	}
	
	public function is_element_panel(){
		
		if( is_admin() ){
			
			$post_type = !empty($_GET['post_type']) ? $_GET['post_type'] : '';
			
			if( $post_type == 'default-element' )
			
				return true;
		}
			
		return false;
	}
	
	public function add_action_footer(){
		
		if( $this->is_element_panel() ){
			
			echo '<div id="actionConsole" style="display:none;" title="Action Console">';
				
				echo '<div id="actionLogs" style="height:50vh;width:50vw;">';

				echo '</div>';				
				
			echo '</div>';
		}
	}
	
	public function add_action_scripts(){
		
		if( $this->is_element_panel() ){
		
			if( current_user_can('administrator') ){
				
				// add style
				
				wp_register_style($this->parent->_token . '-element-actions', false,array());
				wp_enqueue_style($this->parent->_token . '-element-actions');
	
				wp_add_inline_style($this->parent->_token . '-element-actions', $this->get_style() );
				
				// add script
				
				wp_register_script( $this->parent->_token . '-element-actions', '', array( 'jquery' ) );
				wp_enqueue_script( $this->parent->_token . '-element-actions' );

				wp_add_inline_script( $this->parent->_token . '-element-actions', $this->get_script() );
			
				add_filter('admin_footer',array( $this, 'add_action_footer' ) );
			}
		}
	}
		
	public function get_style(){
		
		$style = '
			
			.column-actions{
				
				width: 150px;
			}
			
			.action-buttons {
				margin-bottom:10px;
			}
			
			.action-buttons button {
				margin-right:5px !important;
			}
			
			.action-message {
				padding:0 !important;
			}
					
			.action-meter { 
				height: 10px;
				padding: 5px;
				position: relative;
				background: #555;
				-moz-border-radius: 25px;
				-webkit-border-radius: 25px;
				border-radius: 25px;
				box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
			}
			.action-meter > span {
			  display: block;
			  height: 100%;
			  border-top-right-radius: 8px;
			  border-bottom-right-radius: 8px;
			  border-top-left-radius: 20px;
			  border-bottom-left-radius: 20px;
			  background-color: rgb(43,194,83);
			  background-image: linear-gradient(
				center bottom,
				rgb(43,194,83) 37%,
				rgb(84,240,84) 69%
			  );
			  box-shadow: 
				inset 0 2px 9px  rgba(255,255,255,0.3),
				inset 0 -2px 6px rgba(0,0,0,0.4);
			  position: relative;
			  overflow: hidden;
			  transition: width 5s;
			}

			.action-meter > span:after {
				content: "";
				position: absolute;
				top: 0; left: 0; bottom: 0; right: 0;
				background-image: 
				   -webkit-gradient(linear, 0 0, 100% 100%, 
					  color-stop(.25, rgba(255, 255, 255, .2)), 
					  color-stop(.25, transparent), color-stop(.5, transparent), 
					  color-stop(.5, rgba(255, 255, 255, .2)), 
					  color-stop(.75, rgba(255, 255, 255, .2)), 
					  color-stop(.75, transparent), to(transparent)
				   );
				background-image: 
					-moz-linear-gradient(
					  -45deg, 
					  rgba(255, 255, 255, .2) 25%, 
					  transparent 25%, 
					  transparent 50%, 
					  rgba(255, 255, 255, .2) 50%, 
					  rgba(255, 255, 255, .2) 75%, 
					  transparent 75%, 
					  transparent
				   );
				z-index: 1;
				-webkit-background-size: 50px 50px;
				-moz-background-size: 50px 50px;
				-webkit-animation: move 2s linear infinite;
				   -webkit-border-top-right-radius: 8px;
				-webkit-border-bottom-right-radius: 8px;
					   -moz-border-radius-topright: 8px;
					-moz-border-radius-bottomright: 8px;
						   border-top-right-radius: 8px;
						border-bottom-right-radius: 8px;
					-webkit-border-top-left-radius: 20px;
				 -webkit-border-bottom-left-radius: 20px;
						-moz-border-radius-topleft: 20px;
					 -moz-border-radius-bottomleft: 20px;
							border-top-left-radius: 20px;
						 border-bottom-left-radius: 20px;
				overflow: hidden;
			}
			
			@-webkit-keyframes move {
				0% {
				   background-position: 0 0;
				}
				100% {
				   background-position: 50px 50px;
				}
			}				
		';
		
		return $style;
	}
	
	public function get_script(){
		
		$script = '
			
			;(function($){
				
				// define a new console
				
				var console = (function(oldCons){
					
					return {
					
						log: function(text){
							
							oldCons.log(text);
							
							$("#actionLogs").append("<p style=\"margin-top:0px;color:green;\">" + text + "</p>");
						},
						info: function (text) {
							
							oldCons.info(text);
							
							$("#actionLogs").append("<p style=\"margin-top:0px;font-weight:bold;\">" + text + "</p>");
						},
						warn: function (text) {
							
							oldCons.warn(text);
							
							// $("#actionLogs").append("<p style=\"margin-top:0px;color:orange;\">" + text + "</p>");
						},
						error: function (text) {
							
							oldCons.error(text);
							
							$("#actionLogs").append("<p style=\"margin-top:0px;color:red;\">" + text + "</p>");
						}
					};
					
				}(window.console));

				//Then redefine the old console
				
				window.console = console;

				$(document).ready(function(){
					
					// requests handler
					
					var ajaxQueue = $({});

					$.ajaxQueue = function( ajaxOpts ) {
						var jqXHR,
							dfd = $.Deferred(),
							promise = dfd.promise();

						// queue our ajax request
						ajaxQueue.queue( doRequest );

						// add the abort method
						promise.abort = function( statusText ) {

							// proxy abort to the jqXHR if it is active
							if ( jqXHR ) {
								return jqXHR.abort( statusText );
							}

							// if there wasnt already a jqXHR we need to remove from queue
							var queue = ajaxQueue.queue(),
								index = $.inArray( doRequest, queue );

							if ( index > -1 ) {
								queue.splice( index, 1 );
							}

							// and then reject the deferred
							dfd.rejectWith( ajaxOpts.context || ajaxOpts,
								[ promise, statusText, "" ] );

							return promise;
						};

						// run the actual query
						function doRequest( next ) {
							jqXHR = $.ajax( ajaxOpts )
								.done( dfd.resolve )
								.fail( dfd.reject )
								.then( next, next );
						}

						return promise;
					};
					
					// bind buttons
					
					$(".action-button").each(function(i){
						
						$(this).on("click",function(){
							
							var id 		= $(this).attr("data-id");
							var title 	= $(this).attr("data-title");
							var source 	= $(this).attr("data-source");
	
							var $btns 		= $("#action-buttons-" + id);
							var $meter 		= $("#meter-" + id);
							var $progress 	= $("#meter-" + id + " .progress");
							var $completed 	= $("#message-" + id + " .completed");
							
							var screenshotUrl 	= "' . get_option( $this->parent->_base . 'server_url') . '";
							var uploaderUrl		= "' . get_admin_url() . '";
							
							$btns.find("button").prop("disabled",true);
							
							$meter.show();
							
							$completed.hide();
						
							$.ajaxQueue({
								
								type 		: "GET",
								url  		: source,
								cache		: false,
								beforeSend	: function(){
									
									console.info("Processing " + title + "...");
								},
								error: function() {
								
									console.error(source + " error");
																									
									$meter.hide();
									$btns.find("button").prop("disabled",false);
								},
								success: function(htmlDoc) {
								
									var proto = window.location.href.split("/")[0];
									
									// get total requests
									
									$progress.css("width", ( 100 / 3 ) + "%");
							
									$.ajaxQueue({
										
										type 		: "POST",
										url  		: screenshotUrl,
										data  		: {
											
											dev		: "'.( REW_DEV_ENV === true ? 'true' : 'false' ).'",
											action	: "takeScreenshot",
											htmlDoc : htmlDoc,
											selector: "body"
										},
										cache		: false,
										xhrFields	: {
											
											withCredentials: true
										},										
										beforeSend	: function(){
											
											
										},
										error: function() {
										
											console.error(screenshotUrl + " error");
										},
										success: function(imgData) {

											$.ajaxQueue({
												
												type 		: "POST",
												url  		: uploaderUrl,
												data  		: {
													
													postId	: id,
													imgData	: "image/png;base64," + imgData
												},
												cache		: false,
												xhrFields	: {
													
													withCredentials: true
												},
												beforeSend	: function(){
													
													
												},
												error: function() {
												
													console.error(uploaderUrl + " error");
												},
												success: function(thumbUrl) {
													
													$( ".preview-" + id ).attr("href",thumbUrl);
													$( ".preview-" + id + " img" ).attr("src",thumbUrl);
												},
												complete: function(){

													$progress.css("width", "100%");
													
													$progress.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(){
														
														$meter.hide();
														$progress.css("width", "0%");
														$btns.find("button").prop("disabled",false);
														$completed.show();
														$progress.unbind();
													});
												}
											});
										},
										complete: function(){

											$progress.css("width", ( 100 * 2 / 3 ) + "%");
										}
									});
								},
								complete: function(){
									
									
								}
							});
						});
					});
				});
				
			})(jQuery);
		';	

		return $script;
	}
}

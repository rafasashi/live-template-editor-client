<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Editor { 
	
	public $parent;
	
	private $actions;
	
	/**
	 * Constructor function
	 */ 
	 
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		
		add_filter('ltple_loaded', array( $this, 'init_editor' ));
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('editor',$query_vars)){
				
				$query_vars[] = 'editor';
			}
			
			if(!in_array('edit',$query_vars)){
				
				$query_vars[] = 'edit';
			}
			
			return $query_vars;
			
		}, 1);
		
		add_filter( 'ltple_editor_frontend', array( $this, 'get_editor' ),1);

		add_filter( 'ltple_editor_iframe_url', array( $this, 'filter_iframe_url' ),1,2);	
		
		add_filter( 'ltple_editor_dashboard_url', array( $this, 'filter_dashboard_url' ),1);	
		
		add_filter( 'ltple_editor_settings_url', array( $this, 'filter_settings_url' ),1);	
		
		add_filter( 'ltple_editor_edit_url', array( $this, 'filter_edit_url' ),1);	
				
		add_filter( 'ltple_editor_elements', array( $this, 'filter_elements' ),1);	
				
		add_filter( 'ltple_right_editor_navbar', array( $this, 'filter_right_navbar' ),1);			
		
		add_filter( 'ltple_editor_export_buttons', array( $this, 'filter_export_buttons' ),9999,2);
		
		add_filter( 'ltple_editor_navbar_settings', array( $this, 'filter_navbar_settings' ),1);
		
		add_filter( 'ltple_editor_js_settings', array( $this, 'filter_js_settings' ),1,2);	
		
		add_filter( 'ltple_editor_script', array( $this, 'filter_editor_script' ),1);
	
		add_filter('admin_enqueue_scripts',array( $this, 'add_actions_scripts' ) );
	
		add_filter( 'page_row_actions', array($this, 'filter_editor_row_actions'), 10, 2 );
		
		add_filter( 'post_row_actions', array($this, 'filter_editor_row_actions'), 10, 2 );
	
		add_action( 'admin_post_duplicate', array($this, 'duplicate_post_type') );
	}
	
	public function init_editor(){
		
		// add rewrite rules
		
		add_rewrite_rule(
		
			'edit/?$',
			'index.php?edit=editor',
			'top'
		);
	}
	
	public function get_editor($layer){
		
		if( $slug = get_query_var('edit') ){
			
			if( !empty($_REQUEST['uri']) ){
				
				if( !empty($_REQUEST['lk']) ){
					
					if(  !empty($_POST['domId']) ){
						
						if( !empty($_POST['base64']) ){
						
							// handle cropped image upload
						
							echo LTPLE_Editor::upload_base64_image($layer, $_POST['domId'], $_POST['base64']);
						}
						elseif( !empty($_POST['url']) ){
							
							$crop = !empty($_POST['crop']) ? json_decode(stripslashes($_POST['crop']),true) : false;
							
							echo LTPLE_Editor::upload_image_url($layer, $_POST['domId'], $_POST['url'], $crop);
						}
					}
					else{
			
						LTPLE_Editor::get_remote_script($layer,array(
							
							'key'		=> $_GET['lk'],
							'user'		=> $this->parent->ltple_encrypt_str($this->parent->plan->get_license_holder_email($this->parent->user)),
							'is_local' 	=> $this->parent->layer->is_local ? true : false,				
							'plan_url'	=> $this->parent->urls->plans,
						
						));
						
						echo'Malformed request...';
					}
				}
				elseif( $this->parent->user->has_layer ){
					
					if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ){
						
						wp_register_script( $this->parent->_token . '-editor-panel', '', array( 'jquery', $this->parent->_token . '-notify' ) );
						wp_enqueue_script( $this->parent->_token . '-editor-panel' );
						wp_add_inline_script( $this->parent->_token . '-editor-panel', $this->get_editor_panel_script());
						
						$layer_type = $this->parent->layer->get_layer_type($_REQUEST['uri']);

						if( $layer_type->output == 'hosted-page' ){
							
							add_action('ltple_list_sidebar',array($this->parent->profile,'get_sidebar'),10,3);
						}
						else{
							
							add_action('ltple_list_sidebar',array($this->parent->dashboard,'get_sidebar'),10,3);
						}
						
						include( $this->parent->views . '/editor-panel.php' );
					}
					elseif( ( !$this->parent->user->can_edit || !isset($_GET['edit']) ) && !isset($_GET['quick']) && ( $this->parent->layer->type == 'cb-default-layer' || $this->parent->layer->is_media ) ){

						include( $this->parent->views . '/editor-starter.php' );
					}
					else{
						
						$layer_plan = $this->parent->plan->get_layer_plan( $this->parent->layer->defaultId, 'min' );
						
						if( $this->parent->layer->type != 'cb-default-layer' && $layer_plan['amount'] > 0 && !$this->parent->user->plan["info"]["total_price_amount"] > 0 ){
							
							echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
								
								echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

							echo'</div>';
						}
						elseif( $this->parent->layer->is_editable_output($layer->output) ){
							
							if( $layer_plan['amount'] > 0 && $this->parent->user->remaining_days < 0 && $this->parent->user->plan["info"]["total_price_amount"] > 0 ){
								
								//check license
								
								if( !isset($_GET['period_refreshed']) ){
									
									// refresh user period
									
									$this->parent->users->update_periods($this->parent->user->ID);
									
									// redirect url
									
									$url = add_query_arg( array(
										
										'period_refreshed' => '',
										
									),$this->parent->urls->current);
									
									wp_redirect($url);
									exit;
								}
								else{
									
									echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
										
										echo '<div class="alert alert-warning">Your license is expired, please renew it via the plan page or contact us...</div>';

									echo'</div>';
								}
							}
							elseif( empty($_POST) && !empty($this->parent->layer->layerForm) && $this->parent->layer->layerForm != 'none' && empty($this->parent->layer->layerContent) ){
								
								include( $this->parent->views . '/editor-form.php' );
							}
							else{
								
								do_action('ltple_include_editor');
							}
						}
						else{
							
							echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
								
								echo '<div class="alert alert-warning">This template is not editable...</div>';

							echo'</div>';		
						}
					}
				}
				else{
					
					include( $this->parent->views . '/restricted.php' );
				}
				
				exit;
			}
		}
	}
	
	public function get_editor_panel_script(){
		
		$script = '
		
			;(function($){
			
				$(document).ready(function(){

					$("#saveBtn").on("click",function(e){
						
						e.preventDefault();
						
						$("#navLoader").css("display","inline-block");
						
						var action 	= $("#savePostForm").attr("action");
						
						var data 	= $("#savePostForm").serialize();
						
						$.ajax({
							
							type		: "POST",
							url			: action,
							cache		: false,
							data		: data,
							asych		: false,
							xhrFields	: {
								
								withCredentials: true
							},
							success	: function(data){
								
								// display message
								
								$.notify( data, {
									
									className: "success",
									position: "top center"
								});
								
								$("#navLoader").css("display","none");
							},
							error : function (request, status, error) {
								
								//console.log(request);
								
								// display message
								
								$.notify( "Error saving settings...", {
									
									className: "danger",
									position: "top center"
								});
								
								$("#navLoader").css("display","none");
							}				
						});
						
					});
					
				});
					
			})(jQuery);			
		';
		
		
		return $script;
	}

	public function filter_iframe_url($url,$layer){
		
		$url = $this->parent->urls->edit . '?uri=' . $layer->ID . '&lk=' . $layer->key . '&_=' . $layer->token;

		return $url;
	}
	
	public function filter_dashboard_url($dashboard_url){
	
		$dashboard_url = $this->parent->urls->dashboard;
		
		return $dashboard_url;
	}
	
	public function filter_settings_url($post_id){
		
		return $this->parent->urls->edit . '?uri=' . $post_id . '&action=edit';
	}
	
	public function filter_edit_url($post_id){
		
		return $this->parent->urls->edit . '?uri=' . $post_id;
	}	
	
	public function filter_elements($elemLibraries){
			
		// elements button

		if( !empty($this->parent->layer->defaultElements['name'][0]) ){
			
			$elemLibraries[] = $this->parent->layer->defaultElements;
		}			
		
		if( !empty($this->parent->layer->layerHtmlLibraries) ){
			
			foreach( $this->parent->layer->layerHtmlLibraries as $term ){
				
				$elements = $this->parent->element->get_library_elements($term);

				if( !empty($elements['name'][0]) ){
					
					$elemLibraries[] = $elements;
				}
			} 
		}
		
		if( !empty($elemLibraries) ){
			
			foreach( $elemLibraries as $e => $elements ){
				
				foreach( $elements['image'] as $i => $image ){
				
					if( empty($image) ){
				
						$elemLibraries[$e]['image'][$i] = $this->parent->assets_url . 'images/default-element.jpg';
					}
				}
			}
		}
		
		return $elemLibraries;
	}
	
	public function filter_right_navbar($layer){

		// insert button
		
		if( $layer->output == 'image' ){
			
			// insert image button
			
			echo '<button style="margin-left:2px;margin-right:2px;border:none;background:#9C27B0;color:#fff;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveImgEditorElements" data-height="450" data-width="75%" data-resizable="false">Insert</button>';
	
			echo '<div id="LiveImgEditorElements" title="Elements library" style="display:none;">'; 
			echo '<div id="LiveImgEditorElementsPanel">';
				
				echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->assets_url . '/loader.gif\');height:64px;"></div>';
				
				echo'<iframe data-src="' . $this->parent->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
				
			echo '</div>';
			echo '</div>';										
		}
	}
	
	public function filter_export_buttons($buttons,$layer){
		
		if( empty($_GET['quick']) ){

			// download button
			
			if( empty($_GET['action']) || $_GET['action'] != 'edit' ){
				
				if( $layer->post_type == 'cb-default-layer' || $layer->output == 'canvas' || $layer->output == 'image' || $layer->output == 'inline-css' || $layer->output == 'external-css' || $layer->is_element ){
			
					if( $layer->output == 'canvas' || $layer->output == 'image' ){
					
						$buttons['downloadImgBtn'] = '<span class="glyphicon glyphicon-export" aria-hidden="true"></span> Download image';
					}
					else{
						
						$buttons['downloadImgBtn'] = '<span class="glyphicon glyphicon-camera" aria-hidden="true"></span> Take a screenshot';
					}					
				}
			}
		}
		
		return $buttons;
	}
	
	public function filter_navbar_settings($layer){

		if( !$layer->is_media && ( $layer->post_type != 'cb-default-layer' ) ){
	
			if( $this->parent->user->can_edit ){
				
				echo'<li style="position:relative;">';
					
					echo '<a target="_blank" href="' . $layer->urls['backend'] . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

				echo'</li>';
				
				if( $layer->post_type == 'cb-default-layer' && empty($layer->post_title) ){
				
					echo'<li style="position:relative;">';
						
						echo '<a target="_self" href="' . $layer->urls['edit'] . '"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

					echo'</li>';
				}
			}
		}
	}
	
	public function filter_js_settings($js,$layer){
		
		if( $layer->output == 'image' ){
			
			if( $layer->post_type == 'attachment' ){
				
				$attachment_url = wp_get_attachment_url($layer->ID );
			}
			elseif( $this->parent->layer->layerImageTpl->post_type == 'attachment' ){
				
				$attachment_url = wp_get_attachment_url($this->parent->layer->layerImageTpl->ID);
			}
			else{
				
				$attachment_url = trim(strip_tags(apply_filters('the_content',$this->parent->layer->layerImageTpl->post_content)));		
			}
			
			$js .= ' var layerImageTpl = "' . LTPLE_Editor::get_image_proxy_url($attachment_url) . '";' . PHP_EOL;
		}
		else{
			
			if( !empty($_POST) || empty($layer->urls['preview']) ){
				
				// content based preview
				
				$content = $this->parent->layer->render_output();

				$content .= '<script type="text/javascript" id="jquery" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>';
				$content .= '<script type="text/javascript" id="jqueryui" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';
				$content .= '<script>
					;(function($){

						$(document).ready(function(){
							
								
							$(\'a\').click(function(e) {
								
								e.preventDefault();
							});

						});			
								
					})(jQuery);
				</script>';	
				
				$js .= ' var layerContent = "' . base64_encode($content) . '";' . PHP_EOL;
			}
			else{
				
				// url based preview

				$js .= ' var layerUrl	= "' . $layer->urls['preview'] . '";' . PHP_EOL;
			}

			//include quick edit
			 
			if( isset($_GET['quick']) ){
				
				$js .= ' var quickEdit = true;' .PHP_EOL;
			}
			else{
				
				$user = $this->parent->get_current_user();
				
				if( !$user->plan['info']['total_price_amount'] > 0 ){
					
					$js .= ' var quickEdit = true;' .PHP_EOL;
				}
				else{
					
					$js .= ' var quickEdit = false;' .PHP_EOL;
				}
			}
			
			//include page def
			
			if( $this->parent->layer->pageDef != '' ){
				
				$js .= ' var pageDef = ' . $this->parent->layer->pageDef . ';' .PHP_EOL;
			}
			else{
				
				$js .= ' var pageDef = {};' .PHP_EOL;
			}
			
			//include line break setting

			$js .= ' var disableReturn 	= true;' .PHP_EOL;
			$js .= ' var autoWrapText 	= false;' .PHP_EOL;
		}

		return $js;
	}
	
	public function filter_editor_script($editor){
		
		if( !is_admin() ){
			
			$modal_css = 'position:absolute;top:0;left:0;right:0;width:100%!important;margin:0;bottom:0;';
					
			$iframe_css = 'margin-top:-64px;position:relative;width:100%;top:0;bottom:0;border:0;height:calc( 100vh - 55px);'; 
			
			$editor .= '<div class="modal fade" id="media_library_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
				$editor .= '<div class="modal-dialog modal-lg" role="document" style="' . $modal_css . '">';
					$editor .= '<div class="modal-content">';
					
						$editor .= '<div class="modal-header">';
							$editor .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>';
							$editor .= '<h3 class="modal-title text-left">Media library</h3>';
						$editor .= '</div>';
						
						$editor .= '<div id="media_library_container"></div>';
						$editor .= '<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->assets_url . 'loader.gif\');height:64px;"></div>';
						$editor .= '<iframe id="media_library_iframe" src="" data-src="' . $this->parent->urls->media . '?output=widget" style="' . $iframe_css . '"></iframe>';
					
					$editor .= '</div>';
				$editor .= '</div>';
			$editor .= '</div>';
			
			$editor .= '<div class="modal fade" id="bookmarks_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
				$editor .= '<div class="modal-dialog modal-lg" role="document" style="'.$modal_css.'">';
					$editor .= '<div class="modal-content">';
					
						$editor .= '<div class="modal-header">';
							$editor .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>';
							$editor .= '<h3 class="modal-title text-left">Media Library</h3>';
						$editor .= '</div>';
						
						$editor .= '<div id="media_library_container"></div>';
						
						$editor .= '<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->assets_url . 'loader.gif\');height:64px;"></div>';
						$editor .= '<iframe id="bookmarks_iframe" src=""  data-src="' . $this->parent->urls->media . 'user-payment-urls/?output=widget" style="' . $iframe_css . '"></iframe>';
					
					$editor .= '</div>';
				$editor .= '</div>';
			$editor .= '</div>';
		}
		
		return $editor;
	}
	
	public function add_actions_scripts(){
		
		if( $this->actions = apply_filters('ltple_admin_editor_actions',array()) ){
			
			// add style
			
			wp_register_style($this->parent->_token . '-admin-actions', false,array());
			wp_enqueue_style($this->parent->_token . '-admin-actions');

			wp_add_inline_style($this->parent->_token . '-admin-actions', $this->get_actions_style() );
			
			// add script
			
			wp_register_script( $this->parent->_token . '-admin-actions', '', array( 'jquery' ) );
			wp_enqueue_script( $this->parent->_token . '-admin-actions' );

			wp_add_inline_script( $this->parent->_token . '-admin-actions', $this->get_actions_script() );
		
			add_filter('admin_footer',array( $this, 'add_actions_footer' ) );
		}
	}
	
	public function filter_editor_row_actions($actions,$post){
	
		if( !empty($this->actions) ){
			
			$layer = LTPLE_Editor::instance()->get_layer($post);
					
			foreach( $this->actions as $slug => $name ){
				
				if( $this->parent->layer->is_html_output($layer->output) ){
					
					if( $slug == 'edit-with-ltple' ){
						 
						$actions[$slug] = '<a href="' . $layer->urls['edit'] . '">'.$name.'</a>';
					}
					elseif( $slug == 'refresh-preview' ){
						
						$source = get_preview_post_link($post->ID);
						
						// TODO differentiate actions with slug 
						
						$action = '<div id="action-buttons-' . $post->ID . '">';

							$action .= '<a href="#refreshPreview" data-id="' . $post->ID . '" data-title="preview for ' . $post->post_title . '" data-source="' . $source . '" data-toggle="dialog" data-target="#actionConsole" class="action-button">';
								
								$action .= $name;
								 
							$action .= '</a>';

						$action .= '</div>';

						$actions[$slug] = $action;
					}
				}
			}
		}

		return $actions;
	}
	
	public function duplicate_post_type(){
		
		if( current_user_can( 'administrator' ) ){
			
			if( !empty($_POST['id']) && !empty($_POST['title']) ){
				
				$post_id = $_POST['id'];
				
				if( $post = get_post($post_id,ARRAY_A) ){
					
					unset(
					
						$post['ID'],
						$post['post_name'],
						$post['post_author'],
						$post['post_date'],
						$post['post_date_gmt'],
						$post['post_modified'],
						$post['post_modified_gmt']
					);
					
					$post['post_title'] 	= $_POST['title'];
					$post['post_status'] 	= 'draft';
					
					if( $new_id = wp_insert_post($post) ){
						
						// duplicate all post meta
						
						if( $meta = get_post_meta($post_id) ){
				
							foreach($meta as $name => $value){
								
								if( isset($value[0]) ){
									
									update_post_meta( $new_id, $name, maybe_unserialize($value[0]) );
								}
							}
						}
						
						// duplicate all taxonomies
						
						if( $taxonomies = get_object_taxonomies($post['post_type']) ){
						
							foreach( $taxonomies as $taxonomy ) {
								
								if( $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs')) ){
								
									wp_set_object_terms($new_id, $terms, $taxonomy, false);
								}
							}
						}
						
						// redirect to new post
						
						wp_redirect(get_admin_url().'post.php?post='.$new_id.'&action=edit');
						exit;
					}
				}
			}
		}
		
		if( !empty($_POST['ref']) ){
			
			wp_redirect($_POST['ref']);
			exit;
		}
	}
	
	public function get_actions_style(){
		
		$style = '
			
			.row-actions{
				
				margin-top:10px;
				width:60vw;
			}
			
			.row-actions div{
				
				display:inline-block;
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
	
	public function get_actions_script(){
		
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
							
							$("#actionLogs").append(text);
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
					
					// bind duplicate
					
					$(".duplicate-button").on("click",function(){
						
						var id = $(this).attr("data-id");
						
						var form = "<form action=\"' . get_admin_url() . 'admin-post.php\" method=\"post\">";
							
							form += "<input type=\"hidden\" name=\"action\" value=\"duplicate\">";
							form += "<input type=\"hidden\" name=\"id\" value=\"" + id + "\">";
							form += "<input type=\"hidden\" name=\"ref\" value=\"' . $this->parent->urls->current . '\">";
							
							form += "<input type=\"text\" name=\"title\" value=\"\" placeholder=\"New Title\" class=\"required\" required>";
							
							form += "<button class=\"button\" type=\"submit\" id=\"duplicateBtn\">Duplicate</button>";
							
						form += "</form>";
						
						$("#duplicateForm").empty().append(form);
						
					});
					
					// bind action buttons

					$(".action-button").on("click",function(){
						
						var id 		= $(this).attr("data-id");
						var title 	= $(this).attr("data-title");
						var source 	= $(this).attr("data-source");

						var screenshotUrl 	= "' . get_option( $this->parent->_base . 'server_url') . '";
						var uploaderUrl		= "' . get_admin_url() . '";
						
						console.info("<b>Processing " + title + "...</b>");
					
						var html = "<div id=\"meter-" + id + "\" class=\"action-meter\">";
							
							html += "<span class=\"progress\" style=\"width:0%;\"></span>";
							
						html += "</div>";

						console.info(html);								
						
						$.ajaxQueue({
							
							type 		: "GET",
							url  		: source,
							cache		: false,
							beforeSend	: function(){
								
							
							},
							error: function() {
							
								console.error(source + " error");
																								
								$meter.hide();
								$btns.find("button").prop("disabled",false);
							},
							success: function(htmlDoc) {
							
								var proto = window.location.href.split("/")[0];
								
								// get total requests
								
								$("#meter-" + id + " .progress").css("width", ( 100 / 3 ) + "%");
						
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

												$("#meter-" + id + " .progress").css("width", "100%");
												
												$("#meter-" + id + " .progress").bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(){
													
													$("#meter-" + id).after("<div class=\"completed\">Completed!</div>").remove();
												});
											}
										});
									},
									complete: function(){

										$("#meter-" + id + " .progress").css("width", ( 100 * 2 / 3 ) + "%");
									}
								});
							},
							complete: function(){
								
								
							}
						});
					});
				});
				
			})(jQuery);
		';	

		return $script;
	}
	
	public function add_actions_footer(){
		
		echo '<div id="actionConsole" style="display:none;" title="Action Console">';
			
			echo '<div id="actionLogs" style="height:50vh;width:50vw;">';

			echo '</div>';				
			
		echo '</div>';
		
		echo '<div id="duplicateItem" style="display:none;" title="Duplicate">';
			
			echo '<div id="duplicateForm" style="min-width:300px;">';

			echo '</div>';	
			
		echo '</div>';
	}
}

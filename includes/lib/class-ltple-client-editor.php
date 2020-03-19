<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Editor { 
	
	public $parent;

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
			
			return $query_vars;
			
		}, 1);

		add_filter( 'template_redirect', array( $this, 'get_editor' ),1);	
		
		add_filter( 'ltple_editor_iframe_url', array( $this, 'get_iframe_url' ),1);	
		
		add_filter( 'ltple_editor_dashboard_url', array( $this, 'get_dashboard_url' ),1);	
		
		add_filter( 'ltple_right_editor_navbar', array( $this, 'get_right_navbar' ),1);	
		
		add_filter( 'ltple_editor_js_settings', array( $this, 'get_js_settings' ),1);	
		
		add_filter( 'ltple_editor_content', array( $this, 'add_editor_modals' ),1);
	}
	
	public function init_editor(){
		
		// add rewrite rules
		
		add_rewrite_rule(
		
			'edit/?$',
			'index.php?edit=editor',
			'top'
		);
	}
	
	public function get_editor(){
		
		if( $slug = get_query_var('edit') ){

			// get layer range
			
			$terms = wp_get_object_terms( $this->parent->layer->id, 'layer-range' );
			
			$this->parent->layer->range = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');
			
			// get layer price
			
			$this->parent->layer->price = ( !empty($this->parent->layer->range) ? $this->parent->layer->get_plan_amount($this->parent->layer->range,'price') : 0 );
			
			// Custom default layer post
			
			if( $this->parent->layer->defaultId > 0 ){
				
				remove_all_filters('content_save_pre');
				//remove_filter( 'the_content', 'wpautop' ); // remove line breaks from post_content
			}

			// get editor iframe
			
			if( !empty($this->parent->layer->key) ){
				
				if( $this->parent->user->loggedin === true && $this->parent->layer->type!='' && $this->parent->server->url!==false ){
					
					if( $this->parent->layer->key == md5( 'layer' . $this->parent->layer->id . $this->parent->_time )){
						
						if( !empty($_POST['base64']) && !empty($_POST['domId']) ){
							
							// handle cropped image upload
							
							echo $this->parent->image->upload_editor_image($this->parent->layer->id . '_' . $_POST['domId'] . '.png' ,$_POST['base64']);
						}
						elseif( !empty($_FILES) && !empty($_POST['location']) && $_POST['location'] == 'media' ){
								
							// handle canvas image upload
							
							echo $this->parent->image->upload_collage_image();
						}
						else{
							
							include( $this->parent->views . '/editor-proxy.php' );
						}
					}
					else{
						
						echo 'Malformed iframe request...';				
					}
				}
				else{
					
					echo 'Error starting editor...';
				}
			}
			elseif( $this->parent->user->has_layer ){
				
				if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ){
					
					include( $this->parent->views . '/editor-panel.php' );
				}
				elseif( ( !$this->parent->user->is_editor || !isset($_GET['edit']) ) && !isset($_GET['quick']) && ( $this->parent->layer->type == 'cb-default-layer' || $this->parent->layer->is_media ) ){

					include( $this->parent->views . '/editor-starter.php' );
				}
				elseif( $this->parent->layer->type == 'user-layer' && !$this->parent->user->plan["info"]["total_price_amount"] > 0 ){
					
					echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
						
						echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

					echo'</div>';
				}
				elseif( $this->parent->layer->is_editable($this->parent->layer->layerOutput) ){
					 
					include( get_template_directory() . '/ltple/editor.php' );
				}
				else{
					
					echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
						
						echo '<div class="alert alert-warning">This template is not editable...</div>';

					echo'</div>';		
				}
			}
			else{
				
				include( $this->parent->views . '/restricted.php' );
			}
			
			exit;
		}
	}
	
	public function get_iframe_url($iframe_url){
		
		$iframe_url = $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '&lk=' . md5( 'layer' . $this->parent->layer->id . $this->parent->_time ) . '&_=' . $this->parent->_time;

		return $iframe_url;
	}
	
	public function get_dashboard_url($dashboard_url){
	
		$dashboard_url = $this->parent->urls->dashboard;
		
		return $dashboard_url;
	}
	
	public function get_right_navbar(){
		
		// elements button

		$elemLibraries = array();
		
		if( !empty($this->parent->layer->defaultElements['name'][0]) ){
			
			$elemLibraries[] = $this->parent->layer->defaultElements;
		}			
		
		if( !empty($this->parent->layer->layerHtmlLibraries) ){
		
			foreach( $this->parent->layer->layerHtmlLibraries as $term ){
				
				$elements = get_option( 'elements_' . $term->slug );

				if( !empty($elements['name'][0]) ){
					
					$elemLibraries[] = $elements;
				}
			} 
		}
		
		if( !empty($elemLibraries) ){
			
			echo'<style>'.PHP_EOL;

				echo'#dragitemslistcontainer {
					
					margin: 0;
					padding: 0;
					width: 100%;
					display:inline-block;
				}

				#dragitemslistcontainer li {
					
					float: left;
					position: relative;
					text-align: center;
					list-style: none;
					cursor: move;
					cursor: grab;
					cursor: -moz-grab;
					cursor: -webkit-grab;
				}

				#dragitemslistcontainer li:active {
					cursor: grabbing;
					cursor: -moz-grabbing;
					cursor: -webkit-grabbing;
				}

				#dragitemslistcontainer span {
					
					float: left;
					position: absolute;
					left: 0;
					right: 0;
					background: rgba(52, 87, 116, 0.49);
					color: #fff;
					font-weight: bold;
					padding: 15px 5px;
					font-size: 16px;
					line-height: 25px;
					margin: 48px 4px 0 4px;
				}

				#dragitemslistcontainer li img {
					margin:3px 2px;
				}';		

			echo'</style>'.PHP_EOL;							
			
			echo '<button style="margin-left:2px;margin-right:2px;border:none;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveTplEditorDndDialog" data-height="300" data-width="500" data-resizable="false">Insert</button>';
	
			echo '<div id="LiveTplEditorDndDialog" title="Elements library" style="display:none;">';
			echo '<div id="LiveTplEditorDndPanel">';
			
				echo '<div id="dragitemslist">';
					
					$list = [];
					
					foreach( $elemLibraries as $elements ){
				
						if( !empty($elements['name']) ){
							
							foreach( $elements['name'] as $e => $name ){
								
								if( !empty($elements['type'][$e]) ){
								
									$type = $elements['type'][$e];
									
									$content = str_replace( array('\\"','"',"\\'"), "'", $elements['content'][$e] );
									
									$drop = ( !empty($elements['drop'][$e]) ? $elements['drop'][$e] : 'out' );
									
									if( !empty($content) ){
									
										$item = '<li draggable="true" data-drop="' . $drop . '" data-insert-html="' . $content . '">';
										
											$item .= '<span>'.$name.'</span>';
										
											if( !empty($elements['image'][$e]) ){
										
												$item .= '<img title="'.$name.'" height="150" src="' . $elements['image'][$e] . '" />';
											}
											else{
												
												$item .= '<img title="'.$name.'" height="150" src="' . $this->parent->server->url . '/c/p/live-template-editor-resources/assets/images/flow-charts/corporate/testimonials-slider.jpg" />';
												
												//$item .= '<div style="height: 115px;width: 150px;background: #afcfff;border: 4px solid #fff;"></div>';
											}
										$item .= '</li>';
										
										$list[$type][] = $item;
									}
								}
							}
						}
					}
						
					//echo'<div class="library-content">';
							
						echo'<ul class="nav nav-pills" role="tablist">';

						$active=' class="active"';
						
						foreach($list as $type => $items){
							
							echo'<li role="presentation"'.$active.'><a href="#' . $type . '" aria-controls="' . $type . '" role="tab" data-toggle="tab">'.ucfirst(str_replace(array('-','_'),' ',$type)).' <span class="badge">'.count($list[$type]).'</span></a></li>';
							
							$active='';
						}							

						echo'</ul>';
						
					//echo'</div>';

					echo'<div id="dragitemslistcontainer" class="tab-content row">';
						
						$active=' active';
					
						foreach($list as $type => $items){
							
							echo'<ul role="tabpanel" class="tab-pane'.$active.'" id="' . $type . '">';
							
							foreach($items as $item){

								echo $item;
							}
							
							echo'</ul>';
							
							$active='';
						}
						
					echo'</div>';
				
				echo '</div>';
				
			echo '</div>';
			echo '</div>';				
		}

		// insert button
		
		if( $this->parent->layer->layerOutput == 'image' ){

			echo '<button style="margin-left:2px;margin-right:2px;border: none;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveImgEditorElements" data-height="450" data-width="75%" data-resizable="false">Insert</button>';
	
			echo '<div id="LiveImgEditorElements" title="Elements library" style="display:none;">'; 
			echo '<div id="LiveImgEditorElementsPanel">';
				
				echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
				
				echo'<iframe data-src="' . $this->parent->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
				
			echo '</div>';
			echo '</div>';										
		}
			
		if( is_admin() || ( $this->parent->layer->type != 'cb-default-layer' ) ){
			
			if( empty($_GET['action']) || $_GET['action'] != 'edit' ){
				
				if( $this->parent->layer->layerOutput == 'canvas' ){
					
					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</button>';
						
					echo '</div>';
				}
				elseif( $this->parent->layer->layerOutput == 'image' ){

					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</button>';
						
					echo '</div>';							
				}
			}
			else{
				
				if( $this->parent->layer->is_downloadable_output($this->parent->layer->layerOutput) ){
					
					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<a href="' . apply_filters('ltple_downloadable_url','#download',$this->parent->layer->id,$this->parent->layer->layerOutput) . '" class="btn btn-sm" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</a>';
						
					echo '</div>';								
				}							
			}							
			
			if( ( is_admin() || $this->parent->user->has_layer ) && !$this->parent->layer->is_media ){
				
				// save button
				
				if( !empty($this->parent->user->layer->post_title) && ( empty($_GET['action']) || $_GET['action'] != 'edit' ) ){

					$post_title = $this->parent->user->layer->post_title;
					
					echo'<form style="display:inline-block;margin:0;" target="_parent" action="' . $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '" id="savePostForm" method="post">';
						
						echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
						echo'<input type="hidden" name="postContent" id="postContent" value="">';
						echo'<input type="hidden" name="postCss" id="postCss" value="">';
						echo'<input type="hidden" name="postJs" id="postJs" value="">';
						echo'<input type="hidden" name="postAction" id="postAction" value="save">';
						echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
						 
						wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
						
						echo'<input type="hidden" name="submitted" id="submitted" value="true">';
						
						echo'<div id="navLoader" style="float:left;margin-right:10px;display:none;"><img src="' . $this->parent->assets_url . 'loader.gif" style="height: 20px;"></div>';				
						
						echo'<button style="border:none;" class="btn btn-sm btn-success" type="button" id="saveBtn">Save</button>';
						
					echo'</form>';
					
					if( !$this->parent->layer->is_media ){
										
						$settings_url = is_admin() ? get_admin_url() . 'post.php?post=' . $_GET['post'] . '&action=edit' : $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '&action=edit';
						
						echo'<a href="' . $settings_url . '" style="background-color:#58cac5;color:#fff;border:none;margin-left:2px;" class="btn btn-sm" type="button">Settings</a>';
					}
				}
				
				// view button 
				
				if( $this->parent->layer->has_preview($this->parent->layer->type) ){
					
					echo '<a target="_blank" class="btn btn-sm" href="' . get_preview_post_link($this->parent->layer->id) . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
				}
			}
		}
		
		if( $this->parent->layer->type == 'cb-default-layer' && $this->parent->user->is_editor ){
			
			// save button
			
			$post_title = $this->parent->layer->title;
			
			echo'<form style="display:inline-block;" target="_parent" action="' . $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '" id="savePostForm" method="post">';
				
				echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
				echo'<input type="hidden" name="postContent" id="postContent" value="">';
				echo'<input type="hidden" name="postCss" id="postCss" value="">';
				echo'<input type="hidden" name="postJs" id="postJs" value="">';
				echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
				 
				wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
				
				echo'<input type="hidden" name="submitted" id="submitted" value="true">';
				
				echo'<div id="navLoader" style="margin-right:10px;display:none;"><img src="' . $this->parent->assets_url . 'loader.gif" style="height: 20px;"></div>';				

				if( isset($_GET['edit']) ){
					
					echo'<input type="hidden" name="postAction" id="postAction" value="update">';
					
					echo'<button style="border:none;" class="btn btn-sm btn-success" type="button" id="saveBtn">Update</button>';
				}
				else{
					
					echo'<input type="hidden" name="postAction" id="postAction" value="save">';
				}
				
			echo'</form>';
		}
			
		if( $this->parent->layer->is_editable($this->parent->layer->layerOutput) ){
			
			if( !$this->parent->layer->is_media && ( $this->parent->layer->type != 'cb-default-layer' || $this->parent->user->is_editor ) ){
				
				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 15px;height:28px;background: none;border: none;color: #a5a5a5;box-shadow: none;"><span class="glyphicon glyphicon-cog icon-cog" aria-hidden="true"></span></button>';
										
					echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
						
						echo'<li style="position:relative;">';
						
							echo '<a href="#duplicateLayer" data-toggle="dialog" data-target="#duplicateLayer">Duplicate Template ' . ( $this->parent->layer->type == 'cb-default-layer' ? '<span class="label label-warning pull-right">admin</span>' : '' ) . '</a>';

							echo'<div id="duplicateLayer" title="Duplicate Template">';
								
								echo'<form class="" style="width:250px;display:inline-block;" target="_parent" action="' . $this->parent->urls->current . '" id="duplicatePostForm" method="post">';
									
									echo'<input type="text" name="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" style="margin:7px 0;">';
									echo'<input type="hidden" name="postAction" id="postAction" value="duplicate">';
									echo'<input type="hidden" name="postContent" value="">';
									echo'<input type="hidden" name="postCss" value="">'; 
									echo'<input type="hidden" name="postJs" value="">'; 									
									echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
									
									wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
									
									echo'<input type="hidden" name="submitted" id="submitted" value="true">';
									
									echo'<div class="ui-helper-clearfix ui-dialog-buttonset">';

										echo'<button class="btn btn-xs btn-primary pull-right" type="submit" id="duplicateBtn" style="border-radius:3px;">Duplicate</button>';
								 
									echo'</div>';
									
								echo'</form>';								
								
							echo'</div>';						
							
						echo'</li>';
						
						echo'<li style="position:relative;">';
						
							echo '<a href="' . $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '&action=edit">Edit Settings</a>';
						
						echo'</li>';

						if( $this->parent->user->is_editor ){
							
							echo'<li style="position:relative;">';
								
								echo '<a target="_blank" href="' . get_edit_post_link( $this->parent->layer->id ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

							echo'</li>';
							
							if( $this->parent->layer->type == 'cb-default-layer' && empty($this->parent->user->layer->post_title) ){
							
								echo'<li style="position:relative;">';
									
									echo '<a target="_self" href="' . $this->parent->urls->edit . '?uri=' . $this->parent->layer->id . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

								echo'</li>';
							}
							
							if( $this->parent->layer->layerOutput != 'image' ){
							
								echo'<li style="position:relative;">';
									
									echo '<a target="_blank" href="' . get_post_permalink( $this->parent->layer->id ) . '"> Preview Template <span class="label label-warning pull-right">admin</span></a>';

								echo'</li>';
							}
						}
						
					echo'</ul>';
					
				echo'</div>';
			}
		}
					
		
	}
	
	public function get_js_settings($js){
		
		if( $this->parent->layer->layerOutput == 'image' ){
			
			if( $this->parent->layer->layerImageTpl->post_type == 'attachment' ){
				
				$attachment_url = wp_get_attachment_url($this->parent->layer->layerImageTpl->ID );
			}
			else{
				
				$attachment_url = trim(strip_tags(apply_filters('the_content',$this->parent->layer->layerImageTpl->post_content)));		
			}
			
			$js .= ' var layerImageTpl = "' . $this->parent->layer->layerImgProxy . urlencode($attachment_url) . '";' . PHP_EOL;
		}
		else{
			
			if( !empty($_POST) || !$this->parent->layer->is_local_page($this->parent->layer->id) ){
				
				// content based preview
				
				$content = $this->parent->layer->output_layer();

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
				
				$preview = add_query_arg(array(
					
					'preview' => 'ltple',
					
				),get_preview_post_link($this->parent->layer->id));
				
				$js .= ' var layerUrl	= "' . $preview . '";' . PHP_EOL;
			}
			
			if( $this->parent->layer->layerOutput != '' ){
				
				$js .= ' var layerOutput = "' . $this->parent->layer->layerOutput . '";' . PHP_EOL;
			}
			
			$js .= ' var layerSettings = ' . json_encode($this->parent->layer->layerSettings) . ';' .PHP_EOL;
			
			//include image proxy
			
			if( $this->parent->layer->layerImgProxy != '' ){
			
				$js .= ' var imgProxy = " ' . $this->parent->layer->layerImgProxy . '";' . PHP_EOL;				
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

			if( !is_array( $this->parent->layer->layerOptions ) ){
				
				$js .= ' var disableReturn 	= true;' .PHP_EOL;
				$js .= ' var autoWrapText 	= false;' .PHP_EOL;
			}
			else{
				
				if( !in_array('line-break',$this->parent->layer->layerOptions) ){
					
					$js .= ' var disableReturn = true;' .PHP_EOL;
				}
				else{
					
					$js .= ' var disableReturn = false;' .PHP_EOL;
				}
				
				if(in_array('wrap-text',$this->parent->layer->layerOptions)){
					
					$js .= ' var autoWrapText = true;' .PHP_EOL;
				}
				else{ 
					
					$js .= ' var autoWrapText = false;' .PHP_EOL;
				}
			}
			
			//include icon settings
			
			$enableIcons = 'false';
			
			if( in_array_field( 'font-awesome-4-7-0', 'slug', $this->parent->layer->layerCssLibraries ) ){
				
				$enableIcons = 'true';
			}
			
			$js .= ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
			
			//include forms
			
			if( $this->parent->layer->layerForm == 'importer' ){
				
				$js .= ' var layerForm = "' . $this->parent->layer->layerForm . '";';
			}
		}
		
		return $js;
	}
	
	public function add_editor_modals($editor){
		
		$editor = do_shortcode($editor);
		
		$modal_css = 'position:absolute;top:0;left:0;right:0;width:100%!important;margin:0;bottom:0;';
				
		$iframe_css = 'margin-top:-64px;position:relative;width:100%;top:0;bottom:0;border:0;height:40vh;'; 
		
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
		
		return $editor;
	}
}

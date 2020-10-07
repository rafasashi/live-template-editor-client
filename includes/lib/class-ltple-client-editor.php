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
		
		add_filter( 'ltple_editor_navbar_settings', array( $this, 'filter_navbar_settings' ),1);
		
		add_filter( 'ltple_editor_js_settings', array( $this, 'filter_js_settings' ),1,2);	
		
		add_filter( 'ltple_editor_script', array( $this, 'filter_editor_script' ),1);
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
			
			if( !empty($_GET['uri']) ){
				
				if( !empty($_GET['lk']) ){
					
					if( !empty($_POST['base64']) && !empty($_POST['domId']) ){
						
						// handle cropped image upload
						
						echo $this->parent->image->upload_base64_image($this->parent->layer->id . '_' . $_POST['domId'] . '.png' ,$_POST['base64']);
					}
					elseif( !empty($_FILES) && !empty($_POST['location']) && $_POST['location'] == 'media' ){
							
						// handle canvas image upload
						
						echo $this->parent->image->upload_collage_image();
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
						
						include( $this->parent->views . '/editor-panel.php' );
					}
					elseif( ( !$this->parent->user->can_edit || !isset($_GET['edit']) ) && !isset($_GET['quick']) && ( $this->parent->layer->type == 'cb-default-layer' || $this->parent->layer->is_media ) ){

						include( $this->parent->views . '/editor-starter.php' );
					}
					elseif( $this->parent->layer->type == 'user-layer' && !$this->parent->user->plan["info"]["total_price_amount"] > 0 ){
						
						echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
							
							echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

						echo'</div>';
					}
					elseif( $this->parent->layer->is_editable_output($layer->output) ){
						
						if( empty($_POST) && !empty($this->parent->layer->layerForm) && $this->parent->layer->layerForm != 'none' && empty($this->parent->layer->layerContent) ){
							
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
				else{
					
					include( $this->parent->views . '/restricted.php' );
				}
				
				exit;
			}
		}
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
				
				$elements = get_option( 'elements_' . $term->slug );

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
			
			echo '<button style="margin-left:2px;margin-right:2px;border: none;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveImgEditorElements" data-height="450" data-width="75%" data-resizable="false">Insert</button>';
	
			echo '<div id="LiveImgEditorElements" title="Elements library" style="display:none;">'; 
			echo '<div id="LiveImgEditorElementsPanel">';
				
				echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
				
				echo'<iframe data-src="' . $this->parent->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
				
			echo '</div>';
			echo '</div>';										
		}
		
		if( $layer->post_type != 'cb-default-layer' ){

			// download button
			
			if( empty($_GET['action']) || $_GET['action'] != 'edit' ){
				
				if( $layer->output == 'canvas' ){
					
					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</button>';
						
					echo '</div>';
				}
				elseif( $layer->output == 'image' ){

					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</button>';
						
					echo '</div>';							
				}
			}
			else{
				
				if( $this->parent->layer->is_downloadable_output($layer->output) ){
					
					echo '<div style="margin:0 2px;" class="btn-group">';
						
						echo '<a href="' . apply_filters('ltple_downloadable_url','#download',$layer->ID,$layer->output) . '" class="btn btn-sm" style="border:none;background: #4c94af;">';
						
							echo 'Download';
						
						echo '</a>';
						
					echo '</div>';								
				}							
			}
		}
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
			
			//include icon settings
			
			$enableIcons = 'false';
			
			if( in_array_field( 'font-awesome-4-7-0', 'slug', $this->parent->layer->layerCssLibraries ) ){
				
				$enableIcons = 'true';
			}
			
			$js .= ' var enableIcons = '.$enableIcons.';' .PHP_EOL;
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
}
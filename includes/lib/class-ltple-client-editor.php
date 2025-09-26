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
        
        add_filter('ltple_element_active','__return_true');
		
		add_filter('query_vars', function( $query_vars ){
			
			if(!in_array('editor',$query_vars)){
				
				$query_vars[] = 'editor';
			}
			
			if(!in_array('edit',$query_vars)){
				
				$query_vars[] = 'edit';
			}
			
			return $query_vars;
			
		}, 1);
		
		add_filter('ltple_editor_frontend', array( $this, 'get_editor' ),1);
		
		add_filter('ltple_editor_iframe_url', array( $this, 'filter_iframe_url' ),1,2);	
		
		add_filter('ltple_editor_dashboard_url', array( $this, 'filter_dashboard_url' ),1);	
		
		add_filter('ltple_editor_settings_url', array( $this, 'filter_settings_url' ),1);	
		
		add_filter('ltple_editor_edit_url', array( $this, 'filter_edit_url' ),1);	
				
		add_filter('ltple_editor_elements', array( $this, 'filter_elements' ),1,2);	
				
		add_filter('ltple_right_editor_navbar', array( $this, 'filter_right_navbar' ),1);			
		
		add_filter('ltple_editor_export_buttons', array( $this, 'filter_export_buttons' ),9999,2);
		
		add_filter('ltple_editor_navbar_settings', array( $this, 'filter_navbar_settings' ),1);
		
		add_filter('ltple_editor_js_settings', array( $this, 'filter_js_settings' ),1,2);	

		add_filter('ltple_editor_script', array( $this, 'filter_editor_script' ),0,2);
		
		add_filter('ltple_editor_media_lib_url', function($url,$layer){
			
			if( !is_admin() || $this->parent->layer->is_app_output($layer->output) ){
				
				$url = $this->parent->urls->media . '?output=widget&section=images';
			}
			
			return $url;

		},0,2);
		
		add_filter('ltple_editor_bookmark_lib_url', function($url,$layer){
			
			if( !is_admin() ){
				
				$url = $this->parent->urls->media . 'user-payment-urls/?output=widget';
			}

			return $url;
			
		},0,2);
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
						
							echo LTPLE_Editor::upload_base64_image($layer, $_POST['domId'], $_POST['base64'],'editor');
						}
						elseif( !empty($_POST['url']) ){
							
							$crop = !empty($_POST['crop']) ? json_decode(stripslashes($_POST['crop']),true) : false;
							
							echo LTPLE_Editor::upload_image_url($layer, $_POST['domId'], $_POST['url'], $crop);
						}
					}
					elseif( $user_email = $this->parent->plan->get_license_holder_email($this->parent->user) ){
						
                        LTPLE_Editor::get_remote_script($layer,array(
							
							'key'		=> sanitize_title($_GET['lk']),
							'user'		=> $this->parent->ltple_encrypt_str($user_email),
							'is_local' 	=> $this->parent->layer->is_local ? true : false,				
							'plan_url'	=> $this->parent->urls->plans,
						
						));
						
						echo 'Malformed request...';
					}
                    else{
                        
                        echo 'Login to use the service';
                    }
				}
				elseif( $this->parent->plan->user_has_layer($layer->ID) ){
                    
					if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ){
                        
						wp_register_script('jquery-validate', esc_url( $this->parent->assets_url ) . 'js/jquery.validate.js', array( 'jquery' ), $this->parent->_version );
						wp_enqueue_script('jquery-validate');
						
						wp_register_script( $this->parent->_token . '-editor-panel', '', array( 'jquery','jquery-notify','jquery-validate' ) );
						wp_enqueue_script( $this->parent->_token . '-editor-panel' );
						wp_add_inline_script( $this->parent->_token . '-editor-panel', $this->get_editor_panel_script());
								
						wp_register_style( $this->parent->_token . '-editor-panel', false, array());
						wp_enqueue_style(  $this->parent->_token . '-editor-panel' );
						wp_add_inline_style( $this->parent->_token . '-editor-panel', $this->get_editor_panel_style());
						
						$layer_type = $this->parent->layer->get_layer_type($_REQUEST['uri']);

						if( in_array($layer_type->output,array(
						
							'hosted-page',
							'home-page',
							
						))){
							
							add_action('ltple_list_sidebar',array($this->parent->profile,'get_sidebar'),10,3);
						}
						else{
							
							add_action('ltple_list_sidebar',array($this->parent->dashboard,'get_sidebar'),10,3);
						}

						include( $this->parent->views . '/editor-panel.php' );
					}
                    else{
                        
                        $layer_plan = $this->parent->plan->get_layer_plan( $layer->ID, 'min' );
                       
                        if( $layer->is_storable && !isset($_GET['quick']) && ( $layer->post_type == 'cb-default-layer' || $layer->is_media ) && ( !$this->parent->user->can_edit || !isset($_GET['edit']) ) ){
                           
                            if( !isset($_GET['period_refreshed']) && $layer_plan['amount'] > 0 && $this->parent->user->remaining_days < 0 ){
                                
                                include( $this->parent->views . '/subscription-refresh.php' );
                            }
                            else{
                                
                                include( $this->parent->views . '/editor-starter.php' );
                            }
                        }
                        else{
                            
                            if( $layer->post_type != 'cb-default-layer' && $layer_plan['amount'] > 0 && !$this->parent->user->plan["info"]["total_price_amount"] > 0 ){
                                
                                echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
                                    
                                    echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

                                echo'</div>';
                            }
                            elseif( $this->parent->layer->is_editable_output($layer->output) ){
                                
                                if( $layer_plan['amount'] > 0 && $this->parent->user->remaining_days < 0 && $this->parent->user->plan["info"]["total_price_amount"] > 0 ){
                                    
                                    //check license
                                    
                                    if( !isset($_GET['period_refreshed']) ){
                                        
                                        // refresh user period
                                        
                                        $this->parent->users->remote_update_period($this->parent->user->ID);
                                        
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
                                elseif( !$this->parent->layer->is_app_output($layer->output) && !empty($layer->form) && is_array($layer->form) && empty($_POST) && empty($layer->html) ){
                                   
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

						$("#savePostForm").trigger("submit");
					});
					
					$("#savePostForm").validate({
						
						invalidHandler: function(event, validator){
							
							$.notify("Missing field",{
								
								className: "error",
								position: "top center"
							});
						},
						submitHandler: function(form){

							$("#navLoader").css("display","inline-block");
							
							var action 	= $(form).attr("action");
							
							var data 	= $(form).serialize();

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
									
									try {
					
										data = JSON.parse(data);
									}
									catch(e){
										
										data = JSON.parse(JSON.stringify(data));
									}

									if( typeof data.message != typeof undefined ){
										
										// object response
										
										var message = data.message;
										
										if( typeof data.callback != typeof undefined ){
											
											eval(data.callback);
										}		
									}							
									else{
										
										// text response
										
										var message = data;
									}
									
									$.notify( message, {
										
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
						}
					});
				});
					
			})(jQuery);			
		';
		
		
		return $script;
	}

	public function get_editor_panel_style(){
		
		$css = '';
		
		$css .= '.editor-tab-panel-body {';
			
			$css .= 'min-height:380px;';
			$css .= 'padding:10px 0;';
			$css .= 'width:100%;';
			$css .= 'display:inline-block;';
			
		$css .= '}';
		
		$css .= '.editor-tab-panel-body #toolbar {';
			
			$css .= 'position:absolute;';
			
		$css .= '}';
		
		$css .= '.editor-tab-panel-body tr {
			
			background-color:#fff !important;
		}';
		
		$css .= '.editor-tab-panel-body td {
			
			border:none !important;
		}';
		
		$css .= '.editor-tab-panel-body .fixed-table-toolbar {
			
			margin:0 10px -7px 0 !important;
		}';
		
		$css .= '.editor-tab-panel-body th, .editor-tab-panel-body .fixed-table-pagination {
			
			display:none;
		}';
		
		$css .= '.editor-tab-actions  {
			
			position:absolute;
			margin:8px;
		}';
		
		$css .= '.editor-tab-actions .dropdown-menu li .label {
			
			font-size:11px;
			margin:0;
		}';
		
		$css .= '.editor-tab-actions .btn, .editor-tab-actions .btn:hover, .editor-tab-actions .btn:active, .editor-tab-actions .btn:visited {
		
			border: 1px solid ' . $this->parent->settings->mainColor . ' !important;
			color:' . $this->parent->settings->mainColor . ' !important;
			background:#fff !important;
			margin-right:5px;
		}';

		// validation errors
		
		$css .= 'label.error{
			color: #D95C5C;
			padding: 0 10px;
		}';
		
		$css .= 'input.error,select.error,textarea.error{
			color: #D95C5C !important;
			border-color: #D95C5C !important;
		}';
		
		$css .= 'input.error:focus,select.error:focus,textarea.error:focus{
			outline: auto !important;
			border-color: inherit;
			-webkit-box-shadow: none;
			box-shadow: none;
		}';		
		
		$css .= '.editor-tab-panel-body .form-group {
		
			margin-bottom:5px;
		}';	

		return $css;		
	}

	public function filter_iframe_url($url,$layer){
		
		$url = apply_filters('ltple_client_iframe_url',$this->parent->urls->edit . '?uri=' . $layer->ID . '&lk=' . $layer->key . '&_=' . $layer->token,$layer);

		return $url;
	}
	
	public function filter_dashboard_url($dashboard_url){
	
		$dashboard_url = $this->parent->urls->dashboard;
		
		return $dashboard_url;
	}
	
	public function filter_settings_url($post_id){
		
		return $this->parent->urls->get_edit_url($post_id);
	}
	
	public function filter_edit_url($post_id){
		
		return $this->parent->urls->get_edit_url($post_id);
	}	
	
	public function filter_elements($elemLibraries,$layer){
			
        if( !empty($layer->default_id) ){
            
            $defaultElements = get_post_meta( $layer->default_id, 'layerElements', true );
            
            if( !empty($defaultElements['name'][0]) ){
                
                $elemLibraries[] = $defaultElements;
            }
            
            $theme = $this->parent->profile->get_current_theme();
            
            $themeId = !empty($theme->ID) ? $theme->ID : 0;
            
            if( $libraries = LTPLE_Element::get_libraries(array($themeId,$layer->default_id),'element')  ){
                
                foreach( $libraries as $term ){
                    
                    $elements = LTPLE_Element::get_library_elements($term);

                    if( !empty($elements['name'][0]) ){
                        
                        $elemLibraries[] = $elements;
                    }
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
					
					echo'<iframe data-src="' . $this->parent->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
					
				echo '</div>';
			echo '</div>';										
		}
	}
	
	public function filter_export_buttons($buttons,$layer){
		
		// download button
		
		if( empty($_GET['action']) || $_GET['action'] != 'edit' ){
			
			if( $layer->output == 'canvas' || $layer->output == 'image' || $layer->output == 'inline-css' || $layer->output == 'external-css' || $layer->is_element ){
		
				if( $layer->output == 'canvas' || $layer->output == 'image' ){
				
					$buttons['downloadImgBtn'] = '<span class="glyphicon glyphicon-export" aria-hidden="true"></span> Download image';
				}
				elseif( $this->parent->layer->is_html_output($layer->output) ){
					
					$buttons['downloadImgBtn'] = '<span class="glyphicon glyphicon-camera" aria-hidden="true"></span> Take a screenshot';
				}					
			}
		}

		return $buttons;
	}
	
	public function filter_navbar_settings($layer){
		
		$default_id = $this->parent->layer->get_default_id($layer->ID);
		
		if( $permalink = get_permalink($default_id) ){
			
			echo'<li style="position:relative;">';
			
				echo '<a href="'.$permalink.'" target="_blank">Template Info</a>';
			
			echo'</li>';
		}
		
		if( $this->parent->user->can_edit ){
            
			if( !empty($layer->urls['backend']) ){
				
				echo'<li style="position:relative;">';
					
					echo '<a target="_blank" href="' . $layer->urls['backend'] . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

				echo'</li>';
			}
		}
	}
	
	public function filter_js_settings($js,$layer){
		
        $layer_type = $this->parent->layer->get_layer_type($layer);
        
        if( $layer->output == 'image' ){
			
			if( $layer->post_type == 'attachment' ){
				
				$attachment_url = wp_get_attachment_url($layer->ID );
			}
            elseif( !empty($layer_type) ){
                
                $attachments = $this->parent->layer->get_layer_attachments($layer->default_id,$layer_type->storage);
                
                if( $image = reset($attachments) ){
                    
                    if( $image->post_type == 'attachment' ){
                        
                        $attachment_url = wp_get_attachment_url($image->ID);
                    }
                    else{
                        
                        $attachment_url = trim(strip_tags(apply_filters('the_content',$image->post_content)));		
                    }
                }
            }
			
			$js .= ' var layerImageTpl = "' . LTPLE_Editor::get_image_proxy_url($attachment_url) . '";' . PHP_EOL;
		}
		else{
			
			$js .= ' var mediaLibUrl = "' . add_query_arg(array('in_editor'=>true),apply_filters('ltple_editor_media_lib_url','',$layer,$layer_type)) . '";'. PHP_EOL;
			
            $js .= ' var bookmarkUrl = "' . add_query_arg(array('in_editor'=>true),apply_filters('ltple_editor_bookmark_lib_url','',$layer,$layer_type)) . '";'. PHP_EOL;
        
            $js .= ' var layerForm = ' . json_encode($layer->form) . ';' . PHP_EOL;
        
			$js .= ' var layerJson = "' . base64_encode($this->parent->layer->layerJson) . '";' . PHP_EOL;
            
            // content based preview
            
            $content = $this->parent->layer->render_output($layer) . PHP_EOL;
           
            if( !$this->parent->layer->is_app_output($layer->output) ){
                            
                $content .= '<script id="LiveTplEditorClientScript">
                    document.addEventListener("DOMContentLoaded", function() {
                        document.querySelectorAll("a").forEach(function(link) {
                            link.addEventListener("click", function(ev) {
                                ev.preventDefault();
                                return false;
                            }, false);
                        });
                    });
                </script>' . PHP_EOL;
            }
            
            $js .= ' var layerContent = "' . base64_encode($content) . '";' . PHP_EOL;
        
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
	
	public function filter_editor_script($editor,$layer){
		
		return $editor;
	}
}

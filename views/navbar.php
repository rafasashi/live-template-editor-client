<?php 
	
	$ltple = LTPLE_Client::instance();

	if( empty($_REQUEST['output']) || $_REQUEST['output'] != 'widget' ){

		// get navbar
		
		if( $ltple->profile->id > 0 ){
			
			echo'<div style="background: transparent;padding: 8px 15px;margin: 0;position: absolute;width: 100%;z-index: 1000;right: 0;left: 0;">';
		}
		else{
		
			echo'<div class="row" style="box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);background: rgb(236, 236, 236);padding: 8px 0;margin: 0;border-bottom: 1px solid #ddd;position: relative;">';
		}
		
			echo'<div class="col-xs-6 col-sm-4" style="z-index:10;padding:0 8px;">';			
				
				echo'<div class="pull-left">';
				
					echo'<button type="button" id="sidebarCollapse">';
							
						echo'<i class="glyphicon glyphicon-align-left"></i>';
						
					echo'</button>';
					
				echo'</div>';
				
				/*
				echo'<div class="pull-left">';
				
					echo'<a class="menuIconBtn" href="' . $ltple->urls->dashboard . '" style="width: 32px;height: 28px;border-top: 0;border-right: 1px solid #ddd;border-bottom: 0;border-left: 0;color: #777;text-align: left;font-size: 16px;display: block;background: transparent;padding: 3px 5px;margin: 0 10px 0 0;">';
							
						echo'<i class="glyphicon glyphicon-th-large"></i>';
						
					echo'</a>';
					
				echo'</div>';
				*/
				
				echo'<div class="pull-left hidden-xs">';
					
					/*
					echo'<a style="background:' . $ltple->settings->mainColor . ';border:1px solid ' . $ltple->settings->borderColor . ';" class="btn btn-sm" href="'. $ltple->urls->editor .'" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Gallery of Templates" data-content="The gallery is where you can find templates to start a project. New things are added every weeks.">';
					
						echo'Templates';
					
					echo'</a>';
					*/
					
					echo'<a style="color:' . $ltple->settings->mainColor . ';background: #f5f5f5;border: none;" class="btn btn-sm" href="'. $ltple->urls->dashboard .'" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Dashboard" data-content="The dashboard is where you can manage all your projects and services. New things are added every weeks.">';
					
						echo'Dashboard';
					
					echo'</a>';	
					
				echo'</div>';

				echo'<div class="pull-left hidden-xs">';

					echo'<a style="color:' . $ltple->settings->mainColor . ';background:#f5f5f5;border:none;margin-left:6px;" class="btn btn-sm" href="' . $ltple->urls->media . 'user-images/" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Media Library" data-content="The media library allows you to import and manage all your media, a good way to centralize everything.">';
						
						echo'Media';
					
					echo'</a>';
				
				echo'</div>';				
				
				if( $ltple->user->loggedin === true ){
										
					do_action('ltple_left_navbar');
					
					if( $ltple->layer->id > 0 ){
						
						// elements button
					
						$elemLibraries = array();
						
						if( !empty($ltple->layer->defaultElements['name'][0]) ){
							
							$elemLibraries[] = $ltple->layer->defaultElements;
						}			
						
						if( !empty($ltple->layer->layerHtmlLibraries) ){
						
							foreach( $ltple->layer->layerHtmlLibraries as $term ){
								
								$elements = get_option( 'elements_' . $term->slug );

								if( !empty($elements['name'][0]) ){
									
									$elemLibraries[] = $elements;
								}
							} 
						}
					}
				}

			echo'</div>';
			
			echo'<div class="col-xs-6 col-sm-8 text-right" style="padding:0 5px;">';
				
				if( $ltple->user->loggedin === true ){
					
					if(  $ltple->layer->id > 0 && ( isset($_GET['uri']) || is_admin() )){
						
						if( !isset($_GET['action']) || $_GET['action'] != 'edit' ){
							
							// insert button
							
							if( $ltple->layer->layerOutput == 'image' ){

								echo '<button style="margin-left:2px;margin-right:2px;border: none;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveImgEditorElements" data-height="450" data-width="75%" data-resizable="false">Insert</button>';
						
								echo '<div id="LiveImgEditorElements" title="Elements library" style="display:none;">'; 
								echo '<div id="LiveImgEditorElementsPanel">';
									
									echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $ltple->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
									
									echo'<iframe data-src="' . $ltple->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
									
								echo '</div>';
								echo '</div>';										
							}					
							elseif( !empty($elemLibraries) ){
								
								echo'<style>'.PHP_EOL;

									echo'#dragitemslistcontainer {
										
										margin: 0;
										padding: 0;
										/*
										height: 69px;
										overflow: hidden;
										border-bottom: 3px solid #eee;
										background: rgb(201, 217, 231);
										*/
										width: 100%;
										display:inline-block;
									}

									#dragitemslistcontainer li {
										
										float: left;
										position: relative;
										text-align: center;
										list-style: none;
										cursor: move; /* fallback if grab cursor is unsupported */
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
																	
																	$item .= '<img title="'.$name.'" height="150" src="' . $ltple->server->url . '/c/p/live-template-editor-resources/assets/images/flow-charts/corporate/testimonials-slider.jpg" />';
																	
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
						}

						if( is_admin() || ( $ltple->layer->type != 'cb-default-layer' ) ){
							
							if( empty($_GET['action']) || $_GET['action'] != 'edit' ){
								
								if( $ltple->layer->layerOutput == 'canvas' ){
									
									echo '<div style="margin:0 2px;" class="btn-group">';
										
										echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
										
											echo 'Download';
										
										echo '</button>';
										
									echo '</div>';
								}
								elseif( $ltple->layer->layerOutput == 'image' ){

									echo '<div style="margin:0 2px;" class="btn-group">';
										
										echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border:none;background: #4c94af;">';
										
											echo 'Download';
										
										echo '</button>';
										
									echo '</div>';							
								}
							}
							else{
								
								if( $ltple->layer->is_downloadable_output($ltple->layer->layerOutput) ){
									
									echo '<div style="margin:0 2px;" class="btn-group">';
										
										echo '<a href="' . apply_filters('ltple_downloadable_url','#download',$ltple->layer->id,$ltple->layer->layerOutput) . '" class="btn btn-sm" style="border:none;background: #4c94af;">';
										
											echo 'Download';
										
										echo '</a>';
										
									echo '</div>';								
								}							
							}							
							
							if( ( is_admin() || $ltple->user->has_layer ) && !$ltple->layer->is_media ){
								
								// save button
								
								if( !empty($ltple->user->layer->post_title) && ( empty($_GET['action']) || $_GET['action'] != 'edit' ) ){

									$post_title = $ltple->user->layer->post_title;
									
									echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '" id="savePostForm" method="post">';
										
										echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
										echo'<input type="hidden" name="postContent" id="postContent" value="">';
										echo'<input type="hidden" name="postCss" id="postCss" value="">';
										echo'<input type="hidden" name="postJs" id="postJs" value="">';
										echo'<input type="hidden" name="postAction" id="postAction" value="save">';
										echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
										 
										wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
										
										echo'<input type="hidden" name="submitted" id="submitted" value="true">';
										
										echo'<div id="navLoader" style="float:left;margin-right:10px;display:none;"><img src="' . $ltple->assets_url . 'loader.gif" style="height: 20px;"></div>';				
										
										echo'<button style="border:none;" class="btn btn-sm btn-success" type="button" id="saveBtn">Save</button>';
										
									echo'</form>';
									
									if( !$ltple->layer->is_media ){
									
										echo'<a href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&action=edit" style="background-color:#58cac5;border:none;margin-left:2px;" class="btn btn-sm" type="button">Settings</a>';
									}
								}
								
								// view button 
								
								if( $ltple->layer->layerOutput != 'image' && !$ltple->layer->is_downloadable_output($ltple->layer->layerOutput) ){
									
									$preview = add_query_arg(array(
									
										'preview' => '',
									
									), get_post_permalink( $ltple->layer->id ));
									
									echo '<a target="_blank" class="btn btn-sm" href="' . $preview . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
								}
								
								// delete button
								
								if( !empty($_GET['action']) && $_GET['action'] == 'edit' && $ltple->layer->type != 'cb-default-layer' ){

									echo '<a style="border:none;background: #f44336;" class="btn btn-sm" href="#removeCurrentTpl" data-toggle="dialog" data-target="#removeCurrentTpl">Delete</a>';
								
									echo'<div style="display:none;" id="removeCurrentTpl" title="Remove current template">';
										
										echo '<h4>Are you sure you want to delete this template?</h4>';						

										echo '<a style="margin:10px;" class="btn btn-xs btn-success" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&postAction=delete&confirmed">Yes</a>';
										
										//echo '<button style="margin:10px;" type="button" class="btn btn-xs btn-danger ui-button ui-widget" role="button" title="Close"><span class="ui-button-text">No</span></button>';

									echo'</div>';						
								}
							}
						}
				
						if( $ltple->layer->type == 'cb-default-layer' && $ltple->user->is_editor ){
							
							// load button
							
							$post_title = $ltple->layer->title;
							
							echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '" id="savePostForm" method="post">';
								
								echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
								echo'<input type="hidden" name="postContent" id="postContent" value="">';
								echo'<input type="hidden" name="postCss" id="postCss" value="">';
								echo'<input type="hidden" name="postJs" id="postJs" value="">';
								echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
								 
								wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
								
								echo'<input type="hidden" name="submitted" id="submitted" value="true">';
								
								echo'<div id="navLoader" style="margin-right:10px;display:none;"><img src="' . $ltple->assets_url . 'loader.gif" style="height: 20px;"></div>';				

								if( isset($_GET['edit']) ){
									
									echo'<input type="hidden" name="postAction" id="postAction" value="update">';
									
									echo'<button style="border:none;" class="btn btn-sm btn-success" type="button" id="saveBtn">Update</button>';
								}
								else{
									
									echo'<input type="hidden" name="postAction" id="postAction" value="save">';
								}
								
							echo'</form>';
							
							if( !isset($_GET['quick']) ){
							
								// view button
							
								echo '<a target="_blank" class="btn btn-sm" href="' . get_post_permalink( $ltple->layer->id ) . '?preview" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
							}
						}
					}
					
					if( $ltple->user->ID > 0  ){
						
						do_action('ltple_right_navbar');

						if( $ltple->layer->defaultId > 0 ){
							
							if( !$ltple->layer->is_media && ( $ltple->layer->type != 'cb-default-layer' || $ltple->user->is_editor ) ){
								
								echo'<div style="margin:0 2px;" class="btn-group">';
								
									echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 15px;height:28px;background: none;border: none;color: #a5a5a5;box-shadow: none;"><span class="glyphicon glyphicon-cog icon-cog" aria-hidden="true"></span></button>';
														
									echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
										
										echo'<li style="position:relative;">';
										
											echo '<a href="#duplicateLayer" data-toggle="dialog" data-target="#duplicateLayer">Duplicate Template ' . ( $ltple->layer->type == 'cb-default-layer' ? '<span class="label label-warning pull-right">admin</span>' : '' ) . '</a>';

											echo'<div id="duplicateLayer" title="Duplicate Template">';
												
												echo'<form class="" style="width:250px;display:inline-block;" target="_parent" action="' . $ltple->urls->current . '" id="duplicatePostForm" method="post">';
													
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
										
											echo '<a href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&action=edit">Edit Settings</a>';
										
										echo'</li>';

										if( $ltple->user->is_editor ){
											
											echo'<li style="position:relative;">';
												
												echo '<a target="_blank" href="' . get_edit_post_link( $ltple->layer->id ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

											echo'</li>';
											
											if( $ltple->layer->type == 'cb-default-layer' && empty($ltple->user->layer->post_title) ){
											
												echo'<li style="position:relative;">';
													
													echo '<a target="_self" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

												echo'</li>';
											}
											
											if( $ltple->layer->layerOutput != 'image' ){
											
												echo'<li style="position:relative;">';
													
													echo '<a target="_blank" href="' . get_post_permalink( $ltple->layer->id ) . '?preview"> Preview Template <span class="label label-warning pull-right">admin</span></a>';

												echo'</li>';
											}
										}
										
									echo'</ul>';
									
								echo'</div>';
							}
						}
						else{
								
							echo'<div style="margin:0 2px;" class="btn-group">';
							
								echo'<button style="border-radius:5px;background:#42bcf5;font-weight:bold;color:#fff;font-size:11px;padding: 4px 8px;text-align: center;" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Project</button>';
								
								echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
									
									$storage_types = $ltple->layer->get_storage_types();
										
									foreach( $storage_types as $slug => $name ){
										
										if( $slug != 'user-menu' && $slug != 'wp-installer' ){
											
											echo'<li style="position:relative;">';
											
												echo '<a href="' . $ltple->urls->editor . '?layer[default_storage]='.$slug.'">' . $name . '</a>';
										
											echo'</li>';
										}
									}
								
								echo'</ul>';
								
							echo'</div>';
						}
					}
				}
				else{
					

					echo'<a style="margin:0 2px;" class="btn btn-sm btn-success" href="'. wp_login_url( $ltple->request->proto . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn btn-sm btn-info" href="'. wp_login_url( $ltple->urls->editor ) .'&action=register">Register</a>';
										
				}

			echo'</div>';
			
		echo'</div>';
	}
	
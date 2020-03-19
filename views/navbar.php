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
					echo'<a style="background:' . $ltple->settings->mainColor . ';border:1px solid ' . $ltple->settings->borderColor . ';" class="btn btn-sm" href="'. $ltple->urls->gallery .'" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Gallery of Templates" data-content="The gallery is where you can find templates to start a project. New things are added every weeks.">';
					
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
				}

			echo'</div>';
			
			echo'<div class="col-xs-6 col-sm-8 text-right" style="padding:0 5px;">';
				
				if( $ltple->user->loggedin === true ){
					
					if(  $ltple->layer->id > 0 && isset($_GET['uri']) ){
						
						if( $ltple->layer->type != 'cb-default-layer' ){
														
							if( $ltple->user->has_layer && !$ltple->layer->is_media ){
													
								// view button 
								
								if( $ltple->layer->has_preview($ltple->layer->type) ){
									
									echo '<a target="_blank" class="btn btn-sm" href="' . get_preview_post_link($ltple->layer->id) . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
								}
								
								// delete button
								
								if( !empty($_GET['action']) && $_GET['action'] == 'edit' && $ltple->layer->type != 'cb-default-layer' ){

									echo '<a style="border:none;background: #f44336;" class="btn btn-sm" href="#removeCurrentTpl" data-toggle="dialog" data-target="#removeCurrentTpl">Delete</a>';
								
									echo'<div style="display:none;" id="removeCurrentTpl" title="Remove current template">';
										
										echo '<h4>Are you sure you want to delete this ' . $ltple->layer->get_storage_name($ltple->layer->layerStorage) . '?</h4>';						

										echo '<a style="margin:10px;" class="btn btn-xs btn-success" href="' . $ltple->urls->edit . '?uri=' . $ltple->layer->id . '&postAction=delete&confirmed">Yes</a>';
										
										//echo '<button style="margin:10px;" type="button" class="btn btn-xs btn-danger ui-button ui-widget" role="button" title="Close"><span class="ui-button-text">No</span></button>';

									echo'</div>';						
								}
							}
						}
				
						if( $ltple->layer->type == 'cb-default-layer' && $ltple->user->is_editor ){
							
							// load button
							
							$post_title = $ltple->layer->title;
							
							echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->edit . '?uri=' . $ltple->layer->id . '" id="savePostForm" method="post">';
								
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
							
								echo '<a target="_blank" class="btn btn-sm" href="' . get_post_permalink( $ltple->layer->id ) . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
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
										
											echo '<a href="' . $ltple->urls->edit . '?uri=' . $ltple->layer->id . '&action=edit">Edit Settings</a>';
										
										echo'</li>';

										if( $ltple->user->is_editor ){
											
											echo'<li style="position:relative;">';
												
												echo '<a target="_blank" href="' . get_edit_post_link( $ltple->layer->id ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

											echo'</li>';
											
											if( $ltple->layer->type == 'cb-default-layer' && empty($ltple->user->layer->post_title) ){
											
												echo'<li style="position:relative;">';
													
													echo '<a target="_self" href="' . $ltple->urls->edit . '?uri=' . $ltple->layer->id . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

												echo'</li>';
											}
											
											if( $ltple->layer->layerOutput != 'image' ){
											
												echo'<li style="position:relative;">';
													
													echo '<a target="_blank" href="' . get_post_permalink( $ltple->layer->id ) . '"> Preview Template <span class="label label-warning pull-right">admin</span></a>';

												echo'</li>';
											}
										}
										
									echo'</ul>';
									
								echo'</div>';
							}
						}
						elseif( $storage_count = $ltple->layer->count_layers_by('storage') ){
							
							$storage_types = $ltple->layer->get_storage_types();
							
							echo'<div style="margin:0 2px;" class="btn-group">';
								
								echo'<button style="border-radius:5px;background:#42bcf5;font-weight:bold;color:#fff;font-size:11px;padding: 4px 8px;text-align: center;" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Project</button>';
								
								echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
									
									foreach( $storage_types as $slug => $name ){
										
										if( $slug != 'user-menu' && !empty($storage_count[$slug]) ){
											
											echo'<li style="position:relative;">';
											
												echo '<a href="' . $ltple->urls->gallery . '?layer[default_storage]='.$slug.'">' . $name . '</a>';
										
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
					
					echo'<a style="margin:0 2px;" class="btn btn-sm btn-info" href="'. wp_login_url( $ltple->urls->current ) .'&action=register">Register</a>';
										
				}

			echo'</div>';
			
		echo'</div>';
	}
	
	if(!empty($ltple->message)){ 
	
		//output message
	
		echo $ltple->message;
	}
	
	if(!empty($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	
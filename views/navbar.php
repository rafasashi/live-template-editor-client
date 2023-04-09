<?php 
	
	$ltple = LTPLE_Client::instance();

	if( empty($ltple->profile->id) || $ltple->user->loggedin ){
		
		// get navbar
		
		if( !$ltple->inWidget ){

			echo'<div id="navbar_2" class="row" style="box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);background: rgb(236, 236, 236);padding: 8px 0;margin: 0;border-bottom: 1px solid #ddd;position: relative;">';
				
				$user_storage_types = !empty($ltple->user->ID) ? $ltple->layer->get_user_storage_types($ltple->user->ID) : null;
			
				echo'<div class="col-xs-6 col-sm-10" style="z-index:10;padding:0 8px;">';			
					
					echo'<div class="pull-left" style="margin-right:-6px;">';
					
						echo $ltple->get_collapse_button();
						
					echo'</div>';
					
					if( has_nav_menu('ltple_navbar') ) {
						
						wp_nav_menu(array(
						
							'theme_location'  	=> 'ltple_navbar',
							'container'       	=> 'div',
							'container_id'    	=> 'ltple_navbar',
							'container_class' 	=> '',
							'menu_id'         	=> false,
							'menu_class'     	=> '',
							'depth'           	=> 1,
							'fallback_cb'		=> false,
							'walker'			=> new LTPLE_Client_Menu_Navbar()
						));
					}

					if( $ltple->user->loggedin === true ){
											
						do_action('ltple_left_navbar');
					}

				echo'</div>';
				
				echo'<div class="col-xs-6 col-sm-2 text-right" style="padding:0 5px;">';
					
					if( $ltple->user->loggedin === true ){
						
						// get layer
		
						$layer = LTPLE_Editor::instance()->get_layer($ltple->layer->id);
													
						if( !empty($layer) && isset($_GET['uri']) ){
							
							if( $layer->post_type != 'cb-default-layer' ){
															
								if( $ltple->user->has_layer && !$layer->is_media ){

									if( !empty($_GET['action']) && $_GET['action'] == 'edit' ){
										
										echo'<div id="navLoader" style="position:absolute;left:-15px;top:2px;margin-right:10px;display:none;"><img src="' . $ltple->assets_url . 'loader.gif" style="height: 20px;"></div>';				

										// save button
										
										echo '<input style="border:none;" class="btn btn-sm btn-success" type="submit" id="saveBtn" value="Save" />';

										// delete button
										
										echo '<button style="border:none;background:#f44336;margin-left:2px;color:#fff;" class="btn btn-sm" data-toggle="dialog" data-target="#removeCurrentTpl">Delete</button>';
									
										echo'<div style="display:none;text-align:center;" id="removeCurrentTpl" title="Remove current template">';
											
											echo '<div class="alert alert-danger">Are you sure you want to delete this ' . $ltple->layer->get_storage_name($ltple->layer->layerStorage) . '?</div>';						

											echo '<a target="_self" style="margin:10px;" class="btn btn-xs btn-danger" href="' . $ltple->urls->edit . '?uri=' . $layer->ID . '&postAction=delete&confirmed=self">Delete permanently</a>';
											
										echo'</div>';						
									}
									
									// view button 
									
									if( $ltple->layer->has_preview($layer->post_type) ){
										
										echo '<a id="viewBtn" target="_blank" class="btn btn-sm hidden-xs" href="' . $layer->urls['view'] . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
									}
								}
							}
							elseif( $ltple->user->can_edit ){
								
								// load button
								
								$post_title = $layer->post_title;
								
								echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->edit . '?uri=' . $layer->ID . '" id="savePostForm" method="post">';
									
									echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
									echo'<input type="hidden" name="postContent" id="postContent" value="">';
									echo'<input type="hidden" name="postJson" id="postJson" value="">';
									echo'<input type="hidden" name="postCss" id="postCss" value="">';
									echo'<input type="hidden" name="postJs" id="postJs" value="">';
									echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
									 
									wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
									
									echo'<input type="hidden" name="submitted" id="submitted" value="true">';
									
									echo'<div id="navLoader" style="position:absolute;left:-15px;top:2px;margin-right:10px;display:none;"><img src="' . $ltple->assets_url . 'loader.gif" style="height: 20px;"></div>';				

									if( isset($_GET['edit']) ){
										
										echo'<input type="hidden" name="postAction" id="postAction" value="update">';
										
										echo'<input style="border:none;" class="btn btn-sm btn-success" type="submit" id="saveBtn" value="Update" />';
									}
									else{
										
										echo'<input type="hidden" name="postAction" id="postAction" value="save">';
									}
									
								echo'</form>';
								
								if( !isset($_GET['quick']) ){
								
									// view button
								
									echo '<a id="viewBtn" target="_blank" class="btn btn-sm hidden-xs" href="' . $layer->urls['view'] . '" style="margin-left:2px;margin-right:2px;border:none;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
								}
							}
						}
						
						if( $ltple->user->ID > 0  ){
							
							do_action('ltple_right_navbar');

							if( $ltple->layer->defaultId > 0 ){
								
								if( !$layer->is_media && ( $layer->post_type != 'cb-default-layer' || $ltple->user->can_edit ) ){
									
									echo'<div style="margin:0 2px;" class="btn-group">';
									
										echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size:15px;height:28px;width:28px;padding:5px;background:none;border:none;color:#a5a5a5;box-shadow: none;"><span class="glyphicon glyphicon-cog icon-cog" aria-hidden="true"></span></button>';
															
										echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;margin-top:9px;">';
											
											if( $layer->output != 'image' && $ltple->layer->has_preview($layer->post_type) ){
											
												echo'<li>';
													
													echo '<a target="_blank" href="' . get_preview_post_link( $layer->ID ) . '"> Preview Template</a>';

												echo'</li>';
											}
												
											echo'<li>';
											
												echo '<a href="#" data-toggle="dialog" data-target="#duplicateLayer">Duplicate Template ' . ( $layer->post_type == 'cb-default-layer' ? '<span class="label label-warning pull-right">admin</span>' : '' ) . '</a>';

												echo'<div id="duplicateLayer" title="Duplicate Template">';
													
													echo'<form class="" style="width:250px;display:inline-block;" target="_parent" action="' . $ltple->urls->current . '" id="duplicatePostForm" method="post">';
														
														echo'<input type="text" name="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" style="margin:7px 0;" required>';
														echo'<input type="hidden" name="postAction" id="postAction" value="duplicate">';
														echo'<input type="hidden" name="postContent" value="">';
														echo'<input type="hidden" name="postJson" value="">';
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

											if( $ltple->user->can_edit ){
												
												echo'<li>';
													
													echo '<a target="_blank" href="' . get_edit_post_link( $layer->ID ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

												echo'</li>';
												
												if( $layer->post_type == 'cb-default-layer' && empty($ltple->user->layer->post_title) ){
												
													echo'<li>';
														
														echo '<a target="_self" href="' . $ltple->urls->edit . '?uri=' . $layer->ID . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

													echo'</li>';
												}
											}
											
										echo'</ul>';
										
									echo'</div>';
								}
							}
							elseif( !empty($user_storage_types) ){
								
								echo'<div style="margin:0 2px;" class="btn-group">';
									
									echo'<button style="background:#42bcf5;font-weight:bold;color:#fff;font-size:11px;padding: 4px 8px;text-align: center;" type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Project</button>';
									
									echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;margin-top:11px;text-transform:uppercase;">';
										
										foreach( $user_storage_types as $slug => $name ){
											
											echo'<li style="position:relative;">';
											
												echo '<a href="' . $ltple->urls->gallery . '?layer[default_storage]='.$slug.'">' . $name . '</a>';
										
											echo'</li>';
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
		elseif( $ltple->modalId ){
				
			echo '<button style="position:absolute;z-index:999999;right:5px;top:10px;" type="button" class="close close_widget"><span aria-hidden="true">×</span></button>';
		}
	}
	
	echo $ltple->output_message();
	
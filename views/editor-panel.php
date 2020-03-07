<?php 

$ltple = LTPLE_Client::instance();

get_header();

include( $this->parent->views . '/navbar.php' );

// get current tab

$currentTab = $ltple->user->layer->post_type;

$post_type = get_post_type_object( $ltple->user->layer->post_type );

$layer_type = $ltple->layer->get_layer_type($ltple->layer->id);

// ------------- output panel --------------------

echo'<div id="panel" class="wrapper">';

	echo $ltple->dashboard->get_sidebar($currentTab);
	
	echo'<div id="content" class="library-content" style="border-left:1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:calc( 100vh - ' . ( $ltple->inWidget ?  0 : 190 ) . 'px);">';
		
		echo'<div class="tab-content">';
		
			if(!empty($ltple->parent->message)){ 
			
				//output message
			
				echo $ltple->message;
			}
			
			if( !empty($ltple->user->layer) ){
				
				echo '<h2 style="margin-top:5px;">Edit ' . $post_type->labels->singular_name . '</h2>';
						
				$fields = $ltple->layer->get_user_layer_fields($ltple->user->layer);
						
				echo '<form method="post" enctype="multipart/form-data">';
					
					echo'<div class="row gutter-20">';
						
						echo'<div class="col-md-9">';
							
							echo'<input type="hidden" name="postAction" value="edit" />';
							
							// ID
							
							echo'<input type="hidden" name="id" value="' . ( !empty($ltple->user->layer->ID) ? $ltple->user->layer->ID : 0 ) . '" />';

							// title
							
							echo'<div class="panel panel-default">';

								echo'<div class="panel-heading">';
									
									echo $post_type->labels->singular_name . ' Title';
									
								echo'</div>';
								
								echo'<div class="panel-body">';
								
									echo'<input type="text" placeholder="Title" name="post_title" value="' . ( !empty($ltple->user->layer->post_title) ? $ltple->user->layer->post_title : '' ) . '" style="width:100%;padding:10px;font-size:20px;border-radius:2px;" required="required"/>';
									
									do_action('ltple_edit_layer_title',$ltple->user->layer);								
							
								echo'</div>';
							
							echo'</div>';						
							
						echo'</div>';

						echo'<div class="col-md-3">';

							// status panel
					
							echo'<div class="panel panel-default">';
								
								do_action('ltple_edit_layer_status',$ltple->user->layer,$post_type);
								
								echo'<div class="panel-footer">';
								
									echo'<div class="row" style="padding: 0 10px;">';
								
										echo '<input type="submit" value="Update" class="btn btn-md btn-success pull-right" style="font-size:12px;" />';
									
									echo'</div>'; 
									
								echo'</div>';

							echo'</div>';
						
						echo'</div>';
						
					echo'</div>';
					
					echo'<div class="row gutter-20">';
						
						echo'<div class="col-md-3 col-md-push-9">';
							
							if( $ltple->layer->is_public_output($ltple->layer->layerOutput) ){
								
								// image preview
								
								$media_url = add_query_arg( array(
								
									'output' => 'widget',
									
								), $ltple->urls->media . 'user-images/' );
								
								$md5 = md5($media_url);
								
								$modal_id 	= 'modal_' . $md5;
								$preview_id = 'preview_' . $md5;
								$input_id 	= 'input_' . $md5;
								
								echo'<div class="panel panel-default">';
								
									echo '<div id="'.$preview_id.'" class="thumb_wrapper" style="background-image:url(' . $ltple->layer->get_thumbnail_url($ltple->user->layer) . ');background-size:cover;background-repeat:no-repeat;background-position:center center;width:100%;display:block;"></div>';
									
									echo '<input type="hidden" id="'.$input_id.'" name="image_url" value="" />';
									
									echo'<div class="panel-footer">';
									
										echo'<div class="row" style="padding: 0 10px;">';
											
											echo '<button type="button" class="pull-right btn btn-xs btn-info" data-toggle="modal" data-target="#'.$modal_id.'">Edit</button>';
										
											echo '<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
												
												echo '<div class="modal-dialog modal-lg" role="document" style="margin:0;width:100% !important;position:absolute;">'.PHP_EOL;
													
													echo '<div class="modal-content">'.PHP_EOL;
														
														echo '<div class="modal-header">'.PHP_EOL;
															
															echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
															
															echo '<h4 class="modal-title text-left" id="myModalLabel">Media Library</h4>'.PHP_EOL;
														
														echo '</div>'.PHP_EOL;

														echo '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $ltple->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

														echo '<iframe id="iframe_'.$modal_id.'" data-src="' . $media_url . '" data-input-id="#' . $input_id . '" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';						
														
														echo '<script>';

															echo ';(function($){';

																echo '$(document).ready(function(){
																	
																	$("#'.$input_id.'").on("change", function(e){
																		
																		$("#'.$preview_id.'").css("background-image","url(" + $(this).val() + ")");
																	});
																
																});';
															
															echo '})(jQuery);';
															
														echo '</script>';
														
													echo '</div>'.PHP_EOL;
													
												echo '</div>'.PHP_EOL;
												
											echo '</div>'.PHP_EOL;									
										
										echo'</div>';
										
									echo'</div>';
								
								echo'</div>';
							}
							
							// side metaboxes
							
							$ltple->admin->display_frontend_metaboxes($fields,$ltple->user->layer,'side');
							
						echo'</div>';
						
						echo'<div class="col-md-9 col-md-pull-3">';
							
							if( $tabs = $ltple->layer->get_project_tabs($ltple->user->layer,$fields) ){
								
								echo'<ul class="nav nav-tabs" role="tablist" style="background:transparent;margin:-1px;padding:0px !important;overflow:visible !important;height:50px;font-size:15px;font-weight:bold;">';
									
									$class=' class="active"';
									
									foreach( $tabs as $tab ){
										
										echo'<li role="presentation"'.$class.'><a href="#'.$tab['slug'].'" aria-controls="'.$tab['slug'].'" role="tab" data-toggle="tab" aria-expanded="true">'.$tab['name'].'</a></li>';
									
										$class = '';
									}
									
								echo'</ul>';

								echo'<div class="panel panel-default">';								
								
									echo'<div class="panel-body tab-content" style="min-height:380px;">';
										
										$class = ' class="tab-pane active"';
										
										foreach( $tabs as $tab ){
											
											echo '<div role="tabpanel"'.$class.' id="'.$tab['slug'].'">';
											
												echo $tab['content'];
												
											echo '</div>';
											
											$class = ' class="tab-pane"';
										}
										
									echo'</div>';
									
								echo'</div>';
							}
							
							// advanced metaboxes
							
							//$ltple->admin->display_frontend_metaboxes($fields,$ltple->user->layer,'advanced');
							
						echo'</div>';
						
					echo'</div>';
					
				echo'</form>';
			}

		echo'</div>';
		
	echo'</div>	';

echo'</div>';

get_footer();
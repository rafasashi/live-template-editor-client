<?php 
	
	// get current tab
	
	$currentTab = $this->user->layer->post_type;
	
	$post_type = get_post_type_object( $this->user->layer->post_type );

	// ------------- output panel --------------------
	
	echo'<div id="panel" class="wrapper">';

		echo $this->dashboard->get_sidebar($currentTab);
		
		echo'<div id="content" class="library-content" style="border-left:1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			echo'<div class="tab-content">';
			
				if(!empty($this->parent->message)){ 
				
					//output message
				
					echo $this->message;
				}
				
				if( !empty($this->user->layer) ){
					
					echo '<h2>Edit ' . $post_type->labels->singular_name . '</h2>';
							
					$fields = $this->layer->get_user_layer_fields($this->user->layer);
							
					echo '<form method="post" enctype="multipart/form-data">';
						
						echo'<div class="row">';
							
							echo'<div class="col-md-9">';
								
								echo'<input type="hidden" name="postAction" value="edit" />';
								
								// ID
								
								echo'<input type="hidden" name="id" value="' . ( !empty($this->user->layer->ID) ? $this->user->layer->ID : 0 ) . '" />';

								// title
								
								echo'<div class="panel panel-default">';

									echo'<div class="panel-heading">';
										
										echo $post_type->labels->singular_name . ' Title';
										
									echo'</div>';
									
									echo'<div class="panel-body">';
									
										echo'<input type="text" placeholder="Title" name="post_title" value="' . ( !empty($this->user->layer->post_title) ? $this->user->layer->post_title : '' ) . '" style="width:100%;padding:10px;font-size:20px;border-radius:2px;" required="required"/>';
										
										do_action('ltple_edit_layer_title');								
								
									echo'</div>';
								
								echo'</div>';						
								
							echo'</div>';

							echo'<div class="col-md-3">';

								// status panel
						
								echo'<div class="panel panel-default">';
									
									if( $this->layer->is_hosted($this->user->layer) ){
										
										echo'<div class="panel-heading">';
											
											echo $post_type->labels->singular_name . ' Status';
																				
										echo'</div>';
											
										echo'<div class="panel-body">';					
																						
											$status = array(
												
												'publish' 	=> 'Public',
												'draft' 	=> 'Draft',
											);
											
											$this->admin->display_field( array(
											
												'type'				=> 'select',
												'id'				=> 'post_status',
												'name'				=> 'post_status',
												'options' 			=> $status,
												'description'		=> '',
												
											),$this->user->layer );	
											
										echo'</div>';
									}
									
									echo'<div class="panel-footer">';
									
										echo'<div class="row" style="padding: 0 10px;">';
									
											echo '<input type="submit" value="Update" class="btn btn-md btn-primary pull-right" style="font-size:12px;" />';
										
										echo'</div>';
										
									echo'</div>';
									
								
								echo'</div>';
							
							echo'</div>';
							
						echo'</div>';
						
						echo'<div class="row">';
							
							echo'<div class="col-md-3 col-md-push-9">';
							
								// image preview
								
								$media_url = add_query_arg( array(
								
									'output' => 'widget',
									
								), $this->urls->media . 'user-images/' );
								
								$md5 = md5($media_url);
								
								$modal_id 	= 'modal_' . $md5;
								$preview_id = 'preview_' . $md5;
								$input_id 	= 'input_' . $md5;
								
								echo'<div class="panel panel-default">';
								
									echo '<div id="'.$preview_id.'" class="thumb_wrapper" style="background-image:url(' . $this->layer->get_thumbnail_url($this->user->layer) . ');background-size:cover;background-repeat:no-repeat;background-position:top center;width:100%;display:block;"></div>';
									
									echo '<input type="hidden" id="'.$input_id.'" name="image_url" value="" />';
									
									echo'<div class="panel-footer">';
									
										echo'<div class="row" style="padding: 0 10px;">';
											
											echo '<button type="button" class="pull-right btn btn-xs btn-info" data-toggle="modal" data-target="#'.$modal_id.'">Edit</button>';
										
											echo '<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
												
												echo '<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
													
													echo '<div class="modal-content">'.PHP_EOL;
														
														echo '<div class="modal-header">'.PHP_EOL;
															
															echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
															
															echo '<h4 class="modal-title text-left" id="myModalLabel">Media Library</h4>'.PHP_EOL;
														
														echo '</div>'.PHP_EOL;

														echo '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

														echo '<iframe id="iframe_'.$modal_id.'" data-src="' . $media_url . '" data-input-id="#' . $input_id . '" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:500px;"></iframe>';						
														
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
								
								// side metaboxes
								
								$this->admin->display_frontend_metaboxes($fields,$this->user->layer,'side');
								
							echo'</div>';
							
							echo'<div class="col-md-9 col-md-pull-3">';
								
								// edit content
								
								echo'<div class="panel panel-default">';
																		
									echo'<div class="panel-body" style="text-align:center;">';
										
										/*
										echo'<div style="background:#fbfbfb;margin-bottom:15px;padding:20px;">';
										
											echo'Menu';
										
										echo'</div>';
										*/

										echo'<div style="background:#fbfbfb;padding:140px 0;">';
										
											echo'<a class="btn btn-lg btn-primary" href="' . $this->urls->editor . '?uri=' . $this->user->layer->ID . '">Edit Content</a>';
							
										echo'</div>';
											
									echo'</div>';							

								echo'</div>';	
								
								// advanced metaboxes
								
								$this->admin->display_frontend_metaboxes($fields,$this->user->layer,'advanced');
								
							echo'</div>';
							
						echo'</div>';
						
					echo'</form>';
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

			
				
			});
			
		})(jQuery);

	</script>
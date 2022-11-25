<?php 

$ltple = LTPLE_Client::instance();

get_header();

	include( $this->parent->views . '/navbar.php' );

	// get current tab

	$currentTab = !empty($ltple->user->layer->post_type) ? $ltple->user->layer->post_type : '';

	$post_type = get_post_type_object( $currentTab );

	$layer_type = $ltple->layer->get_layer_type($ltple->layer->id);

	// ------------- output panel --------------------

	echo'<div id="panel" class="wrapper">';

		echo '<div id="sidebar">';
				
			echo '<div class="gallery_type_title gallery_head">Dashboard</div>';

			echo '<ul class="nav nav-tabs tabs-left">';
				
				echo apply_filters('ltple_list_sidebar','',$currentTab);
				
			echo '</ul>';
			
		echo '</div>';
		
		echo'<div id="content" class="library-content" style="border-left:1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:calc( 100vh - ' . ( $ltple->inWidget ?  0 : 190 ) . 'px);">';
			
			echo'<div id="editor-panel" class="col-xs-12">';
			
				if(!empty($ltple->parent->message)){ 
				
					//output message
				
					echo $ltple->message;
				}
				
				if( !empty($ltple->user->layer) ){
					
					echo '<h2 style="margin-top:15px;">Edit ' . ( !empty($post_type->labels->singular_name) ? $post_type->labels->singular_name : 'project' ) . '</h2>';
						
					$fields = $ltple->layer->get_user_layer_fields(array(),$ltple->user->layer);
							
					echo '<form id="savePostForm" method="post" enctype="multipart/form-data">';
						
						echo'<div class="row gutter-20">';
							
							echo'<div class="col-md-9">';
								
								// action
								
								echo'<input type="hidden" name="postAction" value="edit" />';
								
								// ID
								
								echo'<input type="hidden" name="id" value="' . ( !empty($ltple->user->layer->ID) ? $ltple->user->layer->ID : 0 ) . '" />';
								
								// title
								
								$post_title = !empty($ltple->user->layer->post_title) ? $ltple->user->layer->post_title : '' ;
								
								if( post_type_supports($post_type->name,'title') ){
									
									echo'<div class="panel panel-default">';

										echo'<div class="panel-heading">';
											
											if( !empty($post_type->labels->singular_name) ){
												
												echo $post_type->labels->singular_name . ' ';
											}
											
											echo 'Title';
											
										echo'</div>';
										
										echo'<div class="panel-body">';
											
											echo'<input type="text" placeholder="Title" name="post_title" value="' . $post_title . '" style="width:100%;padding:10px;font-size:20px;border-radius:2px;" required="required"/>';
											
											do_action('ltple_edit_layer_title',$ltple->user->layer);								
										
										echo'</div>';
									
									echo'</div>';
								}
								else{
									
									echo'<input type="hidden" name="post_title" value="' . $post_title . '" />';
								}
								
							echo'</div>';

							echo'<div class="col-md-3">';

								// status panel
						
								echo'<div class="panel panel-default">';
									
									do_action('ltple_edit_layer_status',$ltple->user->layer,$post_type);

								echo'</div>';
							
							echo'</div>';
							
						echo'</div>';
						
						echo'<div class="row gutter-20">';
							
							if( $tabs = $ltple->layer->get_project_tabs($ltple->user->layer,$fields) ){
									
								echo'<div class="col-md-9">';

									echo'<ul class="nav nav-tabs" role="tablist" style="background:transparent;margin:-1px;padding:0px !important;overflow:visible !important;height:47px;font-size:13px;font-weight:bold;">';
										
										$class=' class="active"';
										
										foreach( $tabs as $tab ){
											
											echo'<li role="presentation"'.$class.'><a href="#'.$tab['slug'].'" aria-controls="'.$tab['slug'].'" role="tab" data-toggle="tab" aria-expanded="true">'.$tab['name'].'</a></li>';
										
											$class = '';
										}
										
									echo'</ul>';
									
								echo'</div>';
							
								echo'<div class="col-xs-12">';
	
									echo'<div class="editor-tab-content tab-content">';
										
										$active = ' active';
										
										foreach( $tabs as $i => $tab ){
											
											echo '<div role="tabpanel" class="editor-tab-pane tab-pane'.$active.'" id="'.$tab['slug'].'">';
												
												echo'<div class="col-md-9 panel panel-default">';
													
													echo'<div class="editor-tab-panel-body">';
													
														echo $tab['content'];
														
													echo'</div>';
													
												echo'</div>';
												
												echo'<div class="col-md-3">';
													
													if( !empty($tab['tips']) ){
														
														echo $tab['tips'];
													}
													
												echo'</div>';
												
											echo '</div>';
											
											$active = '';
										}
										
									echo'</div>';
								
								echo'</div>';
							}
							
						echo'</div>';
						
					echo'</form>';
				}
			
			echo'</div>';
			
		echo'</div>	';

	echo'</div>';

get_footer();
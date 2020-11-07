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
					
				$fields = $ltple->layer->get_user_layer_fields(array(),$ltple->user->layer);
						
				echo '<form id="savePostForm" method="post" enctype="multipart/form-data">';
					
					echo'<div class="row gutter-20">';
						
						echo'<div class="col-md-9">';
							
							// action
							
							echo'<input type="hidden" name="postAction" value="edit" />';
							
							// ID
							
							echo'<input type="hidden" name="id" value="' . ( !empty($ltple->user->layer->ID) ? $ltple->user->layer->ID : 0 ) . '" />';
							
							// json
							
							
							
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

							echo'</div>';
						
						echo'</div>';
						
					echo'</div>';
					
					echo'<div class="row gutter-20">';
						
						echo'<div class="col-md-3 col-md-push-9">';
							
							// side metaboxes
							
							//$ltple->admin->display_frontend_metaboxes($fields,$ltple->user->layer,'side');
							
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
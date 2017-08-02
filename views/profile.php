<?php

if( $displayedUser = get_user_by( 'ID', intval($_GET['pr'])) ){

	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">About me</li>';
				
				echo'<li class="active"><a href="#general-info" data-toggle="tab">General Info</a></li>';
				
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';

				//---------------------- output General Info --------------------------
				
				echo'<div class="tab-pane active" id="general-info">';
				
					echo'<form action="" method="post" class="tab-content row" style="margin:20px;">';

						echo'<div class="col-xs-12 col-sm-6">';
					
							// get profile picture
							
							$picture = get_user_meta( $displayedUser->ID , $this->_base . 'profile_picture', true );
							
							if( empty($picture) ){
								
								$picture = get_avatar_url( $displayedUser->ID );
							}
							
							// get profile title
							
							$title = get_user_meta( $displayedUser->ID , $this->_base . 'profile_title', true );
							
							if( empty($title) ){
								
								$title = 'General Information';
							}
							
							echo'<h3>'.'<img src="'.$picture.'" height="75" width="75" /> '.$title.'</h3>';
							
						echo'</div>';

						echo'<div class="col-xs-12 col-sm-4"></div>';
						
						echo'<div class="clearfix"></div>';
					
						echo'<div class="col-xs-12 col-sm-8">';

							echo'<table class="form-table">';
							
								foreach( $this->profile->fields as $field ){
									
									echo'<tr>';
									
										echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
										
										echo'<td>';
										
											if( isset($displayedUser->{$field['id']}) ){
												
												$meta = $displayedUser->{$field['id']};
											}
											else{
												
												$meta = get_user_meta( $displayedUser->ID , $field['id'] );
											}
											
											if(!empty($meta)){
											
												if(	$field['id'] == 'user_url'){
														
													echo '<a target="_blank" href="'.$meta.'">'.$meta.' <span style="font-size:11px;" class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>';
												}
												else{
													
													echo '<p>';
													
														echo str_replace(PHP_EOL,'</p><p>',strip_tags($meta));
														
													echo '</p>';
												}
											}
											else{
												
												echo '';
											}
										
										echo'</td>';
										
									echo'</tr>';
								}
								
							echo'</table>';
							
						echo'</div>';

					echo'</form>';
					
				echo'</div>';

			echo '</div>';
			
		echo '</div>';	

	echo '</div>';
}
else{
	
	echo '<div class="alert alert-warning">';
	
		echo 'This profile doesn\'t exits...';
		
	echo '</div>';
}
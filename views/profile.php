<?php

if( $displayedUser = get_user_by( 'ID', intval($_GET['pr'])) ){

	echo'<div id="media_library" style="margin-top:15px;background:#FFF;display:inline-block;width:100%;">';

		echo'<div class="col-xs-3 col-sm-2">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">About '.ucfirst($displayedUser->user_nicename).'</li>';
				
				echo'<li class="active"><a href="#general-info" data-toggle="tab">General Info</a></li>';
				
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">';
			
			echo'<div class="tab-content">';

				//---------------------- output General Info --------------------------
				
				echo'<div class="tab-pane active" id="general-info">';
				
					echo'<form action="" method="post" class="tab-content row" style="margin:20px;">';

						echo'<div class="col-xs-12 col-sm-6">';
					
							echo'<h3>General Information</h3>';
							
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
												
												echo $displayedUser->{$field['id']};
											}
											elseif( $meta = get_user_meta( $displayedUser->ID , $field['id'] )){
										
												echo $meta;
											}
											else{
												
												echo 'none';
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
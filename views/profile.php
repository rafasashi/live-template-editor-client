<?php

if( $displayedUser = get_user_by( 'ID', intval($_GET['pr'])) ){

	$background_color = '#fff';

	echo'<style>';
	
	echo'
	
		@import url("https://fonts.googleapis.com/css?family=Pacifico");
	
		.profile-overlay {
			
			width: 100%;
			height: 350px;
			position: absolute;
			background-image: linear-gradient(to bottom right,#182f42,' . $this->settings->mainColor . ');
			opacity: .6;
		}
	
		.profile-heading {
			
			height: 350px;
			padding-top: 130px;
			background-color: #333;
			background-image: url(http://www.citi.io/wp-content/uploads/2015/08/1168-00-06.jpg);
			background-position: center center;
			background-size: cover;
			background-attachment: fixed;
			background-repeat: no-repeat;
		}
		
		.profile-heading h2 {
			
			color: #fff;
			font-weight: normal;
			font-size: 53px;
			font-family: "Pacifico", cursive;
			position: relative;
			text-shadow: 0px 0px 8px rgba(0, 0, 0, .4);
		}
			
		.profile-avatar img {

			border: solid 10px '.$background_color.';
			border-radius: 100px;
			margin-top: 70px;
			position: relative;
		}
				
		.profile-menu {
			
			padding: 90px 15px 0 15px;
		}

		.profile-menu ul {
			
			font-size: 16px;
		}			
    
		.profile-content {
			
			display: inline-block;
			padding-top: 50px;
			background-color: '.$background_color.';
			width: 100%;
			min-height: 500px;
		}		
		
	';
	
	echo'</style>';

	echo'<div id="profile_page" class="row">';

		echo'<div class="col-xs-12">';
			
			echo'<div class="profile-overlay"></div>';

			echo'<div class="profile-heading text-center">';
			
				// get name
				
				$name = get_user_meta( $displayedUser->ID , 'nickname', true );
				
				echo '<h2>' . $name . '</h2>';			
			
				// get profile picture
				
				$picture = get_user_meta( $displayedUser->ID , $this->_base . 'profile_picture', true );
				
				if( empty($picture) ){
					
					$picture = get_avatar_url( $displayedUser->ID );
				}
				
				echo '<div class="profile-avatar">';
				
					echo'<img src="'.$picture.'" height="150" width="150" />';
					
				echo '</div>';
								
			echo'</div>'; //profile-overlay
			
			echo'<div class="profile-menu">';
			
				echo'<div class="col-xs-12 col-sm-1"></div>';
			
				echo'<div class="col-xs-12 col-sm-10">';
				
					$tabs = [];
					$tabs['about-me']['name'] = 'About Me';
					$tabs['performer']['name'] = 'Performer';
					
					echo'<ul class="nav nav-tabs" role="tablist" style="overflow: visible;">';
						
						$active = ' active';
						
						foreach( $tabs as $slug => $tab){
							
							echo'<li role="presentation" class="'.$active.'">';
							
								echo'<a href="#' . $slug . '" aria-controls="' . $slug . '" role="tab" data-toggle="tab">'.$tab['name'].'</a>';
							
							echo'</li>';
							
							$active = '';
						}
						
					echo'</ul>';
					
				echo'</div>';
				
				echo'<div class="col-xs-12 col-sm-1"></div>';
			
			echo'</div>'; //profile-menu
			
			echo'<div class="profile-content">';
			
				echo'<div class="col-xs-12 col-sm-1"></div>';
			
				echo'<div class="col-xs-12 col-sm-10">';		
				
					echo'<div class="tab-content">';

						$active = ' active';
					
						foreach( $tabs as $slug => $tab ){
								
							echo'<div class="tab-pane' . $active . '" id="' . $slug . '">';
							
								echo'<div class="col-xs-12 col-sm-8">';

									if( $slug == 'about-me' ){
								
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
									}
									
								echo'</div>';
								
							echo'</div>';
							
							$active = '';
						}
						
					echo'</div>';
					
				echo'</div>';
				
				echo'<div class="col-xs-12 col-sm-1"></div>';

			echo '</div>';
			
		echo '</div>';	

	echo '</div>';
}
else{
	
	echo '<div class="alert alert-warning">';
	
		echo 'This profile doesn\'t exits...';
		
	echo '</div>';
}
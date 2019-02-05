<?php
	
	$is_public = $this->is_public();
	
	$self_profile = ( $this->parent->user->loggedin && $this->user->ID  == $this->parent->user->ID ? true : false );
	
	if( $is_public || $self_profile ){
	
		// get background
	
		$background_image = $this->parent->image->get_banner_url($this->user->ID) . '?' . time();
		
		// is profile editable
		
		$editable = ( $this->parent->user->loggedin && $this->parent->user->ID == $this->user->ID ? true : false );
		
		// get name
		
		$name = get_user_meta( $this->user->ID , 'nickname', true );
		
		// get profile picture
		
		$picture = $this->parent->image->get_avatar_url( $this->user->ID );
		
		// get tabs
		
		$tabs = $this->get_profile_tabs($this->user->ID);
		
		// get apps
		
		$apps = $this->parent->apps->getUserApps($this->user->ID);
		
		// profile style
		
		echo'<style>';
		
		echo'
		
			@import url("https://fonts.googleapis.com/css?family=Pacifico");
		
			.profile-overlay {
				
				width: 100%;
				height: 350px;
				position: absolute;
				background-image: linear-gradient(to bottom right,#284d6b,' . $this->parent->settings->mainColor . ');
				opacity: .5;
			}
		
			.profile-heading {
				
				height: 350px;
				padding-top: 130px;
				background-color: #333;
				background-image: url("' . $background_image . '");
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

				border: solid 20px #f9f9f9;
				border-radius: 100px;
				margin:0px;
				position: relative;
				background:#fff;
			}
					
			.profile-menu {
				
				padding: 90px 15px 0 15px;
			}

			.profile-menu ul {
				
				font-size: 16px;
			}			
		
			.profile-content {
				
				display: inline-block;
				padding-top: 30px;
				padding-bottom: 100px;
				background-color: #fff;
				width: 100%;
				min-height: 500px;
			}		
			
		';
		
		echo'</style>';
		
		echo'<div id="profile_page">';

			echo'<div class="profile-overlay"></div>';

			echo'<div class="profile-heading text-center" style="'.( $editable ? 'padding-top:80px;' : 'padding-top:125px;').'">';
				
				echo '<h2>' . $name . '</h2>';
				
				// mobile avatar
				
				echo '<div class="profile-avatar text-center hidden-sm hidden-md hidden-lg" style="margin:10px;">';
				
					echo'<img style="border:solid 5px #f9f9f9;" src="' . $picture . '" height="100" width="100" />';
					
				echo '</div>';			
				
				if( $editable ){
					
					echo '<a class="hidden-xs" title="Edit profile" href="' . $this->parent->urls->profile . '">';
					
						echo '<span class="fa fa-pencil" style="
							color: #fff;
							font-size: 28px;
							position: relative;
							border: 4px solid #fff;
							border-radius: 250px;
							height: 45px;
							width: 45px;
							text-align: center;
							padding: 5px;
							box-shadow: 0px 0px 8px rgba(0, 0, 0, .4);
						"></span>';
						
					echo '</a>';
				}
								
			echo'</div>'; //profile-overlay
			
			echo'<div id="panel">';
			
				echo'<div class="col-xs-3 col-sm-2 text-center hidden-xs">';
						
					// user avatar	
						
					echo '<div class="profile-avatar text-center" style="margin: -60px 10px 10px 10px;">';
					
						echo'<img src="' . $picture . '" height="150" width="150" />';
						
					echo '</div>';
					
					// user stars
					
					echo '<span class="badge" style="background-color:#fff;color:' . $this->parent->settings->mainColor . ';font-size:18px;border-radius: 25px;padding: 8px 18px;box-shadow: inset 0px 0px 1px #666;">';
						
						echo '<span class="glyphicon glyphicon-star" aria-hidden="true"></span> ';
						
						echo $this->parent->stars->get_count($this->user->ID);
				
					echo '</span>';
					
					// social icons
				
					if( !empty($apps) ){
						
						echo '<div id="social-icons" class="text-center" style="margin:20px 0;">';
						
							foreach( $apps as $app ){
								
								if( !empty($app->user_profile) && !empty($app->social_icon) ){
									
									$show_profile = get_user_meta($this->user->ID,$this->parent->_base . 'app_profile_' . $app->ID,true);
									
									if( $show_profile != 'off' ){
										
										echo'<a href="' . $app->user_profile . '" style="margin:5px;display:inline-block;" ref="nofollow" target="_blank">';
											
											echo'<img src="' . $app->social_icon . '" style="height:30px;width:30px;border-radius:250px;" />';
											
										echo'</a>';
									}
								}
							}
						
						echo '</div>';
					}
				
				echo '</div>';
				
				echo'<div class="col-xs-12 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
				
					echo'<ul class="nav nav-pills" role="tablist" style="overflow:visible;margin-top:0;">';
						
						foreach( $tabs as $tab){
							
							if( !empty($tab['name']) ){

								$active = ( $tab['slug'] == $this->tab ? ' active' : '');

								$url = $this->parent->urls->profile . $this->user->ID . '/';

								if( $tab['slug'] != 'about-me' ){
									
									$url .= $tab['slug'] . '/';
								}
								
								echo'<li role="presentation" class="'.$active.'">';
								
									echo'<a href="' . $url . '" role="tab">'.$tab['name'].'</a>';
								
								echo'</li>';
							}
						}
						
					echo'</ul>';
					
					if( !$is_public && $self_profile ){
						
						echo '<div class="alert alert-warning row" style="margin: 20px 0 !important;">';
							
							echo'<div class="col-xs-9">';
							
								echo 'Your profile is restrected, only you can see this page.';
							
							echo '</div>';
							
							echo'<div class="col-xs-3 text-right">';
							
								echo '<a class="btn btn-sm btn-success" href="' . $this->parent->urls->profile . '?tab=privacy-policy">Edit</a>';
							
							echo '</div>';
							
						echo '</div>';			
					}
					
					echo'<div class="profile-content">';
					
						echo'<div class="col-xs-12 col-sm-10">';		
						
							echo'<div class="tab-content">';
								
								foreach( $tabs as $tab){
									
									if( !empty($tab['content']) && $tab['slug'] == $this->tab  ){

										echo'<div class="tab-pane active" id="'.$tab['slug'].'">';
										
											echo'<div class="col-xs-12 col-sm-8">';
											
												if(!empty($this->parent->message)){ 
												
													//output message
												
													echo $this->parent->message;
												}									
											
												echo $tab['content'];
												
											echo'</div>';
											
										echo'</div>';
										
										break;
									}							
								}					
								
							echo'</div>';
							
						echo'</div>';
						
					echo '</div>';
					
				echo '</div>';
				
			echo '</div>';

		echo '</div>';
	}
	else{ 
		
		echo '<div class="alert alert-warning" style="padding-top:50px;">';
		
			echo 'This profile is not accessible...';
			
		echo '</div>';
	}

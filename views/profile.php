<?php
	
	if( $this->is_public || $this->self_profile ){
		
		// get name
		
		$name = get_user_meta( $this->user->ID , 'nickname', true );
		
		// profile page
		
		echo'<div id="profile_page" style="display:block;">';

			if( $this->tab == 'about-me' ){
				
				// full header
				
				echo'<div class="profile-heading text-center">';
					
					echo'<div class="profile-overlay"></div>';
					
					echo '<h1>' . $name . '</h1>';
					
					// mobile avatar
					
					echo '<div class="profile-avatar text-center hidden-sm hidden-md hidden-lg" style="margin:10px;position:relative;">';
					
						echo'<img style="border:solid 5px #f9f9f9;" src="' . $this->picture . '" height="100" width="100" />';
						
						if( $this->is_pro ){
							
							echo'<span class="label label-primary" style="position:absolute;bottom:24%;margin-left:-30px;background:' . $this->parent->settings->mainColor . ';font-size:14px;">pro</span>';									
						}
						
					echo '</div>';			
					
					if( $this->is_editable ){
						
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
				
				echo'</div>';
			}
			else{
				
				// light header

				echo'<div class="profile-heading text-center" style="height:100px;padding:0;">';
				
					echo'<div class="profile-overlay"></div>';
				
					// mobile avatar
					
					echo'<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">';

						echo '<div class="profile-avatar text-left hidden-sm hidden-md hidden-lg" style="padding:12px 8px;position:absolute;">';
						
							echo'<img style="border:solid 5px #f9f9f9;" src="' . $this->picture . '" height="70" width="70" />';
							
							if( $this->is_pro ){
								
								echo'<span class="label label-primary" style="position:absolute;bottom:24%;margin-left:-30px;background:' . $this->parent->settings->mainColor . ';font-size:14px;">pro</span>';									
							}
							
						echo '</div>';					
					
					echo'</div>';
					
					echo'<div class="col-xs-9 col-sm-9 col-md-9 col-lg-10">';
					
						echo '<h2 style="font-size:30px;float:left;padding:31px 0 0 0;margin:0;">' . $name . '</h2>';
					
					echo'</div>';
					
				echo'</div>';
			}
			
			echo'<div id="panel" style="display:inline-block !important;margin-bottom:-8px !important;box-shadow:inset 0px 2px 11px -4px rgba(0,0,0,0.75);">';
			
				echo'<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2  text-center'.( $this->tab != 'about-me' ? ' hidden-xs' : '' ).'" style="padding:30px;">';
						
					// desktop avatar	
						
					echo '<div class="profile-avatar text-center hidden-xs" style="margin: -90px 10px 25px 10px;position:relative;">';
					
						echo'<img src="' . $this->picture . '" height="150" width="150" />';
						
						if( $this->is_pro ){
							
							echo'<span class="label label-primary" style="position:absolute;bottom:10%;right:16%;background:' . $this->parent->settings->mainColor . ';font-size:14px;">pro</span>';					
						}						
						
					echo '</div>';
					
					// user stars
					
					echo '<span class="badge" style="background-color:#fff;color:' . $this->parent->settings->mainColor . ';font-size:18px;border-radius: 25px;padding: 8px 18px;box-shadow: inset 0px 0px 1px #666;">';
						
						echo '<span class="glyphicon glyphicon-star" aria-hidden="true"></span> ';
						
						echo $this->parent->stars->get_count($this->user->ID);
				
					echo '</span>';
					
					// social icons

					echo '<div id="social_icons" class="text-center" style="margin:20px 0 0 0;">';
							
						do_action('ltple_before_social_icons');
						
						if( !empty($this->apps) ){		
							
							foreach( $this->apps as $app ){
								
								if( !empty($app->user_profile) && !empty($app->social_icon) ){
									
									$show_profile = get_user_meta($this->user->ID,$this->parent->_base . 'app_profile_' . $app->ID,true);
									
									if( $show_profile != 'off' ){
										
										echo'<a href="' . $app->user_profile . '" style="margin:5px;display:inline-block;" ref="nofollow" target="_blank">';
											
											echo'<img src="' . $app->social_icon . '" />';
											
										echo'</a>';
									}
								}
							}
						}
						
						do_action('ltple_after_social_icons');
						
					echo '</div>';
				
				echo '</div>';

				echo'<div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:0px;min-height:700px;">';
				
					echo'<ul class="nav nav-pills" role="tablist" style="box-shadow:inset 0px 2px 5px -4px rgba(0,0,0,0.75);overflow:visible;margin-top:0;">';
						
						foreach( $this->tabs as $tab){
							
							if( !empty($tab['name']) ){

								$active = ( $tab['slug'] == $this->tab ? ' active' : '');
 
								$url = $this->parent->profile->url . '/';

								if( $tab['slug'] != 'about-me' ){
									
									$url .= $tab['slug'] . '/';
								}
								
								echo'<li role="presentation" class="'.$active.'">';
								
									echo'<a href="' . $url . '" role="tab">'.$tab['name'].'</a>';
								
								echo'</li>';
							}
						}
						
					echo'</ul>';
					
					if( !$this->is_public && $this->self_profile ){
						
						echo '<div class="alert alert-warning row" style="margin: 20px 0 !important;">';
							
							echo'<div class="col-xs-9">';
							
								echo 'Your profile is restrected, only you can see this page.';
							
							echo '</div>';
							
							echo'<div class="col-xs-3 text-right">';
							
								echo '<a class="btn btn-sm btn-success" href="' . $this->parent->urls->profile . '?tab=privacy-policy">Start</a>';
							
							echo '</div>';
							
						echo '</div>';			
					}
					
					if( !empty($this->tabs) ){
					
						foreach( $this->tabs as $tab){
							
							if( !empty($tab['content']) && $tab['slug'] == $this->tab  ){

								echo'<div class="tab-pane active" id="'.$tab['slug'].'">';
								
									if(!empty($this->parent->message)){ 
									
										//output message
									
										echo $this->parent->message;
									}									
								
									echo $tab['content'];
									
								echo'</div>';
								
								break;
							}							
						}
					}
					
				echo '</div>';
				
			echo '</div>';

		echo '</div>';
		
		if( !$this->parent->user->loggedin ){

			// login modal
			
			include( $this->parent->views  . '/modals/login.php');
		}
	}
	else{ 
		
		echo '<div class="alert alert-warning" style="padding-top:50px;">';
		
			echo 'This profile is not accessible...';
			
		echo '</div>';
	}

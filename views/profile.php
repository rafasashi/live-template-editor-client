<?php
	
	$ltple = LTPLE_Client::instance();
	
	include_once( $ltple->views . '/profile/header.php' );
	
	if( $ltple->profile->is_public()|| $ltple->profile->is_self() ){
		
		// profile page
		
		echo'<div id="profile_page" style="display:block;">';

			if( $ltple->profile->tab == 'about' ){

				echo'<div id="panel" style="display:inline-block !important;box-shadow:inset 0px 2px 11px -4px rgba(0,0,0,0.75);">';

					echo'<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 hidden-xs text-center" style="padding:30px;">';
							
						// desktop avatar	
							
						echo '<div class="profile-avatar text-center hidden-xs" style="margin: -90px 10px 25px 10px;position:relative;">';
						
							echo'<img src="' . $ltple->profile->picture . '" height="150" width="150" />';
							
							if( $ltple->profile->is_pro ){
								
								echo'<span class="label label-primary" style="position:absolute;bottom:10%;right:16%;background:' . $ltple->settings->mainColor . ';font-size:14px;">pro</span>';					
							}						
							
						echo '</div>';
						
						if( $ltple->settings->options->enable_ranking == 'on' ){
							
							// user stars
							
							echo '<span class="badge" style="background-color:#fff;color:' . $ltple->settings->mainColor . ';font-size:18px;border-radius: 25px;padding: 8px 18px;box-shadow: inset 0px 0px 1px #666;">';
								
								echo '<span class="fa fa-star" aria-hidden="true"></span> ';
								
								echo $ltple->stars->get_count($ltple->profile->user->ID);
						
							echo '</span>';
						}
						
						// social icons

						echo '<div id="social_icons" class="text-center" style="margin:20px 0 0 0;">';
								
							do_action('ltple_before_social_icons');
							
							if( !empty($ltple->profile->apps) ){		
								
								foreach( $ltple->profile->apps as $app ){
									
									if( !empty($app->user_profile) && !empty($app->social_icon) ){
										
										$show_profile = get_user_meta($ltple->profile->user->ID,$ltple->_base . 'app_profile_' . $app->ID,true);
										
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

					echo'<div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 library-content" style="padding:0;border-left:1px solid #ddd;background:#fff;padding-bottom:0px;min-height:calc( 100vh - 150px );">';
					
						echo'<ul id="profile_nav" class="nav nav-pills nav-resizable" role="tablist">';
							
							if( $this->parent->inWidget ){
								
								echo'<li>';
								
									echo $this->parent->get_collapse_button();
									
								echo'</li>';
							}
							
							foreach( $ltple->profile->tabs as $tab){
								
								if( !empty($tab['name']) ){

									$active = ( $tab['slug'] == $ltple->profile->tab ? ' active' : '');
	 
									$url = $ltple->profile->url . '/';

									if( $tab['slug'] != 'home' ){
										
										$url .= $tab['slug'] . '/';
									}
									
									echo'<li role="presentation" class="'.$active.'">';
									
										echo'<a href="' . $url . '" role="tab">'.$tab['name'].'</a>';
									
									echo'</li>';
								}
							}
							
						echo'</ul>';
						
						if( !$ltple->profile->is_public() && $ltple->profile->is_self() ){
							
							echo '<div class="alert alert-warning row" style="margin:0 0 0 0 !important;">';
								
								echo'<div class="col-xs-9">';
								
									echo 'Your profile is restricted, only you can see this page.';
								
								echo '</div>';
								
								echo'<div class="col-xs-3 text-right">';
								
									echo '<a class="btn btn-sm btn-success" href="' . $ltple->urls->profile . '?tab=privacy-settings">Start</a>';
								
								echo '</div>';
								
							echo '</div>';			
						}
						elseif( $ltple->profile->is_unclaimed() ){
							
							echo '<div class="alert alert-info row" style="margin:0px 0 20px 0 !important;">';
								
								echo'<div class="col-xs-9">';
									
									echo 'This profile was auto generated';
								
								echo '</div>';
								
								echo'<div class="col-xs-3 text-right">';
								
									echo '<a class="btn btn-sm btn-success" href="' . $ltple->urls->home . '/contact/">Claim it</a>';
								
								echo '</div>';
								
							echo '</div>';
						}
						
						if( !empty($ltple->profile->tabs) ){
						
							foreach( $ltple->profile->tabs as $tab){
								
								if( !empty($tab['content']) && $tab['slug'] == $ltple->profile->tab  ){

									echo'<div class="tab-pane active" id="'.$tab['slug'].'">';
									
										if(!empty($ltple->message)){ 
										
											//output message
										
											echo $ltple->message;
										}									
									
										echo $tab['content'];
										
									echo'</div>';
									
									break;
								}							
							}
						}
						
					echo '</div>';
					
				echo '</div>';	
			}
			else{
				
				echo'<div id="panel" style="background:#fff;">';

					echo'<div class="library-content" style="padding:0;background:#fff;padding-bottom:0px;min-height:calc( 100vh - 130px );">';
						
						if( !$ltple->inWidget ){
							
							echo'<ul id="profile_nav" class="nav nav-pills nav-resizable" role="tablist">';
								
								foreach( $ltple->profile->tabs as $tab){
									
									if( !empty($tab['name']) ){

										$active = ( $tab['slug'] == $ltple->profile->tab ? ' active' : '');
		 
										$url = $ltple->profile->url . '/';

										if( $tab['slug'] != 'home' ){
											
											$url .= $tab['slug'] . '/';
										}
										
										echo'<li role="presentation" class="'.$active.'">';
										
											echo'<a href="' . $url . '" role="tab">'.$tab['name'].'</a>';
										
										echo'</li>';
									}
								}
								
							echo'</ul>';
						}
						
						if( !$ltple->profile->is_public() && $ltple->profile->is_self() ){
							
							echo '<div class="alert alert-warning row" style="margin:0 0 0 0 !important;">';
								
								echo'<div class="col-xs-9">';
								
									echo 'Your profile is restricted, only you can see this page.';
								
								echo '</div>';
								
								echo'<div class="col-xs-3 text-right">';
								
									echo '<a class="btn btn-sm btn-success" href="' . $ltple->urls->profile . '?tab=privacy-settings">Start</a>';
								
								echo '</div>';
								
							echo '</div>';			
						}
						elseif( $ltple->profile->is_unclaimed() ){
							
							echo '<div class="alert alert-info row" style="margin:0px 0 20px 0 !important;">';
								
								echo'<div class="col-xs-9">';
									
									echo 'This profile was auto generated';
								
								echo '</div>';
								
								echo'<div class="col-xs-3 text-right">';
								
									echo '<a class="btn btn-sm btn-success" href="' . $ltple->urls->home . '/contact/">Claim it</a>';
								
								echo '</div>';
								
							echo '</div>';
						}
						
						if( !empty($ltple->profile->tabs) ){
						
							foreach( $ltple->profile->tabs as $tab){
								
								if( !empty($tab['content']) && $tab['slug'] == $ltple->profile->tab  ){

									echo'<div class="tab-pane active" id="'.$tab['slug'].'">';
									
										if(!empty($ltple->message)){ 
										
											//output message
										
											echo $ltple->message;
										}									
									
										echo $tab['content'];

									echo'</div>';
									
									break;
								}							
							}
						}
						
					echo '</div>';
					
				echo '</div>';
			}

		echo '</div>';
		
		if( !$ltple->user->loggedin ){

			// login modal
			
			include( $ltple->views  . '/modals/login.php');
		}
	}
	else{
		
		echo '<div class="alert alert-warning" style="padding-top:50px;">';
		
			echo 'This profile is not accessible...';
			
		echo '</div>';
	}
	
	include_once( $ltple->views . '/profile/footer.php' );
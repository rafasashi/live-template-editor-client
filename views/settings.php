<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}	
	
	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'general-info' );

	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">My Profile</li>';
				
				echo'<li'.( $currentTab == 'general-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '">General Info</a></li>';
				
				echo'<li'.( $currentTab == 'privacy-settings' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=privacy-settings">Privacy Settings</a></li>';
				
				echo'<li'.( $currentTab == 'email-notifications' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=email-notifications">Email Notifications</a></li>';
								
				do_action('ltple_profile_settings_sidebar');
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';
			
				if( $currentTab == 'general-info' ){
				
					//---------------------- output General Info --------------------------
					
					echo'<div class="tab-pane active" id="general-info">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" enctype="multipart/form-data" class="tab-content row" style="margin:20px;">';
							
							echo'<input type="hidden" name="settings" value="general-info" />';
							
							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>General Information</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-primary" style="font-size: 13px;" href="'.$this->parent->urls->profile . $this->parent->user->ID . '/">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
			
									if(!empty($this->parent->profile->pictures )){
				
										foreach( $this->parent->profile->pictures as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
												
													$this->parent->admin->display_field( $field , $this->parent->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
									if(!empty($this->parent->profile->fields )){
										
										foreach( $this->parent->profile->fields as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
												
													$this->parent->admin->display_field( $field , $this->parent->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-primary" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';
				}
				elseif( $currentTab == 'privacy-settings' ){
				
					//---------------------- output Privacy Settings --------------------------
					
					echo'<div class="tab-pane active" id="privacy-settings">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';
							
							echo'<input type="hidden" name="settings" value="privacy-settings" />';
							
							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Privacy Settings</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-primary" style="font-size: 13px;" href="'.$this->parent->urls->profile . $this->parent->user->ID . '/">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
									
									if(!empty($this->parent->profile->privacySettings )){
										
										foreach( $this->parent->profile->privacySettings as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
												
													$this->parent->admin->display_field( $field , $this->parent->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-primary" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';
				}
				elseif( $currentTab == 'email-notifications' ){
				
					//---------------------- output Email Notifications --------------------------
					
					echo'<div class="tab-pane active" id="email-notifications">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';
							
							echo'<input type="hidden" name="settings" value="email-notifications" />';
							
							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Email Notifications</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
									
									if(!empty($this->parent->profile->notificationSettings )){
										
										foreach( $this->parent->profile->notificationSettings as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
												
													$this->parent->admin->display_field( $field , $this->parent->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-primary" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';
				}
				else{
					
					do_action('ltple_profile_settings_' . $currentTab );			
				}
				
			echo'</div>';
			
		echo'</div>';	

	echo'</div>';
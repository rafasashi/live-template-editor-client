<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}	
	
	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'general-info' );

	echo'<div id="media_library" class="wrapper">';

		echo '<div id="sidebar">';
			
			echo'<div class="gallery_type_title gallery_head">My Profile</div>';
				
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Profile Settings</li>';
				
				echo'<li'.( $currentTab == 'general-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '">General Info</a></li>';
				
				echo'<li'.( $currentTab == 'privacy-settings' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=privacy-settings">Privacy Settings</a></li>';
				
				echo'<li'.( $currentTab == 'social-accounts' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=social-accounts">Social Accounts</a></li>';
				
				do_action('ltple_profile_settings_sidebar',$currentTab);
								
				echo'<li class="gallery_type_title">Account Settings</li>';

				echo'<li'.( $currentTab == 'email-notifications' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=email-notifications">Email Notifications</a></li>';
								
				echo'<li><a href="' . wp_lostpassword_url() . '">Reset Password</a></li>';			
				
				echo'<li'.( $currentTab == 'billing-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->profile . '?tab=billing-info">Billing Info</a></li>';
				
				do_action('ltple_account_settings_sidebar',$currentTab);
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;">';
			
			echo'<div class="tab-content">';
			
				if( $currentTab == 'general-info' ){
				
					//---------------------- output General Info --------------------------
					
					echo'<div class="tab-pane active" id="general-info">';
					
						echo'<form method="post" enctype="multipart/form-data" class="tab-content row" style="margin:10px;">';
							
							echo'<input type="hidden" name="settings" value="general-info" />';
							
							echo'<div class="col-xs-8">';
						
								echo'<h3>General Information</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-4 text-right">';
								
								echo'<a target="_blank" class="label label-primary" style="font-size: 13px;" href="'.$this->parent->urls->profile . $this->parent->user->ID . '/">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-4 pull-right">';
								
								if( $completeness = $this->parent->profile->get_profile_completeness($this->parent->user->ID) ){
									
									echo'<div class="bs-callout bs-callout-default">';
									
										echo'<h4>Profile completeness</h4>';
										
										$progress 	= 0;
										$total 		= 0;
										
										foreach( $completeness as $completion ){
											
											$total += $completion['points'];
											
											if( $completion['complete'] === true ){
												
												$progress += $completion['points'];
											}
										}
										
										$progress = round($progress * 100 / $total, -1, PHP_ROUND_HALF_DOWN); 
				
										$status = 'danger';
										
										if( $progress > 69 ){
											
											$status = 'success';
										}
										elseif( $progress > 29 ){
											
											$status = 'warning';
										}
										
										echo'<div class="progress" style="margin-top:10px;">';
											
											echo'<div class="progress-bar progress-bar-' . $status . '" role="progressbar" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$progress.'%">';
												
												echo $progress . '%';
											
											echo'</div>';
											
										echo'</div>';					
										
										foreach( $completeness as $slug => $completion ){
											
											$complete = $completion['complete'];
											
											echo '<hr style="margin:15px 0;">';

											echo '<div style="font-size:18px;color:' . ( $complete ? '#5cb85c' : '#e0e0e0' ) .';">';
											
												echo '<span class="glyphicon glyphicon-' . ( $complete ? 'ok' : 'remove' ) .'" style="margin-right:5px;"; aria-hidden="true"></span>';
											
												echo $completion['name'];
												
											echo '</div>';
										}
										
									echo'</div>';
								}
								
							echo'</div>';
							
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
											
											$field_id = $field['id'];
											
											$field['data'] = isset($this->parent->user->{$field_id}) ? $this->parent->user->{$field_id} : '';
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
													
													$this->parent->admin->display_field( $field );
												
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
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row" style="margin:10px;">';
							
							echo'<input type="hidden" name="settings" value="privacy-settings" />';
							
							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Privacy Settings</h3>';
								
							echo'</div>';
							
							if(!empty($this->parent->profile->privacySettings )){
							
								echo'<div class="col-xs-12 col-sm-2 text-right">';
									
									echo'<a target="_blank" class="label label-primary" style="font-size: 13px;" href="'.$this->parent->urls->profile . $this->parent->user->ID . '/">view profile</a>';
									
								echo'</div>';
								
								echo'<div class="col-xs-12 col-sm-2"></div>';
								
								echo'<div class="clearfix"></div>';
							
								echo'<div class="col-xs-12 col-sm-8">';

									echo'<table class="form-table">';
										
										foreach( $this->parent->profile->privacySettings as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
												
													$this->parent->admin->display_field( $field , $this->parent->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
										
									echo'</table>';
									
								echo'</div>';
								
								echo'<div class="clearfix"></div>';
								
								echo'<div class="col-xs-12 col-sm-6"></div>';
								
								echo'<div class="col-xs-12 col-sm-2 text-right">';
							
									echo'<button class="btn btn-sm btn-primary" style="width:100%;margin-top: 10px;">Save</button>';
									
								echo'</div>';

								echo'<div class="col-xs-12 col-sm-4"></div>';
							}
							
						echo'</form>';
						
					echo'</div>';
				}
				elseif( $currentTab == 'social-accounts' ){
				
					//---------------------- output Social Accounts --------------------------
					
					echo'<div class="tab-pane active" id="social-accounts">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row" style="margin:10px;">';
							
							echo'<input type="hidden" name="settings" value="social-accounts" />';
							
							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Social Accounts</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-success" style="font-size: 13px;" href="'.$this->parent->urls->apps . '">+ add accounts</a>';
								
							echo'</div>';							
							
							if(!empty($this->parent->profile->socialAccounts )){		
	
								echo'<div class="col-xs-12 col-sm-2"></div>';
								
								echo'<div class="clearfix"></div>';
								
								echo'<div class="col-xs-12 col-sm-8">';

									echo'<table class="form-table">';
										
										foreach( $this->parent->profile->socialAccounts as $label => $fields ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$label.'">'.$label.'</label></th>';
												
												echo'<td style="padding:10px;">';
													
													foreach( $fields as $field ){
														
														echo'<div style="padding:10px;">';
														
															$this->parent->admin->display_field( $field , $this->parent->user );
													
														echo'</div>';
													}
													
												echo'</td>';
												
											echo'</tr>';
										}
										
									echo'</table>';
									
								echo'</div>';
								
								echo'<div class="clearfix"></div>';
								
								echo'<div class="col-xs-12 col-sm-6"></div>';
								
								echo'<div class="col-xs-12 col-sm-2 text-right">';
							
									echo'<button class="btn btn-sm btn-primary" style="width:100%;margin-top: 10px;">Save</button>';
									
								echo'</div>';

								echo'<div class="col-xs-12 col-sm-4"></div>';
							}
								
						echo'</form>';
						
					echo'</div>';
				}
				elseif( $currentTab == 'email-notifications' ){
				
					//---------------------- output Email Notifications --------------------------
					
					echo'<div class="tab-pane active" id="email-notifications">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row" style="margin:10px;">';
							
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
				elseif( $currentTab == 'billing-info' ){
					
					echo'<div class="tab-pane active" id="billing-info">';
					
						echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content" style="margin-top:20px;">';

							echo'<div class="col-xs-12">';
						
								echo'<h3>Billing Information</h3>';
								
								echo'<hr></hr>';
								
							echo'</div>';

							echo'<div class="col-xs-12">';

								$user_plan = $this->parent->plan->get_user_plan_info( $this->parent->user->ID );
			
								if( $user_plan['holder'] == $this->parent->user->ID ){
							
									$plan_usage = $this->parent->plan->get_user_plan_usage( $this->parent->user->ID );
							
									echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
										
										echo'<b>Price</b>: ' . $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].' / '.$user_plan['info']['total_price_period'] . '<br/>';
										
									echo '</div>';
									
									echo $this->parent->plan->get_plan_table($user_plan,$plan_usage);

									echo'<hr>';
									
									echo '<div class="panel panel-default">';
								
										echo '<div class="panel-heading"><b>License & Payment</b></div>';
										
										echo '<div class="panel-body">';								
				
											echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
				
											echo '<iframe src="' . $this->parent->server->url . '/agreement/?overview=' . $this->parent->ltple_encrypt_uri($this->parent->user->user_email) . '&_='.time().'" style="margin-top: -65px;position:relative;top:0;bottom:0;width:100%;height:500px;overflow:hidden;border:0;"></iframe>';
											
										echo '</div>';
										
									echo '</div>';	
								}
								else{
									
									$license_holder = get_user_by('id',$user_plan['holder']);
									
									echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
										
										echo'Your license is currently handled by <a style="font-weight:bold;" href="' . $this->parent->urls->profile . $license_holder->ID . '/">' . ucfirst($license_holder->nickname) . '</a>';
										
									echo '</div>';									
								}
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								//echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';					
				}
				else{
					
					do_action( 'ltple_profile_settings_' . $currentTab );			
				}
				
			echo'</div>';
			
		echo'</div>';	

	echo'</div>';
<?php 
	
	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'email-notifications' );

	echo'<div id="media_library" class="wrapper">';

		echo '<div id="sidebar">';
			
			echo'<div class="gallery_type_title gallery_head">Account Settings</div>';
				
			echo'<ul class="nav nav-tabs tabs-left">';

				//echo'<li class="gallery_type_title">Account Settings</li>';

				echo'<li'.( $currentTab == 'email-notifications' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->account . '?tab=email-notifications">Email Notifications</a></li>';
								
				echo'<li'.( $currentTab == 'billing-info' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->account . '?tab=billing-info">Billing Info</a></li>';
				
				do_action('ltple_account_settings_sidebar',$currentTab);

				echo'<li><a href="' . wp_lostpassword_url() . '">Reset Password</a></li>';

			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'email-notifications' ){
				
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
									
									if(!empty($this->parent->account->notificationSettings )){
										
										foreach( $this->parent->account->notificationSettings as $field ){
											
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
							
									if( empty($this->parent->user->period_end) ){
							
										echo '<div class="alert alert-warning">There is no active subscription for the moment, please renew your license via the plan page or contact us.</div>';
									
										echo '<hr>';
									}
									else{
											
										$plan_usage = $this->parent->plan->get_user_plan_usage( $this->parent->user->ID );

										$remaining_days = $this->parent->user->remaining_days;
										
										if( $remaining_days < 0 ){
											
											$this->parent->users->update_periods($this->parent->user->ID);
										
											$remaining_days = $this->parent->users->get_user_remaining_days($this->parent->user->ID);
										}

										if( $remaining_days < 0 ){
							
											echo '<div class="alert alert-warning">Your license could not be renewed, please update your card details or contact us...</div>';
											
											echo '<hr>';
										}
										
										echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
												
											echo'<b>Price</b>: ' . $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].' / '.$user_plan['info']['total_price_period'] . '<br/>';
												
										echo '</div>';
										
										echo $this->parent->plan->get_plan_table($user_plan,$plan_usage);
									}
								}
								else{
									
									$license_holder = get_user_by('id',$user_plan['holder']);
									
									echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
										
										echo'Your license is currently handled by <a style="font-weight:bold;" href="' . $this->parent->urls->profile . $license_holder->ID . '/">' . ucfirst($license_holder->nickname) . '</a>';
										
									echo '</div>';									
								}

								echo '<div class="panel panel-default">';
							
									echo '<div class="panel-heading"><b>License & Payment</b></div>';
									
									echo '<div class="panel-body">';								
			
										echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->assets_url . '/loader.gif\');height:64px;"></div>';
			
										echo '<iframe src="' . $this->parent->server->url . '/agreement/?overview=' . $this->parent->ltple_encrypt_uri($this->parent->user->user_email) . '&du=' . parse_url($this->parent->urls->primary,PHP_URL_HOST) . '&_='.time().'" style="margin-top: -65px;position:relative;top:0;bottom:0;width:100%;height:500px;overflow:hidden;border:0;"></iframe>';
										
									echo '</div>';
									
								echo '</div>';
								
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
					
					do_action( 'ltple_account_settings_' . $currentTab );			
				}
				
			echo'</div>';
			
		echo'</div>';	

	echo'</div>';
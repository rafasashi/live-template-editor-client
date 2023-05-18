<?php 

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab('email-notifications');

echo'<div id="media_library" class="wrapper">';

	echo '<div id="sidebar">';
		
		echo'<div class="gallery_type_title gallery_head">Account Settings</div>';
			
		echo'<ul class="nav nav-tabs tabs-left">';

			//echo'<li class="gallery_type_title">Account Settings</li>';

			echo'<li'.( $currentTab == 'email-notifications' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->account . '?tab=email-notifications">Email Notifications</a></li>';
							
			echo'<li'.( $currentTab == 'billing-info' ? ' class="active"' : '' ).'><a href="'.$ltple->urls->account . '?tab=billing-info">Billing Info</a></li>';
			
			do_action('ltple_account_settings_sidebar',$currentTab);

			echo'<li><a href="' . wp_lostpassword_url() . '">Reset Password</a></li>';

		echo'</ul>';
		
	echo'</div>';

	echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;">';
		
		echo'<div class="tab-content">';

			if( $currentTab == 'email-notifications' ){
			
				//---------------------- output Email Notifications --------------------------
				
				echo'<div class="tab-pane active" id="email-notifications">';
				
					echo'<form action="' . $ltple->urls->current . '" method="post" class="tab-content row" style="margin:10px;">';
						
						echo'<input type="hidden" name="settings" value="email-notifications" />';
						
						echo'<div class="col-xs-12 col-sm-6">';
					
							echo'<h3>Email Notifications</h3>';
							
						echo'</div>';
						
						echo'<div class="col-xs-12 col-sm-2"></div>';
						
						echo'<div class="clearfix"></div>';
					
						echo'<div class="col-xs-12 col-sm-8">';

							echo'<table class="form-table">';
								
								if(!empty($ltple->account->notificationSettings )){
									
									foreach( $ltple->account->notificationSettings as $field ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
											
											echo'<td style="padding:20px;">';
											
												$ltple->admin->display_field( $field , $ltple->user );
											
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
					
					echo'<ul class="nav nav-pills nav-resizable" role="tablist" style="overflow: visible;">';
					
						echo'<li role="presentation" class="active">';
						
							echo'<a href="' . $ltple->urls->current . '" role="tab">Current Plan</a></li>';
						
						echo'</li>';

					echo'</ul>';
					
					echo '<div class="container">';
						
						echo'<form action="' . $ltple->urls->current . '" method="post" class="tab-content" style="margin-top:20px;">';

							$user_plan = $ltple->plan->get_user_plan_info( $ltple->user->ID );
		
							if( $user_plan['holder'] == $ltple->user->ID ){
						
								if( empty($ltple->user->period_end) ){
						
									echo '<div class="alert alert-warning">There is no active subscription for the moment, please renew your license via the plan page or contact us.</div>';
								
									echo '<hr>';
								}
								else{
										
									$plan_usage = $ltple->plan->get_user_plan_usage( $ltple->user->ID );

									$remaining_days = $ltple->user->remaining_days;
									
									if( $remaining_days < 0 ){
										
										$ltple->users->remote_update_period($ltple->user->ID);
									
										$remaining_days = $ltple->users->get_user_remaining_days($ltple->user->ID);
									}

									if( $remaining_days < 0 ){
						
										echo '<div class="alert alert-warning">Your license could not be renewed, please update your card details or contact us...</div>';
										
										echo '<hr>';
									}
									
									echo $ltple->plan->get_plan_table($user_plan,$plan_usage);
								}
							}
							else{
								
								$license_holder = get_user_by('id',$user_plan['holder']);
								
								echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
									
									echo'Your license is currently handled by <a style="font-weight:bold;" href="' . $ltple->urls->profile . $license_holder->ID . '/">' . ucfirst($license_holder->nickname) . '</a>';
									
								echo '</div>';									
							}
						
							echo'<h2><i class="fas fa-file-invoice"></i> License & Payment</h2>';

							echo '<div class="panel panel-default">';
						
								echo '<div class="panel-body">';								
		
									echo '<iframe data-src="' . $ltple->server->url . '/agreement/?overview=' . $ltple->ltple_encrypt_uri($ltple->user->user_email) . '&du=' . parse_url($ltple->urls->primary,PHP_URL_HOST) . '&_='.time().'" style="position:relative;top:0;bottom:0;width:100%;height:500px;overflow:hidden;border:0;"></iframe>';
									
								echo '</div>';
								
							echo '</div>';
							

						echo'</form>';
						
					echo'</div>';	
					
				echo'</div>';					
			}
			else{
				
				do_action( 'ltple_account_settings_' . $currentTab );			
			}
			
		echo'</div>';
		
	echo'</div>';	

echo'</div>';
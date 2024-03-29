<?php 

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab('general-info');

echo'<div id="media_library" class="wrapper">';

	echo '<div id="sidebar">';
			
		echo '<ul class="nav nav-tabs tabs-left">';
			
			echo apply_filters('ltple_settings_sidebar','',$currentTab);
			
		echo '</ul>';
		
	echo '</div>';
	
	echo'<div id="content" class="library-content">';
		
		echo'<div class="tab-content">';
		
			if( $currentTab == 'general-info' ){
			
				//---------------------- output General Info --------------------------
				
				echo'<div class="tab-pane active" id="general-info">';
				
					echo'<form method="post" enctype="multipart/form-data" class="tab-content row" style="margin:5px 5px 50px 5px;">';
						
						echo'<input type="hidden" name="settings" value="general-info" />';
						
						echo'<div class="col-xs-8">';
					
							echo'<h3>General Information</h3>';
							
						echo'</div>';
						
						echo'<div class="col-xs-4 text-right">';
							
							
						echo'</div>';
						
						echo'<div class="col-xs-12 col-sm-2"></div>';
						
						echo'<div class="clearfix"></div>';
						
						echo'<div class="col-xs-12 col-sm-4 pull-right">';
							
							if( $completeness = $ltple->profile->get_profile_completeness($ltple->user->ID) ){
								
								echo'<div class="bs-callout bs-callout-default" style="margin-top:0;">';
								
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
		
								if(!empty($ltple->profile->pictures )){
			
									foreach( $ltple->profile->pictures as $field ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
											
											echo'<td style="padding:20px;">';
											
												$ltple->admin->display_field( $field , $ltple->user );
											
											echo'</td>';
											
										echo'</tr>';
									}
								}
								
								if(!empty($ltple->profile->fields )){
									
									foreach( $ltple->profile->fields as $field ){
										
										if( $field['location'] == 'general-info' ){
											
											$field_id = $field['id'];
											
											$field['data'] = isset($ltple->user->{$field_id}) ? $ltple->user->{$field_id} : '';
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td style="padding:20px;">';
													
													$ltple->admin->display_field( $field );
												
												echo'</td>';
												
											echo'</tr>';
										}
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
				
					echo'<form action="' . $ltple->urls->current . '" method="post" class="tab-content row" style="margin:5px;">';
						
						echo'<input type="hidden" name="settings" value="privacy-settings" />';
						
						echo'<div class="col-xs-12 col-sm-6">';
					
							echo'<h3>Privacy Settings</h3>';
							
						echo'</div>';
						
						if( $fields = $ltple->profile->get_privacy_fields() ){
						
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
								
									$labels = array();
									
									foreach( $fields as $field ){
										
										$label = $field['label'];
										
										$labels[$label][] = $field;
									}
									
									foreach( $labels as $label => $fields ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$label.'">'.ucfirst($label).'</label></th>';
									
											foreach( $fields as $field ){

												echo'<td style="padding:20px;">';
													
													$ltple->admin->display_field( $field , $ltple->user );
													
												echo'</td>';
											}
											
										echo'</tr>';
									}

								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right"  style="margin-bottom:50px;">';
						
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
				
					echo'<form action="' . $ltple->urls->current . '" method="post" class="tab-content row" style="margin:5px;">';
						
						echo'<input type="hidden" name="settings" value="social-accounts" />';
						
						echo'<div class="col-xs-12 col-sm-6">';
					
							echo'<h3>Social Accounts</h3>';
							
						echo'</div>';
						
						echo'<div class="col-xs-12 col-sm-2 text-right">';
							
							echo'<a class="label label-success" style="font-size: 13px;" href="'.$ltple->urls->apps . '">+ add accounts</a>';
							
						echo'</div>';							
						
						if( $accounts = $this->get_social_accounts() ){		

							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
									
									foreach( $accounts as $label => $fields ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$label.'">'.$label.'</label></th>';
											
											echo'<td style="padding:10px;">';
												
												foreach( $fields as $field ){
													
													echo'<div style="padding:10px;">';
													
														$ltple->admin->display_field( $field , $ltple->user );
												
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
			else{
				
				do_action( 'ltple_profile_settings_' . $currentTab );			
			}
			
		echo'</div>';
		
	echo'</div>';	

echo'</div>';
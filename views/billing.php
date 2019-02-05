<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	
	// get current tab
	
	$currentTab = ( !empty($_GET['my-profile']) ? $_GET['my-profile'] : 'billing-info' );

	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">My Account</li>';
				
				echo'<li'.( $currentTab == 'billing-info' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?my-profile">Billing Info</a></li>';

				do_action('ltple_billing_settings_sidebar');
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';
				
				if( $currentTab == 'billing-info' ){
					
					echo'<div class="tab-pane active" id="billing-info">';
					
						echo'<form action="' . $this->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';

							echo'<div class="col-xs-12">';
						
								echo'<h3>Billing Information</h3>';
								
								echo'<hr></hr>';
								
							echo'</div>';

							echo'<div class="col-xs-12">';

								$user_plan = $this->plan->get_user_plan_info( $this->user->ID );
									
								echo '<div style="margin-bottom:20px;background: rgb(248, 248, 248);display:block;padding:20px;text-align:left;border-left: 5px solid #888;">';
									
									echo'<b>Price</b>: ' . $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].' / '.$user_plan['info']['total_price_period'] . '<br/>';
									echo'<b>Storage</b>: ' . ( !empty($user_plan['info']['total_storage']['templates']) ? $user_plan['info']['total_storage']['templates'] : 0 ) . ' templates' . '<br/>';
									
								echo '</div>';							
								
								echo '<div class="panel panel-default">';
								
									echo '<div class="panel-heading"><b>Template Types</b></div>';
									
									echo '<div class="panel-body">';
										
										$none = true;
										
										if( !empty($user_plan['taxonomies']['layer-type']['terms']) ){

											foreach( $user_plan['taxonomies']['layer-type']['terms'] as $term ){
												
												if( $term['has_term'] ){
													
													echo '<div class="col-xs-12">'.ucfirst($term['name']).'</div>';
												
													$none = false;
												}
											}
										}
										
										if( $none === true ){
											
											echo '<div class="col-xs-12">none</div>';
										}
										
									echo '</div>';
									
								echo '</div>';
								
								echo '<div class="panel panel-default">';
							
									echo '<div class="panel-heading"><b>Template Ranges</b></div>';
									
									echo '<div class="panel-body">';								
											
										$none = true;
											
										if( !empty($user_plan['taxonomies']['layer-range']['terms']) ){

											foreach( $user_plan['taxonomies']['layer-range']['terms'] as $term ){
												
												if( $term['has_term'] ){
													
													echo '<div class="col-xs-12">'.ucfirst($term['name']).'</div>';
												
													$none = false;
												}
											}
										}
										
										if( $none === true ){
											
											echo '<div class="col-xs-12">none</div>';
										}
										
									echo '</div>';
									
								echo '</div>';

								echo '<div class="panel panel-default">';
							
									echo '<div class="panel-heading"><b>License & Payment</b></div>';
									
									echo '<div class="panel-body">';								
			
										echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
			
										echo '<iframe src="' . $this->server->url . '/agreement/?overview=' . $this->ltple_encrypt_uri($this->user->user_email) . '&_='.time().'" style="margin-top: -65px;position:relative;top:0;bottom:0;width:100%;height:500px;overflow:hidden;border:0;"></iframe>';
										
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
					
					do_action('ltple_billing_settings_' . $currentTab );				
				}
				
			echo'</div>';
			
		echo'</div>';	

	echo'</div>';
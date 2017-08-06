<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	
	$inWidget = false;
	$output='default';
	$target='_self';

	if( isset($_GET['output']) && $_GET['output'] == 'widget' ){
		
		$inWidget = true;
		$output=$_GET['output'];
		$target='_blank';
	}	
	
	// get current tab
	
	$currentTab = 'general-info';
	
	if( !empty($_GET['my-profile']) ){
		
		$currentTab = $_GET['my-profile'];
	}

	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">My Profile</li>';
				
				echo'<li'.( $currentTab == 'general-info' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?my-profile=general-info&output='.$output.'">General Info</a></li>';
				
				echo'<li class="gallery_type_title">My Account</li>';
				
				echo'<li'.( $currentTab == 'billing-info' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?my-profile=billing-info&output='.$output.'">Billing Info</a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'general-info' ){
				
					//---------------------- output General Info --------------------------
					
					echo'<div class="tab-pane active" id="general-info">';
					
						echo'<form action="' . $this->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';

							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>General Information</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-warning" style="font-size: 13px;" href="'.$this->urls->editor . '?pr='.$this->user->ID . '">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
			
									if(!empty($this->user->profile->pictures )){
				
										foreach( $this->user->profile->pictures as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td>';
												
													$this->admin->display_field( $field , $this->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
									if(!empty($this->profile->fields )){
										
										foreach( $this->profile->fields as $field ){
											
											echo'<tr>';
											
												echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
												
												echo'<td>';
												
													$this->admin->display_field( $field , $this->user );
												
												echo'</td>';
												
											echo'</tr>';
										}
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';
				}	
					
				/*

				if( $currentTab == 'custom-profile' ){
					
					//---------------------- output Custom Profile --------------------------
					
					echo'<div class="tab-pane active" id="custom-profile">';
						
						echo'<form action="' . $this->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';

							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Custom Profile</h3>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-warning" style="font-size: 13px;" href="'.$this->urls->editor . '?pr='.$this->user->ID . '">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
								
									foreach( $this->user->profile->customization as $field ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
											
											echo'<td>';
											
												$this->admin->display_field( $field , $this->user );
											
											echo'</td>';
											
										echo'</tr>';
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';

							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Update</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
			
						echo'</form>';
						
					echo'</div>';
				}
				*/
					
				if( $currentTab == 'connected-apps' ){

					//---------------------- output Connected Apps --------------------------
					
					echo'<div class="tab-pane active" id="connected-apps">';
					
						echo'<form action="' . $this->urls->current . '" method="post" class="tab-content row" style="margin:20px;">';

							echo'<div class="col-xs-12 col-sm-6">';
						
								echo'<h3>Displayed connected accounts</h3>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-2 text-right">';
								
								echo'<a target="_blank" class="label label-warning" style="font-size: 13px;" href="'.$this->urls->editor . '?pr='.$this->user->ID . '">view profile</a>';
								
							echo'</div>';
							
							echo'<div class="col-xs-12 col-sm-2"></div>';
							
							echo'<div class="clearfix"></div>';
						
							echo'<div class="col-xs-12 col-sm-8">';

								echo'<table class="form-table">';
									
									foreach( $this->user->profile->apps as $field ){
										
										echo'<tr>';
										
											echo'<th><label for="'.$field['label'].'">'.ucfirst($field['label']).'</label></th>';
											
											echo'<td>';
											
												$this->admin->display_field( $field , $this->user );
											
											echo'</td>';
											
										echo'</tr>';
									}
									
								echo'</table>';
								
							echo'</div>';
							
							echo'<div class="clearfix"></div>';
							
							echo'<div class="col-xs-12 col-sm-6"></div>';
							
							echo'<div class="col-xs-12 col-sm-2 text-right">';
						
								echo'<button class="btn btn-sm btn-warning" style="width:100%;margin-top: 10px;">Save</button>';
								
							echo'</div>';

							echo'<div class="col-xs-12 col-sm-4"></div>';
								
						echo'</form>';
						
					echo'</div>';
					
				}
					
				/*	
				
				//---------------------- output HTML markups --------------------------
				
				if( $currentTab == 'html-markups' ){
				
					echo'<div class="tab-pane active" id="html-markups">';
					
						echo'<h3>List of allowed HTML markups</h3>';
					
						echo'<div class="col-xs-12 wrapper">';
						echo'<div class="col-xs-12 dasheaders">';
						echo'<div class="col-xs-3 hed center">Open tag</div>';
						echo'<div class="col-xs-3 hed center">Closed tag</div>';
						echo'<div class="col-xs-6 hed center">Info</div>';

						echo'<div class="col-xs-3 markup">&lt;a&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/a&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a hyperlink, anchor link</div>';

						echo'<div class="col-xs-3 markup">&lt;p&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/p&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a paragraph</div>';

						echo'<div class="col-xs-3 markup">&lt;br&gt;</div>';
						echo'<div class="col-xs-3 markup na">n/a</div>';
						echo'<div class="col-xs-6 info">Inserts a single line break</div>';

						echo'<div class="col-xs-3 markup">&lt;hr&gt;</div>';
						echo'<div class="col-xs-3 markup na">n/a</div>';
						echo'<div class="col-xs-6 info">Defines a thematic change in the content - horizontal rule</div>';

						echo'<div class="col-xs-3 markup">&lt;b&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/b&gt;</div>';
						echo'<div class="col-xs-6 info">Defines bold text</div>';

						echo'<div class="col-xs-3 markup">&lt;em&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/em&gt;</div>';
						echo'<div class="col-xs-6 info">Defines emphasized text</div>';

						echo'<div class="col-xs-3 markup">&lt;i&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/i&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a part of text in an alternate voice or mood</div>';

						echo'<div class="col-xs-3 markup">&lt;strong&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/strong&gt;</div>';
						echo'<div class="col-xs-6 info">Defines important text - similar output like <strong>b</strong> tag</div>';

						echo'<div class="col-xs-3 markup">&lt;u&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/u&gt;</div>';
						echo'<div class="col-xs-6 info">Defines text that should be stylistically different from normal text - underline</div>';

						echo'<div class="col-xs-3 markup">&lt;img/&gt;</div>';
						echo'<div class="col-xs-3 markup"></div>';
						echo'<div class="col-xs-6 info">Defines an image</div>';

						echo'<div class="col-xs-3 markup">&lt;ul&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/ul&gt;</div>';
						echo'<div class="col-xs-6 info">Defines an unordered list</div>';

						echo'<div class="col-xs-3 markup">&lt;ol&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/ol&gt;</div>';
						echo'<div class="col-xs-6 info">Defines an ordered list</div>';

						echo'<div class="col-xs-3 markup">&lt;li&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/li&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a list item</div>';

						echo'<div class="col-xs-3 markup">&lt;div&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/div&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a section in a document</div>';

						echo'<div class="col-xs-3 markup">&lt;span&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/span&gt;</div>';
						echo'<div class="col-xs-6 info">Defines a section in a document</div>';

						echo'<div class="col-xs-3 markup">&lt;header&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/header&gt;</div>';
						echo'<div class="col-xs-6 info html5">Defines a header for a document or section</div>';

						echo'<div class="col-xs-3 markup">&lt;footer&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/footer&gt;</div>';
						echo'<div class="col-xs-6 info html5">Defines a footer for a document or section</div>';

						echo'<div class="col-xs-3 markup">&lt;main&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/main&gt;</div>';
						echo'<div class="col-xs-6 info html5">Specifies the main content of a document</div>';

						echo'<div class="col-xs-3 markup">&lt;section&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/section&gt;</div>';
						echo'<div class="col-xs-6 info html5">Defines a section in a document</div>';

						echo'<div class="col-xs-3 markup">&lt;article&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/article&gt;</div>';
						echo'<div class="col-xs-6 info html5">Defines an article</div>';

						echo'<div class="col-xs-3 markup">&lt;aside&gt;</div>';
						echo'<div class="col-xs-3 markup">&lt;/aside&gt;</div>';
						echo'<div class="col-xs-6 info html5">Defines content aside from the page content</div>';


						echo'</div>';
						echo'</div>';				
									
					echo'</div>';
					
				}
				
				*/
				
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
			
										echo '<iframe src="' . $this->server->url . '/agreement/?overview=' . $this->ltple_encrypt_uri($this->user->user_email) . '" style="margin-top: -65px;position:relative;top:0;bottom:0;width:100%;height:500px;overflow:hidden;border:0;"></iframe>';
										
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

			echo'</div>';
			
		echo'</div>';	

	echo'</div>';
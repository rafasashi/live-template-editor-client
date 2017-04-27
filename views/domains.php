<?php 

	if(!empty($this->message)){ 
	
		//output message
	
		echo $this->message;
	}

	// get current tab
	
	$currentTab = 'domain';
	
	if( in_array($_GET['domain'],['list','urls']) ){
		
		$currentTab = $_GET['domain'];
	}
	
	// ------------- output panel --------------------
	
	echo'<div id="domains">';

		echo'<div class="col-xs-3 col-sm-2">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">My Domains</li>';
				
				echo'<li'.( $currentTab == 'domain' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?domain">Domain Listing <span class="label label-success pull-right"> pro </span></a></li>';

				echo'<li'.( $currentTab == 'urls' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?domain=urls">Urls & Pages <span class="label label-success pull-right"> pro </span></a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;background:#fff;padding-top:15px;padding-bottom:15px;min-height:500px;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'domain' ){
					
					//---------------------- output default domains --------------------------
					
					echo'<div id="domain-listing">';
					
						if(!empty($this->message)){
							
							echo $this->message;
						}
						else{
							
							echo'<div class="bs-callout bs-callout-primary">';

								echo '<h4>Manage Domains and Subdomains</h4>';

								echo '<p>';
								
									
								
								echo'</p>';
							
							echo'</div>';
							
							if(!empty($this->user->domains->list)){
								
								echo'<div class="panel-body">';
																
								echo'<table class="table table-striped">';
								
									echo'<thead>';
									
										echo'<tr>';
										
											echo'<th><b>Domains</b></th>';
											
										echo'</tr>';
										
									echo'</thead>';
									
									echo'<tbody>';
								
									foreach($this->user->domains->list as $domain){
										
										echo'<tr>';
										echo'<td>';
										
											echo $domain->post_title;
										
										echo'</td>';
										echo'</tr>';
									}

									echo'</tbody>';
									
								echo'</table>';											
								echo'</div>';		
							}
							else{
								
								echo'<div class="well">';
								
									echo 'No domains found';
								
								echo'</div>';	
							}		
						}
						
					echo'</div>';
					
				}
				elseif( $currentTab == 'urls' ){

					//---------------------- output members --------------------------
					
					echo'<div id="urls">';

							echo'<div class="bs-callout bs-callout-primary">';

								echo '<h4>Assign Urls to Pages</h4>';

								echo '<p></p>';
							
							echo'</div>';
						
							if( !empty( $this->user->layers ) ){
					
								echo'<table class="table table-striped">';
								
									/*
									echo'<thead>';
									
										echo'<tr>';
										
											echo'<th><b>Templates</b></th>';
											echo'<th><b>View</b></th>';
											
										echo'</tr>';
										
									echo'</thead>';
									*/
									
									echo'<tbody>';
								
									foreach($this->user->layers as $layer){
										/*
										echo'<pre>';
										var_dump($layer);
										exit;
										*/
										
										echo'<tr>';
										
											echo'<td>';
											
												echo $layer->post_title;
											
											echo'</td>';

											echo'<td style="width:545px;">';
											
												echo'<form action="' . $this->urls->current . '" method="post">';
											
													echo'<input type="hidden" name="layerId" value="' . $layer->ID . '" />';
													
													echo'<input type="hidden" name="domainAction" value="assign" />';
											
													echo'<select name="domainUrl[domainId]" class="form-control input-sm" style="width:150px;display:inline-block;">';
														
														echo'<option value="-1">None</option>';
														
														if(!empty($this->user->domains->list)){
															
															$domainName = '';
															
															foreach($this->user->domains->list as $domain){
																
																if(isset($domain->domainUrls[$layer->ID])){
																	
																	$domainName = $domain->post_title;
																}																
																
																echo'<option value="' . $domain->ID . '"' . ( ( $domainName == $domain->post_title ) ? ' selected="true"' : '' ) . '>';
																
																	echo $domain->post_title;
				
																echo'</option>';
															}
														}
													
													echo'</select>';
													
													echo' / ';
													
													$domainPath = '';
													
													foreach($this->user->domains->list as $domain){
														
														if(isset($domain->domainUrls[$layer->ID])){
															
															$domainPath = $domain->domainUrls[$layer->ID];
														}
													}
													
													echo'<input type="text" name="domainUrl[domainPath]" value="'.$domainPath.'" placeholder="category/page-title" class="form-control input-sm" style="width:300px;display:inline-block;" />';
												
													echo' <button type="submit" class="btn btn-primary btn-sm" >assign</button>';
												
												echo'</form>';
												
											echo'</td>';	
											
											echo'<td style="width:50px;">';
											
												if( !empty($domainName) ){
													
													$domainUrl = 'http://'.$domainName.'/'.$domainPath;
												}
												else{
													
													$domainUrl = get_permalink($layer->ID);
												}

												echo '<a href="' . $domainUrl . '" target="_blank" class="btn btn-success btn-sm" style="margin-left: 4px;border-color: #9c6433;color: #fff;background-color: rgb(189, 120, 61);">';
												
													echo 'view';
												
												echo '</a>';
											
											echo'</td>';
											
											echo'<td style="width:50px;">';
											
												echo '<a href="' . $this->urls->editor .'?uri=' . $layer->ID . '" target="_blank" class="btn btn-success btn-sm">';
												
													echo 'edit';
												
												echo '</a>';

											echo'</td>';											

											echo'<td style="width:30px;">';
											
												echo '<a href="' . $this->urls->editor .'?'. $_SERVER['QUERY_STRING'] . '&uri=' . $layer->ID . '&postAction=delete" target="_self" class="btn btn-danger btn-sm" style="font-weight: bold;">';
												
													echo 'x';
												
												echo '</a>';
											
											echo'</td>';												
										
										echo'</tr>';
									}

									echo'</tbody>';
									
								echo'</table>';
							}
							else{
								
								echo'<div class="well">';
								
									echo 'No saved templates found';
								
								echo'</div>';
							}
					
					echo'</div>';
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

			
				
			});
			
		})(jQuery);

	</script>
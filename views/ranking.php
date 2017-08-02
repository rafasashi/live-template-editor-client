<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}

	$tab = ( !empty($_GET['rank']) ? $_GET['rank'] : 'world-ranking' );
	
	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Ranking System</li>';
				
				echo'<li' . ( $tab == 'world-ranking' ? ' class="active"' : '' ) . '><a href="' . $this->urls->editor . '?rank=world-ranking" >World Ranking</a></li>';
				
				if($this->user->loggedin){
				
					echo'<li' . ( $tab == 'ranking-rules' ? ' class="active"' : '' ) . '><a href="' . $this->urls->editor . '?rank=ranking-rules" >Rules & Points</a></li>';

					echo'<li class="gallery_type_title">Referral Tools</li>';
				
					echo'<li' . ( $tab == 'my-ref-urls' ? ' class="active"' : '' ) . '><a href="' . $this->urls->editor . '?rank=my-ref-urls" >My Referral urls</a></li>';
					
					echo'<li' . ( $tab == 'invite-contacts' ? ' class="active"' : '' ) . '><a href="' . $this->urls->editor . '?rank=invite-contacts" >Invite Contacts <span class="label label-success pull-right">new</span></a></li>';
				}
				
			echo'</ul>';
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';
			  
				//---------------------- output world ranking --------------------------
				
				if( $tab == 'world-ranking' ){
					
					echo'<div class="tab-pane active" id="world-ranking">';
					
						//output Tab panes
						
						echo'<div class="tab-content row" style="margin:20px;">';

							// pagination
							
							$page 	= ( !empty($_GET['t']) && is_numeric($_GET['t']) ) ? sanitize_key($_GET['t']) : 1;
							$limit 	= 100;
							$offset = ( ( $page -1 ) * $limit );							
							
							$q = new WP_User_Query( array( 								
								'role' 			=> 'Subscriber',
								'number' 		=> $limit,
								'offset' 		=> $offset,
								'meta_query' 	=> array(
								
									array(
									
										'key' 		=> $this->_base . 'stars',
										'value' 	=> 0,
										'compare' 		=> '>',
									),
									array(
									
										'key' 		=> $this->_base . '_last_seen',
										'value' 	=> 0,
										'compare' 		=> '>',
									)
								),
								'orderby' 		=> 'meta_value_num',
								'order' 		=> 'DESC',
							));

							if(!empty($q->results)){
								
								$pageLinks = paginate_links( array(
								
									'base' 		=> $this->urls->editor . '?' . remove_query_arg('t', $_SERVER['QUERY_STRING']) . '%_%',
									'format' 	=> '&t=%#%', // this defines the query parameter that will be used, in this case "p"
									'prev_text' => __('&laquo; Previous'), // text for previous page
									'next_text' => __('Next &raquo;'), // text for next page
									'total' 	=> ceil( $q->get_total() / $limit), // the total number of pages we have
									'current' 	=> $page, // the current page
									'end_size' 	=> 1,
									'mid_size' 	=> 5,
								));
								
								if($page > 1 ){
									
									echo'<h2>#'. ( $offset + 1 ) .' - #' . $limit * $page . ' Profiles</h2>';
								}
								else{
									
									echo'<h2>TOP '.$limit.' Profiles</h2>';
								}
									
								echo $pageLinks;
								
								echo'<table class="table table-striped table-bordered">';
									
									echo'<thead>';
										echo'<tr>';
											
											echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:6%; text-align:center;">Rank</th>';
											echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;text-align:left;">Profile</th>';
											echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:5%;text-align:center;">Site</th>';
											echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:10%;text-align:center;">Stars</th>';
											
										echo'</tr>';
										
									echo'</thead>';
									
									echo'<tbody>';								
										
										foreach( $q->results as $id => $user ){

											$rank 	= $id + 1 + $offset;
											$stars 	= $user->{$this->_base . 'stars'};
											
											$picture = get_user_meta( $user->ID , $this->_base . 'profile_picture', true );
											
											if( empty($picture) ){
												
												$picture = get_avatar_url( $user->ID );
											}									

											echo'<tr>';
											
												echo'<td style="font-size:16px;font-weight:bold;text-align:center;"># '.$rank.'</td>';
												echo'<td style="font-size:15px;padding:1px;"><a href="' . $this->urls->editor . '?pr='.$user->ID.'">' . '<img src="'.$picture.'" height="35" width="35" /> '. ucfirst( $user->user_nicename ) . '</a></td>';
												echo'<td style="text-align:center;">'.( !empty($user->user_url) ? '<a target="_blank" href="'.$user->user_url . '"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>' : '').'</td>';
												echo'<td style="text-align:center;"><span class="badge" style="font-size:15px;"><span class="glyphicon glyphicon-star" aria-hidden="true"></span> ' . $stars . '</span></td>';
											
											echo'</tr>';										

										}
											
									echo'</tbody>';
									
								echo'</table>';
								
								echo $pageLinks;
							}

						echo'</div>';
						
					echo'</div>';
					
				}
				
				if($this->user->loggedin){
				
					//---------------------- output ranking system --------------------------
					
					if( $tab == 'ranking-rules' ){
						
						echo'<div class="tab-pane active" id="ranking-rules">';
						
							echo'<div class="bs-callout bs-callout-primary">';
							
								echo'<h4>';
								
									echo'Rules & Points';
									
								echo'</h4>';
							
								echo'<p>';
								
									echo 'List of all the actions that can be done to gain stars on the platform.';
								
								echo'</p>';	

							echo'</div>';	
							
							echo'<div class="tab-content row" style="padding:0 15px;">';

								foreach( $this->stars->triggers as $group => $trigger ){
									
									echo'<table class="table table-striped table-bordered">';
										
										echo'<thead>';
											echo'<tr>';
												
												echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;">'.ucfirst($group).'</th>';
												echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:10%;text-align:center;">Stars</th>';
												
											echo'</tr>';
											
										echo'</thead>';
										
										echo'<tbody>';								
											
											foreach( $trigger as $key => $data){
												
												$stars = get_option($this->_base . $key . '_stars');
												
												if( !empty($stars) && $stars!==0 ){
													
													echo'<tr>';
													
														echo'<td>'.ucfirst($data['description']).'</td>';
														echo'<td style="text-align:center;"><span class="badge" style="font-size:15px;">'.( $stars > 0 ? '+ ' . $stars : $stars ).' <span class="glyphicon glyphicon-star" aria-hidden="true"></span></span></td>';
													
													echo'</tr>';
												}									
											}
											
										echo'</tbody>';
										
									echo'</table>';									
								}

							echo'</div>';
							
						echo'</div>';
						
					}
					
					//---------------------- output referral urls --------------------------
					
					if( $tab == 'my-ref-urls' ){
						
						echo'<div class="tab-pane active" id="my-ref-urls">';
						
							echo'<div class="bs-callout bs-callout-primary">';
							
								echo'<h4>';
								
									echo'My Referral Urls';
									
								echo'</h4>';
							
								echo'<p>';
								
									echo 'List of urls to be used to share urls and gain stars';
								
								echo'</p>';	

							echo'</div>';							

							echo'<div class="tab-content row" style="padding:5px;">';

								echo'<div class="col-xs-12 col-sm-6">';
									
									echo'<div class="form-group">';
									
										echo'<h3>My Referral ID</h3>';
									
										echo'<input class="form-control" type="text" value="' . $this->user->refId . '" />';
									
									echo'</div>';
									
									echo'<div class="form-group">';
								
										echo'<h3>My ref link to the main page</h3>';
									
										echo'<input class="form-control" type="text" value="' . $this->urls->editor . '?ri=' . $this->user->refId . '" />';
									
									echo'</div>';
									
									echo'<div class="form-group">';
								
										echo'<h3>My ref link to the login page</h3>';
									
										echo'<input class="form-control" type="text" value="' . $this->urls->login . '?ri=' . $this->user->refId . '" />';
									
									echo'</div>';
									
									echo'<div class="form-group">';
								
										echo'<h3>My ref link to the plans</h3>';
									
										echo'<input class="form-control" type="text" value="' . $this->urls->plans . '?ri=' . $this->user->refId . '" />';
									
									echo'</div>';
									
								echo'</div>';					
							
							echo'</div>';					

							
						echo'</div>';
					}
					
					
					//---------------------- output invite contacts --------------------------
					
					if( $tab == 'invite-contacts' ){
					
						echo'<div class="tab-pane active" id="invite-contacts">';
						
							echo'<div class="bs-callout bs-callout-primary">';
							
								echo'<h4>';
								
									echo'Invite Contacts';
									
								echo'</h4>';
							
								echo'<p>';
								
									echo 'Invite your contacts and gain stars when they login for the first time in a day.';
								
								echo'</p>';	

							echo'</div>';							

							echo'<div class="tab-content row">';
			
								echo'<div class="col-xs-12">';
									
									// get import emails
									
									echo $this->email->get_invitation_form();								
								
								echo'</div>';			

							echo'</div>';
							
						echo'</div>';				
					}
				}
			  
			echo'</div>';
			
		echo'</div>	';

	echo'</div>';//media_library
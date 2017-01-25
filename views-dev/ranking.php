<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
?>

<div id="media_library" style="margin-top:15px;background:#FFF;display:inline-block;width:100%;">

	<div class="col-xs-3 col-sm-2">
	
		<ul class="nav nav-tabs tabs-left">
			
			<li class="gallery_type_title">Ranking System</li>
			
			<li class="active"><a href="#world-ranking" data-toggle="tab">World Ranking</a></li>
			
			<li><a href="#ranking-rules" data-toggle="tab">Rules & Points</a></li>
			
			<li class="gallery_type_title">Referral Tools</li>
			
			<li><a href="#my-ref-urls" data-toggle="tab">My Referral urls</a></li>
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
			<?php

			//---------------------- output world ranking --------------------------
			
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
						'meta_key' 		=> $this->_base . 'stars',
						'orderby' 		=> $this->_base . 'stars',
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
									echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:10%;text-align:center;">Stars</th>';
									
								echo'</tr>';
								
							echo'</thead>';
							
							echo'<tbody>';								
								
									foreach( $q->results as $id => $user ){
										
										$rank = $id + 1 + $offset;
										$stars = $user->{$this->_base . 'stars'};

										echo'<tr>';
										
											echo'<td style="font-size:16px;font-weight:bold;text-align:center;"># '.$rank.'</td>';
											echo'<td style="font-size:15px;"><a href="' . $this->urls->editor . '?pr='.$user->ID.'">' . ucfirst( $user->user_nicename ) . '</a></td>';
											echo'<td style="text-align:center;"><span class="badge" style="font-size:15px;"><span class="glyphicon glyphicon-star" aria-hidden="true"></span> ' . $stars . '</span></td>';
										
										echo'</tr>';										

									}
							echo'</tbody>';
							
						echo'</table>';
						
						echo $pageLinks;
					}

				echo'</div>';
				
			echo'</div>';
			
			//---------------------- output ranking system --------------------------
			
			echo'<div class="tab-pane" id="ranking-rules">';
			
				//output Tab panes
				
				echo'<div class="tab-content row" style="margin:20px;">';

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
			
			//---------------------- output referral urls --------------------------
			
			echo'<div class="tab-pane" id="my-ref-urls">';
			
				//output Tab panes
				
				echo'<div class="tab-content row" style="margin:20px;">';

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
			
			?>
		  
		</div>
		
	</div>	

</div>
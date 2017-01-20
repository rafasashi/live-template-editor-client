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
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
			<?php

			//---------------------- output world ranking --------------------------
			
			echo'<div class="tab-pane active" id="world-ranking">';
			
				//output Tab panes
				
				echo'<div class="tab-content row" style="margin:20px;">';

					echo'<h2>TOP 100 Profiles</h2>';
				
					echo'<table class="table table-striped table-bordered">';
						
						echo'<thead>';
							echo'<tr>';
								
								echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:6%; text-align:center;">Rank</th>';
								echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;text-align:left;">Profile</th>';
								echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:10%;text-align:center;">Stars</th>';
								
							echo'</tr>';
							
						echo'</thead>';
						
						echo'<tbody>';
							
							$user_query = new WP_User_Query( array( 
							
								'role' 			=> 'Subscriber',
								'number' 		=> 100,
								'meta_key' 		=> $this->_base . 'stars',
								'orderby' 		=> $this->_base . 'stars',
								'order' 		=> 'DESC',
							));

							if(!empty($user_query->results)){
							
								foreach( $user_query->results as $id => $user ){
									
									$rank = $id + 1;
									$stars = $user->{$this->_base . 'stars'};

									echo'<tr>';
									
										echo'<td style="font-size:16px;font-weight:bold;text-align:center;"># '.$rank.'</td>';
										echo'<td style="font-size:15px;">' . ucfirst( $user->user_nicename ) . '</td>';
										echo'<td style="text-align:center;"><span class="badge" style="font-size:15px;"><span class="glyphicon glyphicon-star" aria-hidden="true"></span> ' . $stars . '</span></td>';
									
									echo'</tr>';										

								}
							}

						echo'</tbody>';
						
					echo'</table>';					
					
				echo'</div>';
				
			echo'</div>';
			
			//---------------------- output ranking system --------------------------
			
			echo'<div class="tab-pane" id="ranking-rules">';
			
				//output Tab panes
				
				echo'<div class="tab-content row" style="margin:20px;">';

					echo'<table class="table table-striped table-bordered">';
						
						echo'<thead>';
							echo'<tr>';
								
								echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;">Action</th>';
								echo'<th style="background-color:#fff;font-weight: bold;font-size: 15px;width:10%;text-align:center;">Stars</th>';
								
							echo'</tr>';
							
						echo'</thead>';
						
						echo'<tbody>';

							foreach( $this->stars->triggers as $id => $trigger ){
								
								$stars = get_option($this->_base . $id . '_stars');
								
								if( !empty($stars) && $stars!==0 ){
									
									echo'<tr>';
									
										echo'<td>'.ucfirst($trigger['description']).'</td>';
										echo'<td style="text-align:center;"><span class="badge" style="font-size:15px;">'.( $stars > 0 ? '+ ' . $stars : $stars ).' <span class="glyphicon glyphicon-star" aria-hidden="true"></span></span></td>';
									
									echo'</tr>';
								}
							}

						echo'</tbody>';
						
					echo'</table>';
					
				echo'</div>';
				
			echo'</div>';
			
			?>
		  
		</div>
		
	</div>	

</div>

<script>

	;(function($){
		
		$(document).ready(function(){

			// submit forms
			
			$( "button" ).click(function() {
				
				this.closest( "form" ).submit();
			});
		
		});
		
	})(jQuery);

</script>
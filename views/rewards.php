<?php 

	if(isset($_SESSION['message'])){ 
	
		//output message
	
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
	
	$currentTab = ( !empty($_GET['app']) ? $_GET['app'] : 'unlock' );
	
	// ------------- output panel --------------------
	
	echo'<div id="media_library" class="wrapper">';

		echo '<div id="sidebar">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Rewards</li>';
				
				echo'<li'.( $currentTab == 'unlock' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?app=unlock&output='.$output.'">Unlock Free</a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'unlock' ){

				echo'<div class="tab-content">';
				
					echo'<div id="unlock">';

						echo'<div class="bs-callout bs-callout-primary">';

							echo '<h4>Unlock Free</h4>';

							echo '<p>Help us promoting the tool and unlock the Demo output for free during 1 hour</p>';
						
						echo'</div>';

						echo'<div class="row">';

							if( $this->user->ID  > 0 ){
								
								//----------------unlock via twitter -----------------
								
								$app_slug 	= 'twitter';
								$app_title 	= 'Twitter';
								
								echo'<div class="col-xs-12">';
								
									echo'<h3>'.$app_title.'</h3>';
								
								echo'</div>';
		
								echo'<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
								echo'<div class="panel panel-default" style="background:#efefef;">';
									
									echo '<div class="panel-heading"><b>My '.$app_title.' accounts</b></div>';
									
									foreach( $this->user->apps as $user_app ){

										if(strpos($user_app->post_name , $app_slug . '-')===0){
											
											echo '<span style="width:100%;text-align:left;" class="btn btn-md btn-info">';
											
												echo '<span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> ';
											
												echo ucfirst($user_app->post_title);
											
											echo '</span>';
										}
									}
									
									echo '<a target="'.$target.'" href="'.$this->apps->getAppUrl($app_slug,'connect','unlock').'" style="width:100%;text-align:left;" class="btn btn-md btn-default add_account"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add ' . $app_title . ' account</a>';

								echo'</div>';
								echo'</div>';

								echo'<div class="col-xs-12 col-sm-6">';
								
									foreach( $this->user->apps as $user_app ){

										if(strpos($user_app->post_name , $app_slug . '-')===0){
											
											echo'<div class="panel panel-default">';
												
												echo'<div class="panel-body">';
												
													echo '<h4>Tweet via ' . ucfirst($user_app->post_title) . '</h4>';
													
													echo $this->admin->display_field( array(
													
														'type'				=> 'textarea',
														'id'				=> 'unlock-free-' . $user_app->post_name,
														'style'				=> 'height:70px;width:100%;',
														'description'		=> 'Tweet to unlock the demo output during 1 hour',
														'default'			=> get_option( $this->_base . 'twt_unlock_tweet' ),
														'placeholder'		=> '',
														'disabled'			=> true,

													), false, false );
													
													echo'<a target="_parent" href="' . $this->urls->editor . '?app=twitter&action=unlockFree&id='.$user_app->ID.'&output='.$output.'&ref='.( !empty($_GET['ref']) ? $_GET['ref'] : $this->urls->current ).'" class="btn btn-xs btn-primary pull-right">';

														echo 'Tweet';
													
													echo'</a>';
													
												echo'</div>';
												
											echo'</div>';
										}
									}									
								
								echo'</div>';
							}								
								
						echo'</div>';
						
					echo'</div>';
					
				echo'</div>';				
			}

		echo'</div>	';

	echo'</div>';
	
	?>
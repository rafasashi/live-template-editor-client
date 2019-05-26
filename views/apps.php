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
	
	$currentApp = '';
	
	if( !empty($_GET['app']) ){
		
		$currentApp = $_GET['app'];
	}
	elseif( !empty($_SESSION['app']) ){
		
		$currentApp = $_SESSION['app'];
	}
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'apps' );
	
	// ------------- output panel --------------------
	
	echo'<div id="media_library" class="wrapper">';
		
		echo $this->dashboard->get_sidebar($currentTab,$output);

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
			
			if( $currentTab == 'apps' ){
				
				echo'<div class="tab-content">';
			
					$app_types = $this->get_app_types();
			
					//------------------ get items ------------
					
					$items = [];
					
					if( !empty($this->apps->list) ){
					
						foreach( $this->apps->list as $app ){ 
							
							$connect_url = $this->urls->apps . '?app='.$app->slug . '&action=connect';
							
							//get item
							
							$item='';
							
							$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$app->slug) ) . '" id="post-' . $app->slug . '">';
								
								$item.='<div class="panel panel-default">';
									
									/*
									$item.='<div class="panel-heading">';

										$item.='<b>' . $app->name . '</b>';
										
									$item.='</div>';
									*/
									
									$item.='<div class="thumb_wrapper" style="background-image:url(' . $app->thumbnail . ');background-size:cover;background-repeat:no-repeat;background-color:#fff;background-position:center center;height:150px;"></div>';
									
									$item.='<div class="panel-body">';
										
										$item.='<div class="col-xs-12">';

											$item.='<b>' . $app->name . '</b>';
										
										$item.='</div>';									
										
										$item.='<div class="col-xs-7 text-left">';
										
											$a 	= 0;
											$c	= '<p>';
											
											foreach( $this->user->apps as $user_app){
												
												if(strpos($user_app->post_name, $app->slug . '-')===0){
													
													$c .= str_replace($app->slug . ' - ','',$user_app->post_title) . '</br>'. PHP_EOL;

													$a++;
												}
											}
											
											$c .= '</p>';
											
											if($a>0){
												
												$item.='<a href="#" class="badge" data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" title="' . ucfirst($app->name) .' accounts" data-content="'.$c.'">'.$a.' <span class="glyphicon glyphicon-link" aria-hidden="true"></span></a>';	
											}
											else{
												
												$item.='<span class="badge">'.$a.' <span class="glyphicon glyphicon-link" aria-hidden="true"></span></span>';
											}
											
										$item.='</div>';
										$item.='<div class="col-xs-5 text-right">';
											$item.='<a class="btn-sm btn-primary insert_media" href="'.$connect_url.'">Connect</a>';
										$item.='</div>';
									$item.='</div>'; //panel-body
								$item.='</div>';
							$item.='</div>';
							//merge item
							$items[$app->slug]=$item;
						}
					}
					
					// ------------------ get all apps ----------------
					
					foreach( $app_types as $app_type => $a ){
						
						foreach($items as $slug => $item){									
						
							foreach( $this->apps->list as $app ){ 

								if( in_array($app_type,$app->types)){
									
									$app_types[$app_type][$app->slug] = $app;
								}
							}
						}						
					}
					
					// get message
					
					$message = '';

					if(!empty($this->message)){
						
						$message = $this->message;
					}
					elseif( !empty($this->apps->list) ){
							
						foreach( $this->apps->list as $app ){ 
							
							if(!empty($this->apps->{$app->slug}->message)){
								
								$message = $this->apps->{$app->slug}->message;
								break;
							}							
						}
					}					

					
					//---------------------- output default apps --------------------------
					
					echo'<div id="app-library">';
					
						if(!empty($message)){
							
							echo $message;
							
						}
						else{
							
							echo'<ul class="nav nav-pills" role="tablist">';
							
							$active=' class="active"';
							
							foreach($app_types as $app_type => $apps){
								
								if($app_type != ''){
									
									echo'<li role="presentation"'.$active.'><a href="#'.$app_type.'" aria-controls="'.$app_type.'" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$app_type)).'<span class="badge">'.count($app_types[$app_type]).'</span></a></li>';
								}
								
								$active='';
							}
							
							echo'</ul>';
							
							//output Tab panes
							
							echo'<div class="tab-content" style="margin-top:20px;">';
								
								$active=' active';
								
								foreach( $app_types as $app_type => $apps ){
									
									echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$app_type.'">';
										
										foreach($apps as $slug => $app){									
										
											echo $items[$slug];
										}
										
									echo'</div>';
									
									$active='';
								}
								
							echo'</div>';					
						}
						
					echo'</div>';
					
				echo'</div>';
			}
			elseif( $currentTab == 'embedded' ){

				echo'<div class="tab-content">';
				
					echo'<div id="embedded">';

						echo'<div class="bs-callout bs-callout-primary">';

							echo '<h4>Embedded Plugin</h4>';

							echo '<p>Setup your Wordpress Embedded Plugin</p>';
						
						echo'</div>';

						echo'<div class="col-xs-12 col-sm-6">';
							
							echo'<div class="form-group">';
							
								echo'<h3>Customer Key</h3>';
								
								echo'<input class="form-control" type="text" value="' . get_option($this->_base . 'embedded_prefix', $this->_base) . $this->ltple_encrypt_str( $this->user->user_email ) . '_' . $this->ltple_encrypt_str( $this->urls->api_embedded, $this->_base ) . '">';
							
							echo'</div>';
						echo'</div>';
						
					echo'</div>';
					
				echo'</div>';
			}

		echo'</div>	';

	echo'</div>';
	
	?>
<?php 

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab('accounts');

$output		= $ltple->inWidget ? 'widget' : 'default';
$target		= $ltple->inWidget ? '_parent' : '_self';

// get current tab

if( !empty($_GET['app']) ){
	
	$currentApp = sanitize_title($_GET['app']);
}
else{
	
	$currentApp = $ltple->session->get_user_data('app');
}

// ------------- output panel --------------------

echo'<div id="media_library" class="wrapper">';
	
	echo'<div id="sidebar">';
		
		echo'<div class="gallery_type_title gallery_head">Apps & Services</div>';	
			
		echo'<ul class="nav nav-tabs tabs-left">';

			//echo'<li class="gallery_type_title">Accounts</li>';
			
			echo'<li '. ( $currentTab == 'accounts' ? 'class="active"' : '' ) . '><a href="' . $ltple->urls->apps . '?tab=accounts"><span class="fa fa-exchange-alt"></span> Applications</a></li>';
			
			do_action('ltple_connected_accounts_sidebar',$currentTab);
			
		echo'</ul>';
		
	echo'</div>';
		
	echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
		
		if( $currentTab == 'accounts' ){
			
			echo'<div class="tab-content">';
		
				$app_types = $ltple->get_app_types();
		
				//------------------ get items ------------
				
				$items = [];
				
				if( !empty($ltple->apps->list) ){
				
					foreach( $ltple->apps->list as $app ){ 
						
						$connect_url = $ltple->urls->apps . '?app='.$app->slug . '&action=connect';
						
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
										
										if( !empty($ltple->user->apps) ){
											
											foreach( $ltple->user->apps as $user_app){
												
												if(strpos($user_app->post_name, $app->slug . '-')===0){
													
													$c .= str_replace($app->slug . ' - ','',$user_app->post_title) . '</br>'. PHP_EOL;

													$a++;
												}
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
										$item.='<a class="btn btn-xs btn-success insert_media" href="'.$connect_url.'" target="'.$target.'">Add</a>';
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
					
						foreach( $ltple->apps->list as $app ){ 

							if( in_array($app_type,$app->types)){
								
								$app_types[$app_type][$app->slug] = $app;
							}
						}
					}						
				}
				
				// get message
				
				$message = '';
				
				if( !empty($ltple->message) ){
					
					$message = $ltple->message;
				}
				elseif( !empty($ltple->apps->list) ){
							
					foreach( $ltple->apps->list as $app ){ 
						
						if(!empty($ltple->apps->appClasses[$app->slug]->message)){
							
							$message = $ltple->apps->appClasses[$app->slug]->message;
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
						
						echo'<ul class="nav nav-pills nav-resizable" role="tablist">';
						
							if( $ltple->inWidget ){
								
								echo'<li>';
								
									echo $ltple->get_collapse_button();
									
								echo'</li>';
							}
						
						$active=' class="active"';
						
						foreach($app_types as $app_type => $apps){
							
							if( $app_type != '' ){
								
								if( $count = count($app_types[$app_type]) ){
								
									echo'<li role="presentation"'.$active.'><a href="#'.$app_type.'" aria-controls="'.$app_type.'" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$app_type)).'<span class="badge">'.$count.'</span></a></li>';
									
									$active='';
								}
							}
						}
						
						echo'</ul>';
						
						//output Tab panes
						
						echo'<div class="tab-content" style="margin-top:20px;">';
							
							$active=' active';
							
							foreach( $app_types as $app_type => $apps ){
								
								if( $count = count($app_types[$app_type]) ){
								
									echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$app_type.'">';
										
										foreach($apps as $slug => $app){									
										
											echo $items[$slug];
										}
										
									echo'</div>';
									
									$active='';
								}
							}
							
						echo'</div>';					
					}
					
				echo'</div>';
				
			echo'</div>';
		}
		else{
			
			do_action( 'ltple_apps_' . $currentTab );			
		}

	echo'</div>	';

echo'</div>';

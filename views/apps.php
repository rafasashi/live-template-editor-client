<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	//------------------ get app types ------------
	
	if($this->user->is_admin && !empty($_GET['id']) && is_numeric($_GET['id']) ){

		$user_id = $_GET['id'];
	}
	else{
		
		$user_id = $this->user->ID;
	}
	
	$app_types 	= $this->get_app_types();
	$leads 		= $this->leads->get_leads($user_id);
?>

<div id="media_library">

	<div class="col-xs-3 col-sm-2">
	
		<ul class="nav nav-tabs tabs-left">
			
			<li class="gallery_type_title">Applications</li>
			
			<li class="active"><a href="#app-library" data-toggle="tab">Connected Apps</a></li>

			<?php if($this->user->is_admin){ ?>
			
				<li><a href="#automation" data-toggle="tab">Automation</a></li>
				
			<?php } ?>
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
			<?php
			
			//------------------ get items ------------
			
			$items = [];
			
			if( !empty($this->apps->appList) ){
			
				foreach( $this->apps->appList as $app ){ 
					
					$connect_url = $this->urls->editor . '?app='.$app->slug.'&action=connect';
					
					//get item
					
					$item='';
					
					$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4 col-lg-3",$app->slug) ) . '" id="post-' . $app->slug . '">';
						
						$item.='<div class="panel panel-default" style="border-left:1px solid #DDD;">';
							
							$item.='<div class="panel-heading">';

								$item.='<b>' . $app->name . '</b>';
								
							$item.='</div>';

							$item.='<div class="panel-body">';
								
								$item.='<div class="thumb_wrapper" style="height: 120px;margin-bottom: 20px;">';
									
									$item.= '<img class="lazy" data-original="'.$app->thumbnail.'" />';
								
								$item.='</div>'; //thumb_wrapper
								
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
				
					foreach( $this->apps->appList as $app ){ 

						if( in_array($app_type,$app->types)){
							
							$app_types[$app_type][$app->slug] = $app;
						}
					}
				}						
			}
			
			//---------------------- output default apps --------------------------
			
			echo'<div class="tab-pane active" id="app-library">';
			
				if(!empty($this->message)){
					
					echo $this->message;
					
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
					
					echo'<div class="tab-content row" style="margin-top:20px;">';
						
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
			
			
			//---------------------- output automation --------------------------
			
			if($this->user->is_admin){
			
			echo'<div class="tab-pane" id="automation">';

				if(in_array_field( 'twitter', 'slug', $this->apps->appList )){
					
					echo '<h3>Twitter actions</h3>';
					
					echo 'An easy way to grow stars, engagements, quality followers, traffic and influence.';

					echo '<div id="toolbar" class="btn-group">';
						echo '<button type="button" class="btn btn-default">';
							echo '<i class="glyphicon glyphicon-plus"></i>';
						echo '</button>';
						echo '<button type="button" class="btn btn-default">';
							echo '<i class="glyphicon glyphicon-heart"></i>';
						echo '</button>';
						echo '<button type="button" class="btn btn-default">';
							echo '<i class="glyphicon glyphicon-trash"></i>';
						echo '</button>';
					echo '</div>';				
					
					echo '<table id="eventsTable"';
						echo 'data-toggle="table" ';
						//echo 'data-height="400" ';
						echo 'data-url="' . $this->api->get_url('leads/list',$user_id) . '" ';
						echo 'data-pagination="true" ';
						echo 'data-search="true" ';
						echo 'data-show-header="true" ';
						//echo 'data-side-pagination="server" ';
						echo 'data-page-size="20" ';	
						echo 'data-page-list="[20, 50, 100]" ';					
						echo 'data-show-refresh="false" ';
						echo 'data-show-toggle="true" ';
						echo 'data-show-columns="true" ';
						echo 'data-toolbar="#toolbar" ';
						echo 'data-sort-order="desc" ';
						echo 'data-sort-name="description" ';
					echo '>';
						echo '<thead>';
						echo '<tr>';
						
							echo '<th ';
								echo 'data-field="state" ';
								echo 'data-checkbox="true" ';
							echo '>';
							echo '</th>';
							
							echo '<th ';
								echo 'data-field="img" ';
								echo 'data-sortable="false" ';
							echo '>';
								echo '';
							echo '</th>';
							
							echo '<th ';
								echo 'data-field="leadTwtName" ';
								echo 'data-sortable="true" ';
							echo '>';
								echo 'Name';
							echo '</th>';
							
							echo '<th ';
								echo 'data-field="leadTwtFollowers" ';
								echo 'data-sortable="true" ';
							echo '>';
								echo 'Followers';
							echo '</th>';

							echo '<th ';
								echo 'data-field="leadDescription" ';
								echo 'data-sortable="true" ';
							echo '>';
								echo 'Description';
							echo '</th>';

						echo '</tr>';
						echo '</thead>';
					echo '</table>';
				}
			
			echo'</div>';
			}
			
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
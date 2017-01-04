<?php 

	if(isset($_SESSION['message'])){ 
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}
	//------------------ get app types ------------
	
	$app_types = $this->get_app_types();

?>

<div id="media_library" style="margin-top:15px;background:#FFF;display:inline-block;width:100%;">

	<div class="col-xs-3 col-sm-2">
	
		<ul class="nav nav-tabs tabs-left">
			
			<li class="gallery_type_title">Applications</li>
			
			<li class="active"><a href="#app-library" data-toggle="tab">Connected Apps</a></li>
			
		</ul>
	</div>

	<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">
		
		<div class="tab-content">
		  
			<?php
			
			//------------------ get items ------------
			
			$items = [];
			
			$home_url = home_url();
			
			foreach( $this->apps->appType as $app ){ 
				
				$connect_url = $home_url.'/editor/?app='.$app->slug.'&action=connect';
				
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
										
										$c .= '+ ' . str_replace($app->slug . ' - ','',$user_app->post_title) . '</br>'. PHP_EOL;

										$a++;
									}
								}
								
								$c .= '</p>';
								
								if($a>0){
									
									$item.='<a href="#" class="badge" data-html="true" data-toggle="popover" data-placement="top" title="' . ucfirst($app->name) .' accounts" data-content="'.$c.'">'.$a.' <span class="glyphicon glyphicon-link" aria-hidden="true"></span></a>';	
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
			
			//---------------------- output default apps --------------------------
			
			echo'<div class="tab-pane active" id="app-library">';
			
				if(!empty($this->message)){
					
					echo $this->message;
					
				}
				else{
					
					echo'<ul class="nav nav-pills" role="tablist">';
					
					$active=' class="active"';
					
					foreach($app_types as $app_type){
						
						if($app_type != ''){
							
							echo'<li role="presentation"'.$active.'><a href="#'.$app_type.'" aria-controls="'.$app_type.'" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$app_type)).'</a></li>';
						}
						$active='';
					}
					
					echo'</ul>';
					
					//output Tab panes
					
					echo'<div class="tab-content row" style="margin-top:20px;">';
						
						$active=' active';
						
						foreach( $app_types as $app_type ){
							
							echo'<div role="tabpanel" class="tab-pane'.$active.'" id="'.$app_type.'">';
								
								foreach($items as $slug => $item){									
								
									foreach( $this->apps->appType as $term ){ 
									
										if( $term->slug == $slug ){		
											
											$app = $term;
											break;
										}
									}		
									
									if(in_array($app_type,$app->types)){
										
										echo $item;
									}
								}
								
							echo'</div>';
							
							$active='';
						}
					echo'</div>';					
				}				
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
<?php 

	if(isset($_SESSION['message'])){ 
	
		//output message
	
		echo $_SESSION['message'];
		
		//reset message
		
		$_SESSION['message'] ='';
	}

	// get current tab
	
	$currentTab = 'apps';
	
	if( in_array($_GET['app'],['members','leads']) ){
		
		$currentTab = $_GET['app'];
	}

	// ------------- output panel --------------------
	
	echo'<div id="media_library">';

		echo'<div class="col-xs-3 col-sm-2">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Applications</li>';
				
				echo'<li'.( $currentTab == 'apps' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?app">Connected Apps</a></li>';

				echo'<li class="gallery_type_title">My Community</li>';
				
				echo'<li'.( $currentTab == 'members' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?app=members">Top Members <span class="label label-success pull-right"> pro </span></a></li>';
				
				echo'<li'.( $currentTab == 'leads' ? ' class="active"' : '' ).'><a href="'.$this->urls->editor . '?app=leads">Suggestions</a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10" style="border-left: 1px solid #ddd;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'apps' ){
			
					$app_types = $this->get_app_types();
			
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
					
				}
				elseif( $currentTab == 'members' ){

					//---------------------- output members --------------------------
					
					echo'<div class="tab-pane active" id="members">';

						if(in_array_field( 'twitter', 'slug', $this->apps->appList )){
							
							echo'<div class="bs-callout bs-callout-primary">';

								echo '<h4>Top 1K Members</h4>';

								echo '<p>An easy way to discover and engage your most valuable followers and start new business conversations with them.</p>';
							
							echo'</div>';
							
							if( $this->user->plan["info"]["total_price_amount"] > 0 ){
								
								$api_url = $this->api->get_url('leads/list',$this->user->ID);
								
								$this->api->get_table($api_url, true, true);
							}
							else{
								
								echo '<div class="modal-body">'.PHP_EOL;
							
									echo  '<div class="alert alert-info">';
										
										echo  '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> You must be a <b>PRO subscriber</b> to access this feature...';
										
										echo  '<div class="pull-right">';

											echo  '<a class="btn-sm btn-success" href="' . $this->urls->plans . '" target="_parent">Subscribe now</a>';
											
										echo  '</div>';
										
									echo  '</div>';	

								echo '</div>'.PHP_EOL;
							}
						}
						else{
							
							echo '<div class="modal-body">'.PHP_EOL;
						
								echo  '<div class="alert alert-info">';
									
									echo  '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> You must connect a <b>Twitter account</b> to access this feature...';
									
									echo  '<div class="pull-right">';

										echo  '<a class="btn-sm btn-success" href="' . $this->urls->editor . '?app" target="_parent">Connect now</a>';
										
									echo  '</div>';
									
								echo  '</div>';	

							echo '</div>'.PHP_EOL;							
						}
					
					echo'</div>';
				}
				elseif( $currentTab == 'leads' ){

					echo'<div class="tab-pane active" id="leads">';

						echo'<div class="bs-callout bs-callout-primary">';

							echo '<h4>Suggested accounts</h4>';

							echo '<p>Discover and engage the biggest influencers and start new business conversations with them.</p>';
						
						echo'</div>';

						$api_url = $this->api->get_url('leads/list',-1);
							
						$this->api->get_table($api_url, false, true);
					
					echo'</div>';				
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

				// submit forms
				
				$( ":not(#toolbar) > [button]" ).click(function() {
					
					this.closest( "form" ).submit();
				});
				
				// set bootstrap-table export
				
				if( $('#table').length  > 0 && $('#export').length  > 0 ){
				
					var $table 		= $('#table');
					var checkedRows = [];
					
					// store checked row 
					
					$table.on('check.bs.table', function (e, row) {
						
						checkedRows.push({id: row.id});
					});

					// unset unchecked row 
					
					$table.on('uncheck.bs.table', function (e, row) {
						
						$.each(checkedRows, function(index, value) {
							
							if (value.id === row.id) {
								checkedRows.splice(index,1);
							}
						});
					});
					
					$('#export').click(function () {
						
						$table.tableExport({
							type: 'csv',
							escape: false
						});
					});
					
					$('#trash').click(function () {
						
						// loading icon
						
						var $icon = $(this).find("i");
						var currentClasses = $icon.attr('class');
						
						$icon.attr('class', 'fa fa fa-circle-o-notch fa-spin fa-spin');
						
						$.post( "<?php echo $this->api->get_url('leads/list',$this->user->ID); ?>", { "rows" : checkedRows } )
						 .done(function( data ) {

							$icon.attr('class', currentClasses);								
							$table.bootstrapTable("load", data);
						});
					});
				}
			});
			
		})(jQuery);

	</script>
<?php

$ltple = LTPLE_Client::instance();

if( !$ltple->inWidget ){
	
	$url = add_query_arg( array(
	
		'output' => 'widget'
		
	),$ltple->urls->current);
	
	get_header();
	
		include( $ltple->views . '/navbar.php' );
		
		echo '<div style="min-height:calc( 100vh - 145px );overflow:hidden;">';
		
			echo'<iframe data-src="' . $url . '" style="width:100%;border:0;height:calc(100vh - 153px);overflow:hidden;"></iframe>';
		
		echo '</div>';
		
	get_footer();
}
else{
	
	$layer_plan = $ltple->plan->get_layer_plan( $ltple->layer->id, 'min' );
	
	if( !isset($_GET['period_refreshed']) && $layer_plan['amount'] > 0 && $ltple->user->remaining_days < 0 ){
		
		// refresh user period
		
		$ltple->users->update_periods($ltple->user->ID);
		
		// redirect url
		
		$url = add_query_arg( array(
			
			'period_refreshed' => '',
			
		),$ltple->urls->current);
		
		wp_redirect($url);
		exit;
	}
	else{
		
		$layer_type = $ltple->layer->get_layer_type($ltple->layer->id);
		
		$user_plan 	= $ltple->plan->get_user_plan_info($ltple->user->ID);

		$total_storage 	= isset($user_plan['info']['total_storage'][$layer_type->name]) ? $user_plan['info']['total_storage'][$layer_type->name] : 0;
		
		$plan_usage = $ltple->plan->get_user_plan_usage( $ltple->user->ID );
		
		if( $ltple->layer->is_editable_output($layer_type->output) ){
			
			// get download url
			
			$download_url = add_query_arg( array(
			
				'quick' 	=> '',
				
			), remove_query_arg('output',$ltple->urls->current) );
			
			// get download button
			
			$quick_start = '';
			
			if( $layer_type->output == 'image' ){
				
				$quick_start = '<a target="_parent" href="'.$download_url.'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Edit image ( without saving )</a>';
			}
			elseif( $layer_type->output == 'vector' ){
				
				$quick_start = '<a target="_parent" href="'.$download_url.'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Edit vector ( without saving )</a>';
			}
			elseif( $layer_type->output == 'inline-css' || $ltple->layer->layerOutput == 'external-css' ){
				
				$quick_start = '<a target="_parent" href="'.$download_url.'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Get the code ( without hosting )</a>';				
			}
			elseif( $layer_type->output == 'web-app' ){
				
				$quick_start = '<a target="_parent" href="'.$download_url.'" class="btn btn-lg btn-primary" style="margin: 15px 15px 0px 15px;">Launch the app</a>';
			}
		}
		else{
			
			$quick_start = apply_filters('ltple_quick_start_action','');
		}
		
		get_header();		
		
		echo '<div style="min-height:calc( 100vh - ' . ( $ltple->inWidget ? 0 : 145 ) . 'px );overflow:hidden;">';
			
			echo '<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
				
				echo '<h2 class="pull-left">Start a new project</h2>';
					
				if( $total_storage > 0 ){
				
					echo '<a class="pull-right" target="_parent" href="' . $ltple->urls->account . '?tab=billing-info"><span class="label label-default" style="font-size:18px;"> ' . ( !empty($plan_usage[$layer_type->name]) ? $plan_usage[$layer_type->name] : 0 ) . ' / ' . $total_storage . ' </span></a>';
				}
				
				echo '<hr class="clearfix">';
				
				if( !$ltple->layer->is_media && ( $layer_plan['amount'] === floatval(0) || $ltple->user->remaining_days > 0 ) ){
					
					if( $ltple->plan->remaining_storage_amount($ltple->layer->id) > 0 ){
						
						// get editor url
						
						$editor_url = remove_query_arg('output',$this->parent->urls->current);			
						
						echo'<form target="_parent" class="col-xs-6" action="' . $editor_url . '" id="savePostForm" method="post">';
							
							echo'<div class="input-group">';					
								
								echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-lg required" placeholder="Project Title">';
								echo'<input type="hidden" name="postContent" id="postContent" value="">';

								/*
								echo'<input type="hidden" name="postCss" id="postCss" value="">';
								echo'<input type="hidden" name="postJs" id="postJs" value="">';
								echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
								*/
								
								wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

								echo'<input type="hidden" name="submitted" id="submitted" value="true">';
								
								echo'<span class="input-group-btn">';

									echo'<input type="hidden" name="postAction" id="postAction" value="save">';
										
									echo'<input formtarget="_parent" class="btn btn-lg btn-primary" type="submit" id="saveBtn" style="padding:11px 15px;height:42px;" value="Start" />';
								
								echo'</span>';
								
							echo'</div>';
							
						echo'</form>';
						
						if( !empty($quick_start) ){
						
							echo'<div style="font-size:18px;width:100%;display:inline-block;padding:35px 20px 20px 20px;">OR</div>';
						}
					}
					elseif( $total_storage > 0 ){
						
						echo'<div class="alert alert-warning">';
							
							echo'You can\'t save more projects from the <b>' . $layer_type->name . '</b> gallery with the current plan. Delete an old project or upgrade to increase your storage space.';
					
						echo'</div>';				
					}
				}
				
				if( !empty($quick_start) ){
					
					echo $quick_start;
				}

			echo'</div>';
			
			if( $projects = $ltple->layer->get_user_projects($ltple->user->ID,$layer_type) ){

				echo'<div class="col-xs-12 col-sm-12 col-lg-6" style="padding:20px;">';
					
					echo'<h2>Load a similar project <a target="_parent" href="' . $ltple->urls->dashboard . '?list=' . $layer_type->storage . '"><span class="pull-right label" style="font-size:14px;color:#cacaca;padding:10px;">see all</span></a></h2>';
					
					echo'<hr>';
					
					echo'<div style="height:calc( 100vh - ' . ( $ltple->inWidget ? 115 : 260 ) . 'px );overflow:auto;">';
					
						foreach( $projects as $project ){
							
							echo'<div style="margin: 5px 0;display: inline-block;width: 100%;">';
							
								echo'<div class="col-xs-6">';
									
									echo $project->post_title;
							
								echo'</div>';
							
								echo'<div class="col-xs-6 text-right">';
								
									echo $ltple->layer->get_action_buttons($project,$layer_type,'_parent');
									
								echo'</div>';
								
							echo'</div>';
						}
					
					echo'</div>';
						
				echo'</div>';	
			}
			
		echo'</div>';
		
		get_footer();
	}
}
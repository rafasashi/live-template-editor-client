<?php

	$is_embedded = ( (isset($_GET['output']) && $_GET['output'] == 'embedded' && !empty($this->layer->embedded)) ? true : false );

	$output = ( !empty($_GET['output']) ? '&output='. sanitize_text_field($_GET['output']) : '' );
	
	if( !empty($_SESSION['message']) ){
		
		echo $_SESSION['message'];
		
		$_SESSION['message'] = '';
	}
	elseif( ( !$this->user->is_admin || !isset($_GET['edit']) ) && $this->layer->type == 'cb-default-layer' && $this->user->plan["info"]["total_price_amount"] > 0 ){

		$has_storage = ( ( !isset($this->user->plan['info']['total_storage']) || $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates']) ? false : true );

		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<h2>Start a new project</h2>';

			echo'<hr></hr>';
			
			if($has_storage){
				
				// get editor url
				
				$editor_url = $this->urls->editor . '?uri=' . $this->layer->id . $output;			
				
				echo'<form class="col-xs-6" target="_parent" action="' . $editor_url . '" id="savePostForm" method="post">';
					
					echo'<div class="input-group">';					
						
						echo'<input type="text" name="postTitle" id="postTitle" value="" class="form-control input-lg required" placeholder="Template Title">';
						echo'<input type="hidden" name="postContent" id="postContent" value="">';
						
						if( $is_embedded ){
							
							echo'<input type="hidden" name="postEmbedded" id="postEmbedded" value="' . $this->layer->embedded['url'] . '">';
						}
						
						/*
						echo'<input type="hidden" name="postCss" id="postCss" value="">';
						echo'<input type="hidden" name="postJs" id="postJs" value="">';
						echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
						*/
						
						wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );

						echo'<input type="hidden" name="submitted" id="submitted" value="true">';
						
						echo'<span class="input-group-btn">';

							echo'<input type="hidden" name="postAction" id="postAction" value="save">';
								
							echo'<input class="btn btn-lg btn-primary" type="submit" id="saveBtn" style="border-radius: 0 3px 3px 0;" value="Start" />';
						
						echo'</span>';
						
					echo'</div>';
				echo'</form>';
			}
			else{
				
				echo'<div class="alert alert-warning">';
					
					echo'You need to free up storage space first... ( ' . $this->user->layerCount . '/' . $this->user->plan['info']['total_storage']['templates'] . ' )';
			
				echo'</div>';
				
				foreach($this->user->layers as $i => $layer) {
					
					echo '<hr></hr>';
					
					echo'<div style="display:block;">';
					
						echo'<div class="col-xs-9">';
							
							echo '<a target="_blank" href="' . $this->urls->editor . '?uri=' . $layer->ID . '">' . ( $i + 1 ) . ' - ' . ucfirst($layer->post_title) . '</a>';
						
						echo'</div>';
						
						echo'<div class="col-xs-3 text-right">';
							
							echo '<a class="btn-xs btn-danger" href="' . $this->urls->editor . '?uri=' . $layer->ID . '&postAction=delete" style="padding: 5px 10px;font-weight: bold;">x</a>';
						
						echo'</div>';
						
					echo'</div>';
					
					echo '<div class="clearfix"></div>';
				}			
			}	

		echo'</div>';
	}
	elseif( $this->layer->type == 'user-layer' && !$this->user->plan["info"]["total_price_amount"] > 0 ){
		
		echo'<div class="col-xs-12 col-sm-12 col-lg-8" style="padding:20px;min-height:500px;">';
			
			echo '<div class="alert alert-warning">You need a paid plan to edit this template...</div>';

		echo'</div>';
	}
	else{
		
		$iframe_url = $this->urls->editor . '?uri=' . $this->layer->id . '&lk=' . md5( 'layer' . $this->layer->id . $this->_time ) . '&_=' . $this->_time;

		if( $is_embedded ){
			
			$iframe_url .= '&le=' . urlencode($_GET['le']);
		} 	

		echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';

		echo'<iframe id="editorIframe" src="' . $iframe_url .'" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1300px;overflow: hidden;"></iframe>';
	}
<?php
	
	$ltple = LTPLE_Client::instance();
	
	$options = '';
	
	if( !empty($ltple->plan->options) ){
		
		$options = implode('|',$ltple->plan->options);
	}
	elseif(!empty($layer_range)){
		
		$options = $layer_range;
	}

	$checkout_url = add_query_arg( array(
		
		'output' 	=> 'widget',
		'options' 	=> $options,
	
	), $ltple->urls->checkout );
	
	echo '<div class="modal fade" id="upgrade_plan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
		
		echo '<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
			
			echo '<div class="modal-content">'.PHP_EOL;
				
				echo '<div class="modal-header" style="background:#fff !important;">'.PHP_EOL;
					
					echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#345774;">&times;</span></button>'.PHP_EOL;
					
					echo '<h4 class="modal-title text-left" id="myModalLabel" style="color:#345774;">Upgrade your plan</h4>'.PHP_EOL;
				
				echo '</div>'.PHP_EOL;
			  
				echo '<div class="modal-body text-center">'.PHP_EOL;
					
					echo '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $ltple->assets_url . '/loader.gif\');"></div>';

					echo '<iframe data-src="' . $checkout_url . '" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 90px);overflow: hidden;"></iframe>';						
					
				echo '</div>'.PHP_EOL;

			echo '</div>'.PHP_EOL;
			
		echo '</div>'.PHP_EOL;
		
	echo '</div>'.PHP_EOL;
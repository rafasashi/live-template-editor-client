<?php

	echo '<div class="modal fade" id="upgrade_plan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
		
		echo '<div class="modal-dialog modal-lg" role="document" style="width:500px !important;">'.PHP_EOL;
			
			echo '<div class="modal-content" style="height:270px !important;">'.PHP_EOL;
				
				echo '<div class="modal-header">'.PHP_EOL;
					
					echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
					
					echo '<h4 class="modal-title text-left" id="myModalLabel">This template is not included in your plan</h4>'.PHP_EOL;
				
				echo '</div>'.PHP_EOL;
			  
				echo '<div class="modal-body text-center">'.PHP_EOL;

					echo '<div style="display:block;margin:30px;">';
					
						echo '<a style="display:block;width:100%;" class="btn-lg btn-success" href="' . $this->parent->urls->plans . '" target="_parent" title="View plans">View plans</a>';
					
					echo '</div>';
					
					echo '<div style="display:block;margin:30px;">';
					
						echo '<a style="display:block;width:100%;" class="btn-lg btn-info" href="' . site_url() . '/contact/' . '" target="_parent" title="Contact us">Contact us</a>';
					
					echo '</div>';
					
				echo '</div>'.PHP_EOL;

			echo '</div>'.PHP_EOL;
			
		echo '</div>'.PHP_EOL;
		
	echo '</div>'.PHP_EOL;
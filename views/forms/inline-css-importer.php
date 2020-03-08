<?php 

	$ltple = LTPLE_Client::instance();

	$layer = '<div class="container">';
	
		$layer .= '<div class="panel panel-default" style="margin:50px;">';
		
		$layer .= '<div class="panel-heading">';

			$layer .='<h4>'.ucfirst($ltple->layer->title).'</h4>';
			
		$layer .= '</div>';
		
		$layer .= '<div class="panel-body">';
		
			$layer .= '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
			
				$layer .= '<div class="col-xs-3">';
				
					$layer .='<label>HTML</label>';
					
				$layer .= '</div>';
				
				$layer .= '<div class="col-xs-9">';
				
					$layer .= '<div class="form-group">';
					
						$layer .= '<textarea class="form-control" name="importHtml" style="min-height:100px;"></textarea>';
						
					$layer .= '</div>';
					
				$layer .= '</div>';

				$layer .= '<div class="col-xs-12 text-right">';
					
					$layer .= '<input class="btn btn-primary btn-md" type="submit" value="Import" />';
					
				$layer .= '</div>';						
			
			$layer .= '</form>';
			
		$layer .= '</div>';
		$layer .= '</div>';
	
	$layer .= '</div>';
	
	echo $layer;
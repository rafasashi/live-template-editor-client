<?php 
	
	$ltple = LTPLE_Client::instance();
	
    $layer = LTPLE_Editor::instance()->get_layer();
	
    $html = '<div class="container">';
	
		$html .= '<div class="panel panel-default" style="margin:50px;">';
		
		$html .= '<div class="panel-heading">';
		
			$html .='<h4>'.ucfirst($layer->post_title).'</h4>';
			
		$html .= '</div>';
		
		$html .= '<div class="panel-body">';
		
			$html .= '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
			
				$html .= '<div class="col-xs-3">';
				
					$html .='<label>HTML</label>';
					
				$html .= '</div>';
				
				$html .= '<div class="col-xs-9">';
				
					$html .= '<div class="form-group">';
					
						$html .= '<textarea class="form-control" name="importHtml" style="min-height:100px;"></textarea>';
						
					$html .= '</div>';
					
				$html .= '</div>';
			
				$html .= '<div class="col-xs-3">';
				
					$html .='<label>CSS</label>';
					
				$html .= '</div>';
				
				$html .= '<div class="col-xs-9">';
				
					$html .= '<div class="form-group">';
					
						$html .= '<textarea class="form-control" name="importCss" style="min-height:100px;"></textarea>';
						
					$html .= '</div>';
					
				$html .= '</div>';									
				
				$html .= '<div class="col-xs-12 text-right">';
					
					$html .= '<input class="btn btn-primary btn-md" type="submit" value="Import" />';
					
				$html .= '</div>';						
			
			$html .= '</form>';
			
		$html .= '</div>';
		$html .= '</div>';
	
	$html .= '</div>';
	
	echo $html;
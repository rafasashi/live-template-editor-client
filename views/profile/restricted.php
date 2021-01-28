<?php

	$restricted  = '<!DOCTYPE html>';
	
	$restricted .= '<html>';
	
		$restricted .= '<meta name="robots" content="noindex">';
		
		$restricted .= '<link rel="stylesheet" href="'.$this->parent->assets_url . 'css/bootstrap.min.css'.'">';
	
	$restricted .= '</html>';
	
	$restricted .= '<body style="background:#000;">';
	
		$restricted .= '<div id="logo" style="text-align:center;z-index:2000;position:absolute;width: 100%;">';
	
			$restricted .= '<div id="logo_wrapper" style="box-shadow:inset 0px 0px 3px #000000;background:#fff;display:inline-block;padding:30px 10px;border-radius:250px;height:100px;width:100px;margin-top:15px;">';
	
				$restricted .= '<img style="width:100%;" src="' . $this->parent->settings->options->logo_url . '" />';
	
			$restricted .= '</div>';
	
		$restricted .= '</div>';
	
		$restricted .= '<div class="modal-backdrop in"></div>';
		$restricted .= '<div class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="channelModal" style="display:block;">';
			
			$restricted .= '<div class="modal-dialog modal-lg" role="document" style="margin-top:130px;">';
				
				$restricted .= '<div class="modal-content">';
				
					$restricted .= '<div class="modal-header">';

						$restricted .= '<h4 class="modal-title" id="channelModal">Access Restricted</h4>';
					
					$restricted .= '</div>';
				  
					$restricted .= '<div class="modal-body" style="height:200px;overflow: auto;line-height: 30px;font-size: 16px;">';
						
						$restricted .= '<div class="alert alert-warning">';
						
							$restricted .= 'Sorry but the access to this page is restricted';
						
						$restricted .= '</div>';
						
					$restricted .= '</div>';
					
					$restricted .= '<div class="modal-footer">';
					
						$restricted .= '<a id="disagreeBtn" href="' . $this->parent->urls->primary . '" ref="nofollow" class="btn btn-info">Go Back</a>';
						
					$restricted .= '</div>';
					
				$restricted .= '</div>';
				
			$restricted .= '</div>';
			
		$restricted .= '</div>';

	$restricted .= '</body>';

	die($restricted);
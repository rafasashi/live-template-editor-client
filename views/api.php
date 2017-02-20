<?php 

	header('Content-Type: application/json');
	
	$dataset = '';
	
	$api = $_REQUEST['api'];
	
	if($this->user->loggedin || in_array($api,['layer/show']) ){

		$api = explode('/',$api.'/');
		
		list($object, $action, $id) = $api;
			
		if( $_SERVER['REQUEST_METHOD'] == 'GET' ){
			
			if( isset($this->{$object}) ){
			
				$method = $action . '_'.$object;
			
				if( method_exists($this->{$object},$method) ){
					
					$dataset = $this->{$object}->$method($id);
				}
				else{
					
					$dataset = 'This action doesn\'t exist...';
				}
			}
			else{
				
				$dataset = 'This object doesn\'t exist...';
			}
		} 
		elseif( $_SERVER['REQUEST_METHOD'] == 'POST' ){
			
			if( in_array($action,['destroy','engage']) ){
				
				$method = $action . '_'.$object;
				
				$dataset = $this->{$object}->$method($id);
			}
			else{
				
				$dataset = 'This action doesn\'t exists...';
			}
		}
		else{
			
			$dataset = 'Unsupported request method...';
		}
	}
	else{
		
		$dataset = 'You must be loggedin in to access the api...';
	}
	
	echo json_encode($dataset,JSON_PRETTY_PRINT);
	
	exit;
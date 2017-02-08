<?php 

	header('Content-Type: application/json');
	
	$dataset = '';
	
	if($this->user->loggedin){

		$api = explode('/',$_REQUEST['api'].'/');
		
		list($object, $action, $id) = $api;
			
		if( $_SERVER['REQUEST_METHOD'] == 'GET' ){

			if( in_array($action,['list','engage']) ){

				$method = $action . '_'.$object;
				
				$dataset = $this->{$object}->$method($id);
			}
			else{
				
				$dataset = 'This action doesn\'t exists...';
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
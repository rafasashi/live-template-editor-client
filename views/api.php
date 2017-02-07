<?php 

	header('Content-Type: application/json');
	
	$dataset = '';
	
	if($this->user->loggedin){
	
		list($object, $action, $id) = explode('/',$_REQUEST['api']);
		
		if( $_SERVER['REQUEST_METHOD'] == 'GET' ){
		
			if( $action == 'list' ){
				
				$method = $action . '_'.$object;
				
				$dataset = $this->{$object}->$method($id);
			}
		} 
		elseif( $_SERVER['REQUEST_METHOD'] == 'POST' ){
			
			if( $action == 'destroy' ){
				
				$method = $action . '_'.$object;
				
				$dataset = $this->{$object}->$method($id);
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
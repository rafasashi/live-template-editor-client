<?php 
	
	header('Content-Type: application/json');
	
	if($this->user->loggedin){
	
		list($action, $dataset, $id) = explode('/',$_GET['api']);
		
		if( $action == 'get' ){
			
			$method = $action . '_'.$dataset;
			
			$data = $this->{$dataset}->$method($id);
			
			echo json_encode($data,JSON_PRETTY_PRINT);
		}
	}
	else{
		
		echo 'You must be loggedin in to access the api...';
	}
	
	exit;
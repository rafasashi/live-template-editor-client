<?php

	$request_url = $_GET['url'];
	
	if(isset($_GET['url'])){
		
		header('Content-type: image');
		readfile($request_url);		
	}
	
	flush();
	exit;
	die;
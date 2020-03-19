<?php
	
	$ltple = LTPLE_Client::instance();
	
	/* Config */
	
	$timeout = 30; // seconds
	$latency = 0; // simulate latency; seconds
	
	// get license holder email
	
	$user_email = $ltple->plan->get_license_holder_email($ltple->user);

	$ref = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
	
	$ref_key = md5( 'ref' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . $ltple->_time . $user_email );
	
	$iframe_key = md5( 'iframe' . $ltple->layer->key . $ref_key . $ltple->_time . $user_email );
	
	// NB: query unvisible for the final user

	$request_url = add_query_arg(array(
	
		'uri' 	=> $ltple->layer->id,
		'lk'	=> $ltple->layer->key,
		'lo'	=> $ltple->layer->layerOutput,
		'll'	=> (( $ltple->layer->is_local || $ltple->layer->type == 'cb-default-layer') ? md5( 'true' . $ltple->layer->id ) : md5( 'false' . $ltple->layer->id )),
		'ld'	=> ( defined('REW_DEV_SERVER') && $_SERVER['HTTP_HOST'] == REW_DEV_SERVER ) ? md5( 'true' . $ltple->layer->id ) : md5( 'false' . $ltple->layer->id ),
		'ow'	=> $ltple->ltple_encrypt_str( $user_email ),
		'pu'	=> urlencode($ltple->urls->plans),
		'ref'	=> $ref, 
		'rk'	=> $ref_key,
		'ik'	=> $iframe_key,
		'_'		=> $ltple->_time,
	
	),$ltple->server->url . '/server/');
												
	//dump($request_url);
	
	// get request_method
	
	$request_method = $_SERVER['REQUEST_METHOD'];	

	// get request_headers
	
	if (!function_exists('getallheaders')){
		
		function getallheaders(){ 
			   $headers = ''; 
		   foreach ($_SERVER as $name => $value) 
		   { 
			   if (substr($name, 0, 5) == 'HTTP_') 
			   { 
				   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
			   } 
		   } 
		   return $headers; 
		} 
	} 

	$request_headers 						= getallheaders();
	$request_headers['Host']				= parse_url($ltple->server->url, PHP_URL_HOST);
	$request_headers['X-forwarded-Host']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-Server']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-For']		= $ltple->request->ip;
	$request_headers['X-forwarded-Key']		= md5('remote'.$ltple->request->ip);
	$request_headers['X-forwarded-User']	= $ltple->ltple_encrypt_str( $user_email );
	$request_headers['X-forwarded-Demo']	= ( ( $ltple->layer->type != 'cb-default-layer' || $ltple->layer->price > 0 ) ? $ltple->ltple_encrypt_str( md5( 'false' . $user_email ) ) : $ltple->ltple_encrypt_str( md5( 'true' . $user_email ) ));
	//$request_headers['X-ref-Key']			= $ltple->server->ref_key;
	//$request_headers['X-ref-Url']			= $ltple->server->ref_url;

	$request_body = file_get_contents('php://input');

	/* Simulate latency */
	if(is_numeric($latency) && $latency > 0){
		
		sleep($latency);
	}

	/* setup session */
	//session_start();
	
	/* Forward request */
	$headers = [];
	
	foreach ($request_headers as $key => $value) {
		
		$headers[] = $key . ': ' . $value;
	}
	//var_dump($headers);exit;

	$ch = curl_init($request_url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);

	echo'<!DOCTYPE>';

	if($response === false) {
		
		header('HTTP/1.1 502 Bad Gateway');
		header('Content-Type: text/plain');
		
		echo 'Upstream host did not respond.';
	} 
	else {
		
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
		if($httpcode < 400){
		
			// get response header
		
			$header_length = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$response_headers = explode("\n", substr($response, 0, $header_length));
			
			foreach ($response_headers as $i => $header) {

				if($header = trim($header)){
					
					if(strpos($header,'Location')!==0){
						
						//header($header);
					}
					else{
						
						echo 'This page moved permanently...';
						curl_close($ch);
						exit;
					}
				}
			}

			// get response body
			
			$response_body = substr($response, $header_length);
			$response_body = gzdecode ($response_body);			
			
			echo apply_filters('ltple_editor_content',$response_body);
		}
		else{
			
			echo 'This page doesn\'t exists...';
		}
	}

	curl_close($ch);

	flush();
	exit;
	die;
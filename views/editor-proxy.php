<!DOCTYPE>
<?php

	/* Config */
	
	$timeout = 30; // seconds
	$latency = 0; // simulate latency; seconds

	$ref = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
	
	$ref_key = md5( 'ref' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . $this->_time . $this->user->user_email );
	
	$iframe_key = md5( 'iframe' . $this->layer->key . $ref_key . $this->_time . $this->user->user_email );
	
	// NB: query unvisible for the final user
	
	$request_url = $this->server->url . '/editor/?uri=' . $this->layer->id 
												.'&lk=' . $this->layer->key 
												.'&lo=' . $this->layer->outputMode
												.'&pu=' . urlencode($this->urls->plans)												
												.'&ref='. $ref 
												.'&rk='	. $ref_key 
												.'&_=' 	. $this->_time
												.'&ik='	. $iframe_key
												.( ( !empty($this->_dev) ) ? '&debug=1' : '' );
												
	//var_dump($request_url);exit; 
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
	$request_headers['Host']				= parse_url($this->server->url, PHP_URL_HOST);
	$request_headers['X-forwarded-Host']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-Server']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-For']		= $this->request->ip;
	$request_headers['X-forwarded-Key']		= md5('remote'.$this->request->ip);
	$request_headers['X-forwarded-User']	= $this->ltple_encrypt_str( $this->user->user_email );
	$request_headers['X-forwarded-Demo']	= ( ( $this->layer->type != 'cb-default-layer' || $this->layer->price > 0 ) ? $this->ltple_encrypt_str( md5( 'false' . $this->user->user_email ) ) : $this->ltple_encrypt_str( md5( 'true' . $this->user->user_email ) ));
	//$request_headers['X-ref-Key']			= $this->server->ref_key;
	//$request_headers['X-ref-Url']			= $this->server->ref_url;

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
			
			if( !empty($this->layer->layerHtmlLibraries) ){
				
				// add dnd panel
				
				$dnd = '<div id="LiveTplEditorDndPanel">';
				
					$dnd .= '<div id="dragitemslist">';
					
						$dnd .= '<ul id="dragitemslistcontainer">';

							foreach( $this->layer->layerHtmlLibraries as $term ){
								
								$elements = get_option( 'elements_' . $term->slug );
								
								if( !empty($elements['name']) ){
									
									foreach( $elements['name'] as $e => $name ){
										
										$dnd .= '<li draggable="true" data-insert-html="' . str_replace( array('\\"'), array("'"), $elements['content'][$e] ) . '">';
										
											$dnd .= '<span>'.$name.'</span>';
										
											$dnd .= '<img title="'.$name.'" height="60" src="' . $elements['image'][$e] . '" />';
										
										$dnd .= '</li>';
									}
								}
							}
							
						$dnd .= '</ul>';
					
					$dnd .= '</div>';
					
				$dnd .= '</div>';
				
				$response_body = str_replace('<div id="LiveTplEditorDndPanel"></div>',$dnd,$response_body);
			}			
			
			echo $response_body;
		}
		elseif( !empty( $this->_dev ) ){
			
			echo $this->urls->current;
		}
		else{
			
			echo 'This page doesn\'t exists...';
		}
	}

	curl_close($ch);

	flush();
	exit;
	die;
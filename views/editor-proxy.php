<!DOCTYPE>
<?php
	
	/* Config */
	
	$timeout = 30; // seconds
	$latency = 0; // simulate latency; seconds
	
	// get license holder email
	
	$user_email = $this->plan->get_license_holder_email($this->user);

	$ref = urlencode( $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );
	
	$ref_key = md5( 'ref' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . $this->_time . $user_email );
	
	$iframe_key = md5( 'iframe' . $this->layer->key . $ref_key . $this->_time . $user_email );
	
	// NB: query unvisible for the final user

	$request_url = add_query_arg(array(
	
		'uri' 	=> $this->layer->id,
		'lk'	=> $this->layer->key,
		'lo'	=> $this->layer->layerOutput,
		'll'	=> ( $this->layer->is_local ? md5( 'true' . $this->layer->id ) : md5( 'false' . $this->layer->id )),
		'pu'	=> urlencode($this->urls->plans),
		'ref'	=> $ref, 
		'rk'	=> $ref_key,
		'ik'	=> $iframe_key,
		'_'		=> $this->_time,
	
	),$this->server->url . '/server/');
												
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
	$request_headers['Host']				= parse_url($this->server->url, PHP_URL_HOST);
	$request_headers['X-forwarded-Host']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-Server']	= $_SERVER['HTTP_HOST'];
	$request_headers['X-forwarded-For']		= $this->request->ip;
	$request_headers['X-forwarded-Key']		= md5('remote'.$this->request->ip);
	$request_headers['X-forwarded-User']	= $this->ltple_encrypt_str( $user_email );
	$request_headers['X-forwarded-Demo']	= ( ( $this->layer->type != 'cb-default-layer' || $this->layer->price > 0 ) ? $this->ltple_encrypt_str( md5( 'false' . $user_email ) ) : $this->ltple_encrypt_str( md5( 'true' . $user_email ) ));
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

			echo do_shortcode($response_body);
			
			//----------  editor modals  ---------------
			
			?>
			
			<div class="modal fade" id="media_library_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
					
						<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
						<h3 class="modal-title text-left">Media library</h3>
						</div>
						
						<div id="media_library_container"></div>
						<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url('<?php echo $this->assets_url; ?>loader.gif');height:64px;"></div>
						<iframe id="media_library_iframe" src=""  data-src="<?php echo $this->urls->media; ?>?output=widget" style="margin-top: -64px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height:450px;"></iframe>
					
					</div>
				</div>
			</div>
			
			<div class="modal fade" id="bookmarks_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
					
						<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
						<h3 class="modal-title text-left">Media Library</h3>
						</div>
						
						<div id="media_library_container"></div>
						
						<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url('<?php echo $this->assets_url; ?>loader.gif');height:64px;"></div>
						<iframe id="bookmarks_iframe" src=""  data-src="<?php echo $this->urls->media; ?>user-payment-urls/?output=widget" style="margin-top: -64px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height:450px;"></iframe>
					</div>
				</div>
			</div>
			
			<?php
		}
		else{
			
			echo 'This page doesn\'t exists...';
		}
	}

	curl_close($ch);

	flush();
	exit;
	die;
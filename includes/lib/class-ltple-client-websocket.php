<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Websocket {

	var $parent;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		//init websocket 
		
		add_action('wp_loaded',array( $this,'init'));

		add_action('ltple_editor_ui_script',array( $this,'get_editor_ui_script'),10,2);
	}
	
	public function init(){
	
		//Add Custom API Endpoints
		
		add_action('rest_api_init', function(){
					
			register_rest_route( 'ltple-websocket/v1', '/info/', array(
				
				'methods' 	=> 'GET',
				'callback' 	=> array($this,'get_websocket_info'),
				'permission_callback' => '__return_true',
			));
			
		});
	}
	
	public function get_websocket_info($request){
		
		//$referer = $request->get_header( 'referer' );
		
		if( !empty($_GET['room']) ){
			
			$room = $_GET['room'];
			
			$info = array(
			
				'url' 	=> 	$this->get_url($room),
				'room'	=>	$room
			);
			
			// TODO check if server ready
			
			return $info;
		}
		
		return false;
	}
	
	public function get_url($room = '1234'){
		
		$isSecure = ( (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) ? true : false;
		
		$ssl = $isSecure ? 's' : '';
		
		$host_name = 'ws-api.recuweb.co';
		
		// start server
		
		$server_url = 'http'.$ssl.'://' . $host_name . '/a/chat/start.php?room=' . $room;

		$socket_url = false;
		
		if( $ch = curl_init() ){

			curl_setopt($ch,CURLOPT_URL,$server_url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

			$socket_url = curl_exec($ch);

			curl_close($ch);
		}
		
		return $socket_url;
	}
	
	public function get_editor_ui_script($script,$layer){
		
		$script .= ';(function($){' . PHP_EOL;

			$script .= $this->get_script('editor-ui');

			$script .= '$(document).ready(function(){' . PHP_EOL;

				$script .= 'var olWebSocket = startWebsocket("' . $this->get_key('layer_ui_' . $layer->ID) . '");' . PHP_EOL;

			$script .= '});' . PHP_EOL;

		$script .= '})(jQuery);' . PHP_EOL;
		
		return $script;
	}
	
	public function get_script($context=''){
		
		$script = '';
		
		$script .= 'function showMessage(messageHTML) {' . PHP_EOL;
			
			if( $context == 'editor-ui' ){
			
				$script .= 'var message = new DOMParser().parseFromString(messageHTML, "text/xml");' . PHP_EOL;

				$script .= 'console.log($(message).text());' . PHP_EOL;
			}
			else{
			
				$script = apply_filters('ltple_websocket_show_message_script',$script,$context);
			}

		$script .= '}' . PHP_EOL;
		
		$script .= 'function getSocketInfo(room){' . PHP_EOL;
		
			$script .= 'var info;' . PHP_EOL;

			$script .= '$.ajax({' . PHP_EOL;
				
				$script .= 'type: "GET",' . PHP_EOL;
				$script .= 'url	: "' . $this->parent->urls->api . 'ltple-websocket/v1/info/",' . PHP_EOL;
				$script .= 'contentType: "application/json",' . PHP_EOL;
				$script .= 'data: {' . PHP_EOL;
					
					$script .= 'room : room,' . PHP_EOL;
				
				$script .= '},' . PHP_EOL;
				$script .= 'async: false,' . PHP_EOL;
				$script .= 'beforeSend: function() {' . PHP_EOL;

				$script .= '},' . PHP_EOL;
				$script .= 'complete: function() {' . PHP_EOL;
					
					
				$script .= '},' . PHP_EOL;
				$script .= 'success: function(data) {' . PHP_EOL;
					
					$script .= 'if( typeof data.url == typeof undefined){';
						
						$script .= 'showMessage("<ws-error>Error reaching server</ws-error>");' . PHP_EOL;

					$script .= '} else {';
					
						$script .= 'info = data;' . PHP_EOL;

					$script .= '}';

				$script .= '},' . PHP_EOL;
				$script .= 'error: function(jqXHR,textStatus) {' . PHP_EOL;
					
					$script .= 'showMessage("<ws-error>Error reaching server</ws-error>");' . PHP_EOL;
				
				$script .= '}' . PHP_EOL;
			
			$script .= '});' . PHP_EOL;
			
			$script .= 'return info;' . PHP_EOL;
			
		$script .= '}' . PHP_EOL;
		
		$script .= 'function startWebsocket(info){' . PHP_EOL;

			$script .= 'if( typeof info == \'string\' ){' . PHP_EOL;
				
				$script .= 'info = getSocketInfo(info);' . PHP_EOL;
				
			$script .= '}' . PHP_EOL;
			
			$script .= 'if( typeof info == typeof undefined && typeof info.url == typeof undefined ){' . PHP_EOL;
				
				$script .= 'showMessage("<ws-closed>Wrong socket credentials</ws-closed>");' . PHP_EOL;
				
				$script .= 'return false;' . PHP_EOL;
				
			$script .= '}' . PHP_EOL;
			
			$script .= 'showMessage("<ws-start>Connecting...</ws-start>");' . PHP_EOL;

			$script .= 'olWebSocket = new WebSocket(info.url);' . PHP_EOL;

			$script .= 'olWebSocket.onopen = function(event) {' . PHP_EOL; 
				
				$script .= 'showMessage("<ws-connection>Connection established</ws-connection>");' . PHP_EOL;	
				
				// keep the socket alive
				
				$script .= 'setInterval(function() {' . PHP_EOL;
					
					$script .= 'olWebSocket.send("");' . PHP_EOL;
				
				$script .= '}, 10 * 1000 );' . PHP_EOL; // every 10 sec
								
				$script = apply_filters('ltple_websocket_connection_script',$script,$context);
				
			$script .= '}' . PHP_EOL;

			$script .= 'olWebSocket.onmessage = function(event) {' . PHP_EOL;

				$script .= 'var Data = JSON.parse(event.data);' . PHP_EOL;
				
				$script .= 'var type = Data.message_type;' . PHP_EOL;
				
				$script .= "var room = Data.message.substring(0, Data.message.indexOf(':'));" . PHP_EOL;
				
				$script .= "var message = Data.message.substring(Data.message.indexOf(':')+1);" . PHP_EOL;
				
				$script .= 'if( typeof message != "string" || message == "" ) return false;' . PHP_EOL;
				
				$script = apply_filters('ltple_websocket_onmessage_script',$script,$context);

			$script .= '};' . PHP_EOL;

			$script .= 'olWebSocket.onerror = function(event){' . PHP_EOL;
				
				$script .= 'showMessage("<ws-error>Problem due to some Error</ws-error>");' . PHP_EOL;
			
				$script .= 'info = reconnectWebsocket(info,10);' . PHP_EOL;
				
			$script .= '};' . PHP_EOL;
			
			$script .= 'olWebSocket.onclose = function(event){' . PHP_EOL;
				
				$script .= 'showMessage("<ws-closed>Connection closed</ws-closed>");' . PHP_EOL;

				$script .= 'info = reconnectWebsocket(info,0.5);' . PHP_EOL;
			
			$script .= '};' . PHP_EOL;

			$script = apply_filters('ltple_websocket_start_script',$script,$context);
			
			$script .= 'return olWebSocket;' . PHP_EOL;

		$script .= '}' . PHP_EOL;
		
		$script .= 'function reconnectWebsocket(info,delay){' . PHP_EOL;
			
			// get new info
			
			$script .= 'info = getSocketInfo(info.room);' . PHP_EOL;

			$script .= 'setTimeout(function() {' . PHP_EOL;
			  
				// reconnect socket after delay
			  
				$script .= 'olWebSocket = startWebsocket(info);' . PHP_EOL;
			
			$script .= '},( delay * 1000 ));' . PHP_EOL;
		
			$script .= 'return info;' . PHP_EOL;
		
		$script .= '}' . PHP_EOL;
		
		return apply_filters('ltple_websocket_script',$script,$context);
	}
	
	public function open_socket($room,$data){
		
		if( $socket_url = $this->get_url($room) ){
			
			$url = parse_url($socket_url);
			
			$host 	= $url['host'];
			$port 	= $url['scheme'] == 'wss' ? 443 : 80;
			$local 	= $this->parent->urls->current;
			$key 	= 'asdasdaas76da7sd6asd6as7dasdasdaas76da7sd6asd6as7d';
			
			$head = "GET / HTTP/1.1"."\r\n".
					"Upgrade: WebSocket"."\r\n".
					"Connection: Upgrade"."\r\n".
					"Origin: $local"."\r\n".
					"Host: $host"."\r\n".
					"Sec-WebSocket-Version: 13"."\r\n".
					"Sec-WebSocket-Key: $key"."\r\n".
					"Content-Length: ".strlen($data)."\r\n"."\r\n";
			
			// WebSocket handshake
			
			$socket = fsockopen($host, $port, $errno, $errstr, 2);
			
			if( fwrite($socket, $head ) ){
			
				//$headers = fread($socket, 2000);
				
				return $socket;
			}
		}
		
		return false;
	}
	
	public function read_socket(){

		if( $socket = $this->open_socket() ){

			$wsdata = fread($socket, 2000);

			fclose($socket);

			return $this->hybi10Decode($wsdata);
		}

		return false;
	}
	
	public function get_key($str){
		
		return $this->parent->ltple_encrypt_str($str);
	}
	
	public function send_user_message($str,$message){
		
		$room 	= $this->get_key($str);
		
		$data 	= json_encode(array(
			
			'chat_user' 	=> $room,
			'chat_message' 	=> $message,
		));		
		
		if( $socket = $this->open_socket($room,$data) ){

			if( fwrite($socket, $this->hybi10Encode($data)) ){
				
				fclose($socket);
				
				return true;
			}
		}
		
		return false;
	}
	
	public function hybi10Encode($payload, $type = 'text', $masked = true) {
		
		$frameHead = array();
		$frame = '';
		$payloadLength = strlen($payload);

		switch ($type) {
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
				break;
			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
				break;
			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
				break;
			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
				break;
		 }

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}

			// most significant bit MUST be 0 (close connection if frame too big)
			if ($frameHead[2] > 127) {
				$this->close(1004);
				return false;
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked === true) {
			// generate a random mask:
			$mask = array();
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}
			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);
		// append payload to frame:
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		return $frame;
	}

	public function hybi10Decode($data){
		
		$bytes = $data;
		$dataLength = '';
		$mask = '';
		$coded_data = '';
		$decodedData = '';
		$secondByte = sprintf('%08b', ord($bytes[1]));
		$masked = ($secondByte[0] == '1') ? true : false;
		$dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);
		if($masked === true)
		{
			if ($dataLength === 126) {
			   $mask = substr($bytes, 4, 4);
			   $coded_data = substr($bytes, 8);
			}
			elseif ($dataLength === 127) {
				$mask = substr($bytes, 10, 4);
				$coded_data = substr($bytes, 14);
			}
			else {
				$mask = substr($bytes, 2, 4);       
				$coded_data = substr($bytes, 6);        
			}   
			for ($i = 0; $i < strlen($coded_data); $i++) {       
				$decodedData .= $coded_data[$i] ^ $mask[$i % 4];
			}
		}
		else {
			if ($dataLength === 126) {          
			   $decodedData = substr($bytes, 4);
			}
			elseif ($dataLength === 127) {           
				$decodedData = substr($bytes, 10);
			} 
			else {               
				$decodedData = substr($bytes, 2);       
			}       
		}   

		return $decodedData;
	}

	/**
	 * Main LTPLE_Client_Endpoint Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Ranking is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Ranking instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}

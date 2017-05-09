<?php
		
	echo'<!DOCTYPE html>'.PHP_EOL;
	echo'<html>'.PHP_EOL;

		echo'<head>'.PHP_EOL;
		
			wp_head();
			
			echo'<style>'.PHP_EOL;
				
				echo'#header_logo {';
					
					echo'max-width:90px;';
					echo'width:100%;';
					echo'height: 50px;';
					echo'z-index: 9999;';
					echo'position: absolute;';
					echo'overflow: hidden;';
					echo'display: inline-block;';
					echo'background-position: center left;';				
					echo'background-image:url(' . $this->assets_url . 'images/header_small.png);';
				
				echo'}';

				echo'#header_logo a {';
				
					echo'padding:8px 4px;';
					echo'height:50px;';
					echo'width:100%;';
					echo'border:none;';
					echo'display:inline-block;';
					echo'text-align:center;';
					
				echo'}';
				
				echo'#header_logo a img {';
					
					echo'width: auto;';
					echo'height: 35px;';
					echo'margin-left: -10px;';
					
				echo'}';
				
				echo'#main-menu {';
					
					echo'position:absolute;';
					echo'width:100%;';
					echo'padding:10px 0px 0 90px;';
				
				echo'}';
				
				echo'#main-menu li {';
				
					echo'display:inline-flex;';
					echo'margin-left: 5px;';
				
				echo'}';

			echo'</style>'.PHP_EOL;	
			
		echo'</head>';
		
		echo'<body style="background:#f5f5f5;">';

			if( !empty($this->layer->embedded['p']) ){
				
				if(!empty($_SESSION['message'])){
					
					echo $_SESSION['message'].PHP_EOL;
					
					$_SESSION['message'] = '';
				}				

				echo do_shortcode( '[ltple-client-editor]' );
			}
			else{
				
				echo 'Wrong embedded request...';
			}
			
			wp_footer(); 
		
		echo'</body>';
		
	echo'</html>';

	exit;
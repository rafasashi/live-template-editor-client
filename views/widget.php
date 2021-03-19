<?php ob_clean(); 

	// add head
	
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	
	add_action( 'wp_head', array( $this, 'get_header') );
	
	// add menu
	
	add_filter( 'wp_nav_menu', array( $this, 'get_menu' ), 10, 2);
?>

<!DOCTYPE html>
<html>

	<head>
	
		<?php 
		
			wp_head(); 
		
		
		?>
		
		<style>.pgheadertitle{display:none;}.tabs-left,.tabs-right{border-bottom:none;padding-top:2px}.tabs-left{border-right:0px solid #ddd}.tabs-right{border-left:0px solid #ddd}.tabs-left>li,.tabs-right>li{float:none;margin-bottom:2px}.tabs-left>li{margin-right:-1px}.tabs-right>li{margin-left:-1px}.tabs-left>li.active>a,.tabs-left>li.active>a:focus,.tabs-left>li.active>a:hover{border-left: 5px solid #F86D18;border-top:0;border-right:0;border-bottom:0; }.tabs-right>li.active>a,.tabs-right>li.active>a:focus,.tabs-right>li.active>a:hover{border-bottom:0px solid #ddd;border-left-color:transparent}.tabs-left>li>a{border-radius:4px 0 0 4px;margin-right:0;display:block}.tabs-right>li>a{border-radius:0 4px 4px 0;margin-right:0}.sideways{margin-top:50px;border:none;position:relative}.sideways>li{height:20px;width:120px;margin-bottom:100px}.sideways>li>a{border-bottom:0px solid #ddd;border-right-color:transparent;text-align:center;border-radius:4px 4px 0 0}.sideways>li.active>a,.sideways>li.active>a:focus,.sideways>li.active>a:hover{border-bottom-color:transparent;border-right-color:#ddd;border-left-color:#ddd}.sideways.tabs-left{left:-50px}.sideways.tabs-right{right:-50px}.sideways.tabs-right>li{-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg)}.sideways.tabs-left>li{-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg)}</style>
		
	</head>
	<body id="ltple-wrapper" style="background-color:#fff !important;position:absolute;overflow:hidden;top:0;bottom:0;left:0;right:0;">
	
	<?php 
		
		wp_body_open();

		if(!empty($_SESSION['message'])){
			
			echo $_SESSION['message'].PHP_EOL;
			
			$_SESSION['message'] = '';
		}					
		
		global $post;
		
		if( isset($post->post_type) ){

			if( $post->post_type ==  'subscription-plan' ){
				
				if( !empty($_GET['sc']) && shortcode_exists( $_GET['sc'] ) ){
					
					echo do_shortcode( '['.$_GET['sc'].' id="' . $post->ID . '" widget="true"]' );
				}
				else{
					
					echo do_shortcode( '[subscription-plan id="' . $post->ID . '" widget="true"]' );
				}
			}
			else{
				
				// addon widgets

				echo apply_filters( 'the_content', $post->post_content );
			}
		}
		
		wp_footer(); 
		 
	?>
	
	</body>
</html>

<?php exit; ?>
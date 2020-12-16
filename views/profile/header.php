<?php

	$ltple = LTPLE_Client::instance();
	
	// add head
		
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		
	add_action( 'wp_head', array( $ltple, 'get_header') );
	
?>
<!DOCTYPE html>	
<html <?php language_attributes(); ?> class="<?php echo apply_filters('ltple_document_classes','ltple-theme'); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head();?>
</head> 
<body <?php body_class('boxedlayout'); ?>>

	<div id="ltple-wrapper" class="boxedcontent" style="position:absolute;z-index:auto;border:none;">
		
<?php 
	
	if( !$ltple->inWidget ){
		
		// get name
		
		$name = get_user_meta( $ltple->profile->user->ID , 'nickname', true );

		if( $ltple->profile->in_tab ){

			echo'<div class="profile-heading text-center" style="height:100px;padding:0;">';
			
				echo'<div class="profile-overlay"></div>';
			
				// mobile avatar
				
				echo'<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">';

					echo '<div class="profile-avatar text-left hidden-sm hidden-md hidden-lg" style="padding:12px 8px;position:absolute;">';
					
						echo'<img style="border:solid 5px #f9f9f9;" src="' . $ltple->profile->picture . '" height="70" width="70" />';
						
						if( $ltple->profile->is_pro ){
							
							echo'<span class="label label-primary" style="position:absolute;bottom:24%;margin-left:-30px;background:' . $ltple->settings->mainColor . ';font-size:14px;">pro</span>';									
						}
						
					echo '</div>';					
				
				echo'</div>';
				
				echo'<div class="col-xs-9 col-sm-9 col-md-9 col-lg-10">';
				
					echo '<h2 style="font-size:25px;float:left;padding:31px 0 0 0;margin:0;">' . $name . '</h2>';
				
				echo'</div>';
				
			echo'</div>';
		}
		else{
			
			echo'<div class="profile-heading text-center" style="height:60px;padding:0;">';
			
				echo'<div class="profile-overlay"></div>';
				
				echo'<div class="col-xs-12">';
				
					echo '<h2 style="font-size:22px;float:left;padding:15px 0 0 0;margin:0;">' . $name . '</h2>';
				
				echo'</div>';
				
			echo'</div>';
		}
	}
		
		if( $buttons = apply_filters('ltple_floating_buttons','')){
		
			echo '<div class="floating-buttons" style="position:fixed;z-index:1050;right:0;bottom:50px;margin:15px 3%;">';
			
				echo  $buttons;
				
			echo '</div>';
		}
<?php

	$ltple = LTPLE_Client::instance();
	
	// add head
		
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		
	add_action( 'wp_head', array( $ltple, 'get_header') );
	
	$sitemap_url = trailingslashit($ltple->urls->home) . 'wp-sitemap.xml';
	$feed_url 	 = trailingslashit($ltple->urls->home) . 'feed/';
	
	$output = $ltple->inWidget ? 'widget' : 'ui';
?>
<!DOCTYPE html>	
<html <?php language_attributes(); ?> class="<?php echo apply_filters('ltple_document_classes','ltple-theme'); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="sitemap" href="<?php echo $sitemap_url; ?>" type="application/xml" />
	<link rel="alternate" href="<?php echo $feed_url; ?>"  type="application/rss+xml" title="RSS Feed" />
	<?php wp_head();?>
</head> 
<body <?php body_class('boxedlayout ltple-' . $output); ?>>

	<?php wp_body_open(); ?>

	<div id="ltple-wrapper" class="boxedcontent" style="position:absolute;z-index:auto;border:none;width:100%;top:0;left:0;right:0;display:contents !important;">
		
<?php 
	
	if( !$ltple->inWidget ){
		
		// get name
		
		$name = ucfirst(get_user_meta( $ltple->profile->user->ID , 'nickname', true ));

		if( $ltple->profile->in_tab ){
			
			if( $ltple->profile->tab == 'about' ){

				echo'<div class="profile-heading text-center" style="height:100px;padding:0;">';
				
					echo'<div class="profile-overlay"></div>';
				
					// mobile avatar
					
					echo'<div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">';

						echo '<div class="profile-avatar text-left hidden-sm hidden-md hidden-lg" style="padding:12px 8px;position:absolute;">';
						
							echo'<img style="border:none;" src="' . $ltple->profile->picture . '" height="70" width="70" />';
							
						echo '</div>';					
					
					echo'</div>';
					
					echo'<div class="col-xs-9 col-sm-9 col-md-9 col-lg-10">';
					
						echo '<h2 style="font-size:calc( 0.5vw + 15px );float:left;padding:35px 0 0 0;margin:0;">' . $name . '</h2>';
					
					echo'</div>';
					
				echo'</div>';
			}
			else{

				echo'<div class="profile-heading text-center" style="height:80px;padding:0;">';
				
					echo'<div class="profile-overlay"></div>';
				
					// avatar

					echo '<div class="profile-avatar text-left" style="padding:10px;position:absolute;">';
					
						echo'<img style="border:none;" src="' . $ltple->profile->picture . '" height="55" width="55" />';
						
					echo '</div>';					
					
					echo '<h2 style="font-size:23px;float:left;padding:25px 0 0 85px;margin:0;line-height:25px;">' . $name . '</h2>';
					
				echo'</div>';
			}
		}
		else{
			
			echo'<div class="profile-heading text-center" style="height:60px;padding:0;">';
			
				echo'<div class="profile-overlay"></div>';
				
				// avatar

				echo '<div class="profile-avatar text-left" style="padding:8px;position:absolute;">';
				
					echo'<img style="border:none;" src="' . $ltple->profile->picture . '" height="40" width="40" />';
					
				echo '</div>';	
					
				echo '<h2 style="font-size:22px;float:left;padding:15px 0 0 60px;margin:0;">' . $name . '</h2>';
				
			echo'</div>';
		}
	}
		
	if( $buttons = apply_filters('ltple_floating_buttons','')){
	
		echo '<div class="floating-buttons" style="position:fixed;z-index:1050;right:0;top:' . ( $ltple->inWidget ? 70 : 190 ).'px;margin:15px 3%;">';
		
			echo  $buttons;
			
		echo '</div>';
	}
	
?>

<nav id="profile_menu" class="formheadersearch" role="navigation" style="background-color:transparent;z-index:998;"></nav>
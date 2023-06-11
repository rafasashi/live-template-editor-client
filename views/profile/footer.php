<?php

$ltple = LTPLE_Client::instance();

// Gets all the scripts included by wordpress, wordpress plugins or functions.php

if( $ltple->profile->in_tab() ){

	echo'<div id="floating_bar">';
		
		if( $whatsapp_url = $ltple->profile->get_whatsapp_url() ){
		
			echo'<a id="whatsapp" style="background:#25d366;" data-toggle="tooltip" data-placement="left" title="WhatsApp Chat" href="'.$whatsapp_url.'" target="_blank"><i class="fab fa-whatsapp" style="color:#fff;"></i></a>';
		}

		echo'<a id="to_top" href="#" data-toggle="tooltip" data-placement="left" title="Scroll to Top"><i class="fa fa-angle-up p-0 m-2"></i></a>';
	
	echo'</div>';
			
	if( !$ltple->inWidget ){
	
		echo'<footer id="ltple-footer" role="contentinfo" style="margin:0 !important;padding:0 !important;position:relative !important;width: 100% !important;display:table !important;">';
		
		if( LTPLE_Editor::get_framework('css') == 'bootstrap-4' ){
			
			echo '<div class="footerbottom px-2">';
			
				echo '<div class="row no-gutters">';
		}
		else{
			
			echo '<div class="footerbottom">';
		}
		
		?>

				<div class="col-md-5">
				
					 <?php
					  if( get_theme_mod( 'wow_copyright' ) == '') { ?>
					  <a href="<?php echo $ltple->urls->primary; ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					  <?php }
					  else { echo wp_kses_post( get_theme_mod( 'wow_copyright' ) ); } ?>

				</div>
				
				<div class="col-md-7 smallspacetop">
					
					<div class="pull-right smaller">
					
					<?php
					
					if( wp_nav_menu( array( 
						
						'theme_location' 	=> 'footer',
						'container'  		=> false,
						'depth'		 		=> 0,
						'menu_class' 		=> 'footermenu',
						'fallback_cb' 		=> 'false'
						
					))){
						
						echo wp_nav_menu( array( 'sort_column' => 'menu_order', 'container'  => false, 'theme_location' => 'footer' , 'echo' => '0' ) );
					}
					else {

					}
					
					?>
					
					</div>

				</div>
				
			</div>
				
		<?php 
			
			if( LTPLE_Editor::get_framework('css') == 'bootstrap-4' ){
			
				echo '</div>';
				echo '</div>';
			}
			else{
				
				echo '</div>';
			}
		
		echo '</footer>';
	} 
}
echo'</div>';
wp_footer();
echo'</body>';
echo'</html>';
exit;
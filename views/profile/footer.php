<?php

$ltple = LTPLE_Client::instance();

// Gets all the scripts included by wordpress, wordpress plugins or functions.php

	if( !$ltple->inWidget ){
	
		echo'<footer role="contentinfo" style="position:relative;">';
		
		if( LTPLE_CSS_FRAMEWORK == 'bootstrap-4' ){
			
			echo '<div class="footerbottom px-2">';
			
				echo '<div class="row">';
		}
		else{
			
			echo '<div class="footerbottom">';
			
				echo '<div class="container">';
		}
		
			?>

					<!-- left -->
					<div class="col-md-5">
					
						 <?php
						  if( get_theme_mod( 'wow_copyright' ) == '') { ?>
						  <a href="<?php echo $ltple->urls->primary; ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
						  <?php }
						  else { echo wp_kses_post( get_theme_mod( 'wow_copyright' ) ); } ?>

					</div>
					
					<!-- right -->
					
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
					
					<!-- end right -->
					
				</div>
				
				<!-- end container -->
				
			</div>	
		
		</footer>
	
	<?php } ?>
	
<!-- FOOTER END
================================================== -->
</div>

<?php wp_footer(); ?>
</body>
</html>
<?php exit; ?>
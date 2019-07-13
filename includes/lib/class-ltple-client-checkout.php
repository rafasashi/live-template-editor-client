<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Checkout {
	
	/**
	 * The single instance of LTPLE_Client_Checkout.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;	

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		add_shortcode('ltple-client-checkout', array( $this , 'get_shortcode' ) );
	}
	
	public function get_shortcode() {
		
		if( $this->parent->user->loggedin ){	
			
			if( !empty($_GET['template']) && is_numeric($_GET['template']) ){
				
				$layer_id = intval($_GET['template']);
			
				if( $layer = get_post($layer_id) ){
					
					if( $layer->post_type == 'cb-default-layer' ){
					
						echo'<div class="col-sm-7">';

							if( $plans = $this->parent->plan->get_plans_by_id( $layer->ID ) ){
								
								echo '<h4 style="margin-top:15px;">This template is included in ' . count($plans) . ' plans</h4>';
								
								foreach( $plans as $plan ){
									
									echo'<hr>';
									
									echo'<div class="row">';

										echo'<div class="col-xs-8">';
											
											echo'<div>';
											
												echo '<b>' . $plan['title'] . '</b>';
											
											echo'</div>';
											
											echo'<div>';
												
												echo '<span class="label label-success">' . $plan['price_tag'] . '</span>';			
												
											echo'</div>';

										echo'</div>';
										
										echo'<div class="col-xs-4 text-right">';
											
											echo'<a href="'.$plan['info_url'].'" target="_blank" class="btn btn-sm btn-info" style="margin-right: 5px;">Info</a>';
											
											echo'<a href="'.$plan['agreement_url'].'" target="_self" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
										
										echo'</div>';
										
									echo'</div>';
								}				
							}
						
						echo'</div>';
					}
					else{
						
						echo apply_filters('ltple_checkout_content','',$layer);
					}
				}
				else{
					
					echo 'This template doesn\'t exist...';
				}
			}
			elseif( !empty($_GET['options']) ){
			
				$options = explode('|',$_GET['options']);
				
				if( $plans = $this->parent->plan->get_plans_by_options( $options ) ){
					
					echo'<div class="col-sm-7">';
						
						echo '<h4 style="margin-top:15px;">Upgrade to one of the following plans</h4>';
						
						foreach( $plans as $plan ){
							
							echo'<hr>';
							
							echo'<div class="row">';

								echo'<div class="col-xs-8">';
									
									echo'<div>';
									
										echo '<b>' . $plan['title'] . '</b>';
									
									echo'</div>';
									
									echo'<div>';
										
										echo '<span class="label label-success">' . $plan['price_tag'] . '</span>';			
										
									echo'</div>';

								echo'</div>';
								
								echo'<div class="col-xs-4 text-right">';
									
									echo'<a href="'.$plan['info_url'].'" target="_blank" class="btn btn-sm btn-info" style="margin-right: 5px;">Info</a>';
									
									echo'<a href="'.$plan['agreement_url'].'" target="_self" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
								
								echo'</div>';
								
							echo'</div>';
						}
					
					echo'</div>';
				}
			}
		}
	}
	
	/**
	 * Main LTPLE_Client_Checkout Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Checkout is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Checkout instance
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

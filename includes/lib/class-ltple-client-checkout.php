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
		
		ob_start();
		
		if( !empty($_GET['template']) && is_numeric($_GET['template']) ){
			
			$layer_id = intval($_GET['template']);
		
			if( $layer = get_post($layer_id) ){
				
				if( $layer->post_type == 'cb-default-layer' || $this->parent->layer->is_element($layer) ){
				
					echo'<div class="col-sm-7">';

						if( $plans = $this->parent->plan->get_plans_by_id( $layer->ID ) ){
							
							echo '<h4 style="margin-top:15px;">This template is included in ' . count($plans) . ' plans</h4>';
							
							foreach( $plans as $plan ){
								
								echo'<hr style="margin-top:15px;margin-bottom:15px;">';
								
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
										
										if( $this->parent->user->loggedin ){
											
											echo'<a href="' . $plan['agreement_url'] . '" target="_self" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
										}
										else{
											
											echo'<a href="' . wp_login_url(remove_query_arg('output',$this->parent->urls->current)) . '" target="_parent" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
										}
										
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
			
			echo'<div class="col-sm-7" style="' . ( !$this->parent->inWidget ? 'margin-top:20px;min-height:calc(100vh - 103px)' : '' ).'">';
				
				if( $plans = $this->parent->plan->get_plans_by_options($options,'OR') ){
					
					if( !$this->parent->inWidget ){
					
						echo '<h2 style="margin-top:15px;">Upgrade to one of the following plans</h2>';
					}
					
					foreach( $plans as $i => $plan ){
						
						if( $i > 0 ){
							
							echo'<hr style="margin-top:15px;margin-bottom:15px;">';
						}
						
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
	
								if( $this->parent->user->loggedin ){
									
									if( !$this->parent->inWidget ){
										
										$modal_id = 'modal_' . md5($plan['agreement_url']);
										
										echo '<button type="button" onclick="return false;" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#'.$modal_id.'">' . ucfirst($plan['action']) . '</button>';
										
										echo '<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
											
											echo '<div class="modal-dialog modal-lg" role="document" style="margin:0;width:100% !important;position:absolute;">'.PHP_EOL;
												
												echo '<div class="modal-content">'.PHP_EOL;
													
													echo '<div class="modal-header">'.PHP_EOL;
														
														echo '<button type="button" class="close m-0 p-0 border-0 bg-transparent" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
														
														echo '<h4 class="modal-title text-left" id="myModalLabel">Unlock '.$plan['title'].'</h4>'.PHP_EOL;
													
													echo '</div>'.PHP_EOL;

													echo '<iframe id="iframe_'.$modal_id.'" data-src="' . $plan['agreement_url'] . '" style="display:block;position:relative;width:100%;top:0;bottom: 0;border:0;height:calc( 100vh - 50px );"></iframe>';						
													
												echo '</div>'.PHP_EOL;
												
											echo '</div>'.PHP_EOL;
											
										echo '</div>'.PHP_EOL;
									}
									else{
										
										echo'<a href="' . $plan['agreement_url'] . '" target="_self" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
									}
								}
								else{
									
									echo'<a href="' . wp_login_url(remove_query_arg('output',$this->parent->urls->current)) . '" target="_parent" class="btn btn-sm btn-primary">' . ucfirst($plan['action']) . '</a>';
								}
								
							echo'</div>';
							
						echo'</div>';
					}
				}
				else{
					
					echo '<div class="alert alert-warning">No plan available for this item, please contact the sales department</div>';
				}
			
			echo'</div>';
		}
		
		return ob_get_clean();
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

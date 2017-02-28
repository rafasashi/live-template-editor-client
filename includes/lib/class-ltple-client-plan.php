<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Plan {
	
	var $parent;
	var $key;
	var $subscribed;
	var $data;
	var $message;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		add_action( 'init', array( $this, 'init_plan' ));
	}
	
	public function hasHosting($plan){
		
		if( !empty($plan['meta']) ){
			
			foreach( $plan['meta'] as $meta ){
				
				if(!empty($meta['domain_name'])){
					
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function init_plan(){
		
		return true;
	}

	public function update_user(){
		
		// get plan subscription

		if( !empty( $this->parent->user->ID ) && isset($_GET['pk'])&&isset($_GET['pd'])&&isset($_GET['pv'])){

			$plan_data = sanitize_text_field($_GET['pd']);
			$plan_data = $this->parent->base64_urldecode($plan_data);

			$this->key = sanitize_text_field($_GET['pk']);
			$this->subscribed = sanitize_text_field($_GET['pv']);
			
			// subscribed plan data
			
			if( $this->key == md5('plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email ) && $this->subscribed == md5('subscribed'.$_GET['pd'] . $this->parent->_time . $this->parent->user->user_email ) ){
				
				$plan_data = html_entity_decode($plan_data);
				
				$this->data = json_decode($plan_data,true);
					
				//var_dump($this->data);exit;
				
				if(!empty($this->data['name'])){
					
					//var_dump($plan);exit;
							
					$options = $this->parent->get_layer_custom_taxonomies_options();
					$user_has_subscription = 'false';
					$all_updated_terms = [];
					
					foreach( $options as $taxonomy => $terms ) {
						
						$update_terms=[];
						$update_taxonomy='';
						
						foreach($terms as $i => $term){

							if ( in_array( $term->slug, $this->data['options'] ) ) {
								
								$update_terms[]= $term->term_id;
								$update_taxonomy=$term->taxonomy;
								
								if( $this->data["price"] > 0 ){
									
									$user_has_subscription = 'true';
								}
								
								$all_updated_terms[]=$term->slug;
							}
						}

						// update current user custom taxonomy
						
						$user_plan_id = $this->parent->get_user_plan_id( $this->parent->user->ID, true );
						
						$append = false;

						if( $this->data['price'] == 0 ){
							
							// demo or donation case
							
							$append = true;
						}

						$response = wp_set_object_terms( $user_plan_id, $update_terms, $update_taxonomy, $append );

						clean_object_term_cache( $user_plan_id, $update_taxonomy );
					}
					
					if($this->hasHosting($this->data)){

						foreach($this->data['meta'] as $meta){
							
							if( !empty($meta['domain_name']['name']) ){
								
								// parse domains
								
								foreach($meta['domain_name']['name'] as $i => $name){
									
									if( !empty($meta['domain_name']['name']) ){

										// get domain_name
									
										$domain_name = $name.$meta['domain_name']['ext'][$i];
										
										//check if domain exists
										
										$domain = get_page_by_title( $domain_name, OBJECT, 'user-domain' );

										if( empty($domain) ){
											
											// add domain	
											
											$args = array(
												
												'post_author' 	=> $this->parent->user->ID,
												'post_title' 	=> $domain_name,
												'post_name' 	=> $domain_name,
												'post_type' 	=> 'user-domain',
												'post_status' 	=> 'publish'
											);

											if($domain_id = wp_insert_post( $args )){

												// Do anything
											}
										}
									}
								}
							}
						}
					}
					
					// hook triggers
					
					if( intval($this->data['price']) > 0 ){
						
						do_action('ltple_paid_plan_subscription');
					}
					else{
						
						do_action('ltple_free_plan_subscription');
					}
					
					// schedule email series
					
					$this->parent->ltple_schedule_series( $this->data['id'], $this->parent->user);
					
					if( $this->data['price'] > 0 ){
						
						//send admin notification
							
						wp_mail($this->parent->settings->options->emailSupport, 'Plan edited on checkout - user id ' . $this->parent->user->ID . ' - ip ' . $this->parent->request->ip, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($all_updated_terms,true) . PHP_EOL . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);						
						
						// update user has subscription						
						
						update_user_meta( $this->parent->user->ID , 'has_subscription', $user_has_subscription);

						// store message
						
						$this->message .= '<div class="alert alert-success">';
							
							$this->message .= 'Congratulations, you have successfully subscribed to '.$this->data['name'].'!';
							
							/*
							$this->message .= '<div class="pull-right">';
							
								$this->message .= '<a class="btn-sm btn-success" href="' . $this->parent->urls->editor . '" target="_parent">Start editing</a>';
						
							$this->message .= '</div>';
							*/
							
						$this->message .= '</div>';
							
						//Google adwords Code for subscription completed
						
						$this->message .='<script type="text/javascript">' . PHP_EOL;
							$this->message .='/* <![CDATA[ */' . PHP_EOL;
							$this->message .='var google_conversion_id = 866030496;' . PHP_EOL;
							$this->message .='var google_conversion_language = "en";' . PHP_EOL;
							$this->message .='var google_conversion_format = "3";' . PHP_EOL;
							$this->message .='var google_conversion_color = "ffffff";' . PHP_EOL;
							$this->message .='var google_conversion_label = "wm6DCP2p7GwQoKf6nAM";' . PHP_EOL;
							$this->message .='var google_conversion_value = '.$this->data['price'].'.00;' . PHP_EOL;
							$this->message .='var google_conversion_currency = "USD";' . PHP_EOL;
							$this->message .='var google_remarketing_only = false;' . PHP_EOL;
							$this->message .='/* ]]> */' . PHP_EOL;
						$this->message .='</script>' . PHP_EOL;
						
						$this->message .='<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . PHP_EOL;
						$this->message .='</script>' . PHP_EOL;
						
						$this->message .='<noscript>' . PHP_EOL;
							$this->message .='<div style="display:inline;">' . PHP_EOL;
								$this->message .='<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/866030496/?value='.$this->data['price'].'.00&amp;currency_code=USD&amp;label=wm6DCP2p7GwQoKf6nAM&amp;guid=ON&amp;script=0"/>' . PHP_EOL;
							$this->message .='</div>' . PHP_EOL;
						$this->message .='</noscript>' . PHP_EOL;	

						
						//Facebook Pixel Code for subscription completed
						
						$this->message .='<script>' . PHP_EOL;	
						
							$this->message .='!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?' . PHP_EOL;	
							$this->message .='n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;' . PHP_EOL;	
							$this->message .='n.push=n;n.loaded=!0;n.version=\'2.0\';n.queue=[];t=b.createElement(e);t.async=!0;' . PHP_EOL;	
							$this->message .='t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,' . PHP_EOL;	
							$this->message .='document,\'script\',\'https://connect.facebook.net/en_US/fbevents.js\');' . PHP_EOL;	
							$this->message .='fbq(\'init\', \'135366043652148\');' . PHP_EOL;	
							//$this->message .='fbq(\'track\', \'PageView\');' . PHP_EOL;	
							$this->message .='fbq(\'track\', \'Purchase\', {' . PHP_EOL;	
								$this->message .='value: '.$this->data['price'].'.00,' . PHP_EOL;	
								$this->message .='currency: \'USD\'' . PHP_EOL;
							$this->message .='});' . PHP_EOL;
							
							$this->message .='<noscript><img height="1" width="1" style="display:none"' . PHP_EOL;	
							$this->message .='src="https://www.facebook.com/tr?id=135366043652148&ev=PageView&noscript=1"' . PHP_EOL;	
							$this->message .='/></noscript>' . PHP_EOL;						

						$this->message .='</script>' . PHP_EOL;	

					}
					else{
						
						$this->message .= '<div class="alert alert-success">';
							
							$this->message .= 'Thanks for purchasing the '.$this->data['name'].'!';

						$this->message .= '</div>';						
					}
					
					include( $this->parent->views . $this->parent->_dev .'/message.php' );
				}
			}
			else{
				
				echo 'Wrong plan request...';
				Exit;
			}
		}		
	}
	
	/**
	 * Main LTPLE_Client_Plan Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Stars is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Stars instance
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
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Plan {
	
	var $parent;
	var $key;
	var $subscribed;
	var $data;
	var $message;
	var $fields;
	var $subscription_plans	= NULL;
	var $layer_options		= array();
	var $user_plans			= array();
	var $layerOptions 		= NULL;
	var $buttons 			= array();
	var $shortcode 			= '';
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		$this->parent->register_post_type( 'subscription-plan', __( 'Subscription Plans', 'live-template-editor-client' ), __( 'Subscription Plan', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu'		 	=> 'subscription-plan',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'plan'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_post_type( 'user-plan', __( 'User Plans', 'live-template-editor-client' ), __( 'User Plans', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-plan',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post'
		));
		
		add_action( 'add_meta_boxes', function(){
		
			$this->parent->admin->add_meta_box (
			
				'plan_options',
				__( 'Plan options', 'live-template-editor-client' ), 
				array("subscription-plan"),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'userPlanValue',
				__( 'Plan Info', 'live-template-editor-client' ), 
				array("user-plan"),
				'advanced'
			);
		});
		
		// add user-plan
		
		add_filter("user-plan_custom_fields", array( $this, 'add_user_plan_fields' ));		
		
		add_action( 'init', array( $this, 'init_plan' ));
	}

	public function init_plan(){
		
		if( !is_admin() ){
		
			add_shortcode('subscription-plan', array( $this, 'get_subscription_plan_shortcode' ) );
		}
		else{

			// add user taxonomy custom fields
			
			add_action( 'show_user_profile', array( $this, 'get_user_plan_and_pricing' ),2,10 );
			add_action( 'edit_user_profile', array( $this, 'get_user_plan_and_pricing' ) );
			
			// save user taxonomy custom fields
			
			add_action( 'personal_options_update', array( $this, 'save_custom_user_taxonomy_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_taxonomy_fields' ) );
			
			// add subscription-plan
			
			add_filter("subscription-plan_custom_fields", array( $this, 'get_subscription_plan_fields' ));	
							
			add_filter('manage_subscription-plan_posts_columns', array( $this, 'set_subscription_plan_columns'));
			add_action('manage_subscription-plan_posts_custom_column', array( $this, 'add_subscription_plan_column_content'), 10, 2);
			add_filter('nav_menu_css_class', array( $this, 'change_subscription_plan_menu_classes'), 10,2 );
		}
	}	
	
	// Add user plan data custom fields

	public function add_user_plan_fields(){
		
		$fields=[];
		
		$user_plan  = get_post();

		if( !empty($user_plan->post_author) ){
		
			$layer_plan = $this->get_user_plan_info( intval($user_plan->post_author) );
			$fields[]=array(
			
				"metabox" =>
				
					array('name'	=>"userPlanValue"),
					'id'			=>	"userPlanValue",
					'label'			=>	"",
					'type'			=>	'plan_value',
					'plan'			=>	$layer_plan,
					'placeholder'	=>	"Plan Value",
					'description'	=>	''
			);
		}
		
		return $fields;
	}
	
	public function set_subscription_plan_columns($columns){

		// Remove description, posts, wpseo columns
		
		$columns = [];
		
		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['title'] 		= 'Title';
		$columns['cover'] 		= 'Cover';
		$columns['shortcode'] 	= 'Shortcode';
		$columns['date'] 		= 'Date';

		return $columns;		
	}
	
	public function get_thumb_url($post_id){
		
		$thumb_url = get_the_post_thumbnail_url($post_id);
		
		if( empty($thumb_url) ){

			$thumb_url = $this->parent->assets_url . 'images/plan_background.jpg';
		}

		return $thumb_url;
	}
	
	public function add_subscription_plan_column_content($column_name, $post_id){

		if($column_name === 'shortcode') {
			
			echo '<input style="width:200px;" type="text" name="shortcode" value="[subscription-plan id=\'' . $post_id . '\']" ' . disabled( true, true, false ) . ' />';
		}	
		elseif($column_name == 'cover') {
			
			$thumb_url = $this->get_thumb_url($post_id);
			
			echo '<div style="width:250px;">';
				
				echo '<img src="'.$thumb_url.'" style="width:100%;" />';
			
			echo '</div>';
		}		
	}

	public function get_subscription_plan_shortcode( $atts ){
		
		$atts = shortcode_atts( array(
		
			'id'		 		=> NULL,
			'widget' 			=> 'false',
			'title' 			=> NULL,
			'thumb' 			=> false,
			'content' 			=> NULL,
			'button' 			=> NULL,
			'attributes' 		=> true
			
		), $atts, 'subscription-plan' );		
		
		if(!is_null($atts['id'])&&is_numeric($atts['id'])){
			
			$id=intval($atts['id']);
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		='month';
			$total_fee_period		='once';
			$total_price_currency	='$';
			
			$option_name = 'plan_options';

			if( $plan_options = get_post_meta( $id, $option_name, true ) ){
				
				//get plan
				
				$plan = get_post($id);
				
				//get plan title
				
				if(is_string($atts['title'])){
					
					$plan_title = $atts['title'];
				}
				else{
					
					$plan_title = $plan->post_title;
				}
				
				//get plan content
				
				if(is_string($atts['content'])){
					
					$plan_form 		= '';
					$plan_content 	= $atts['content'];
					$style			= 'font-weight: bold;color: rgb(138, 206, 236);';
				}
				else{
					
					$plan_form 		= '';
					$plan_content 	= $plan->post_content;
					$style 			= 'margin-bottom: 0;padding: 30px 30px;font-weight: bold;background: rgba(158, 158, 158, 0.24);color: rgb(138, 206, 236);box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);';
				}

				// get total_price_amount & total_storage
					
				$taxonomies = $this->get_layer_taxonomies_options();

				foreach( $taxonomies as $taxonomy => $terms ) {
					
					$taxonomy_options = [];
					
					foreach($terms as $term){

						if ( in_array( $term->slug, $plan_options ) ) {
							
							$total_price_amount = $this->sum_total_price_amount( $total_price_amount, $term->options, $total_price_period);	
							$total_fee_amount 	= $this->sum_total_price_amount( $total_fee_amount, $term->options, $total_fee_period);				
							$total_storage 		= $this->sum_total_storage( $total_storage, $term->options);

							if( !empty($term->options['form']) && ( count($term->options['form']['input'])>1 || !empty($term->options['form']['name'][0]) ) ){

								if( !empty($_POST['meta_'.$term->slug]) ){
									
									// store data in session
									
									$_SESSION['pm_' . $plan->ID]['meta_'.$term->slug] = $_POST['meta_'.$term->slug];
								}
								else{
									
									$plan_form .= $this->parent->admin->display_field( array(
							
										'type'				=> 'form',
										'id'				=> 'meta_'.$term->slug,
										'name'				=> $term->taxonomy . '-meta',
										'array' 			=> $term->options,
										'action' 			=> '',
										'method' 			=> 'post',
										'description'		=> ''
										
									), false, false );
								}
							}
						}
					}
				}
				
				// round total_price_amount
				
				$total_fee_amount 	= round($total_fee_amount, 2);
				$total_price_amount = round($total_price_amount, 2);
				
				// user has plan

				$user_has_plan = $this->user_has_plan( $plan->ID );
				
				// user plan upgrade

				$plan_upgrade = $this->user_plan_upgrade( $plan->ID );
				
				$total_upgrade = 0;
				
				if(!empty($plan_upgrade)){
					
					foreach($plan_upgrade['now'] as $option => $value){
						
						$total_upgrade += $value;
					}
				}

				// is plan unlocked
				
				$plan_status = 'locked';
				
				if( $this->parent->user->loggedin ){
				
					if( $total_price_amount == 0 && $total_fee_amount == 0 && $user_has_plan === true ){
						
						$plan_status = 'unlocked';
					}
					elseif( $total_price_amount > 0 && $user_has_plan === true ){
						
						$plan_status = 'renew';
					}
					elseif( $total_upgrade > 0 ){
						
						$plan_status = 'upgrade';
					}
				}

				//get plan_data
				
				sort($plan_options);
				ksort($total_storage);
				
				$plan_data=[];
				$plan_data['id'] 		= $plan->ID;
				$plan_data['name'] 		= $plan->post_title;
				$plan_data['options'] 	= $plan_options;
				$plan_data['price'] 	= $total_price_amount;
				$plan_data['fee'] 		= $total_fee_amount;
				$plan_data['currency']	= $total_price_currency;
				$plan_data['period'] 	= $total_price_period;
				$plan_data['fperiod']	= $total_fee_period;
				$plan_data['storage'] 	= $total_storage;
				$plan_data['subscriber']= $this->parent->user->user_email;
				$plan_data['client']	= $this->parent->client->url;
				$plan_data['meta']		= ( !empty($_SESSION['pm_' . $plan->ID]) ? $_SESSION['pm_' . $plan->ID] : '' );
				
				if( $plan_status == 'upgrade' ){
					
					$plan_data['upgrade'] = $plan_upgrade;
					
					// display total upgrade price
				}
				
				$plan_data = esc_attr( json_encode( $plan_data ) );
				
				$plan_key = md5( 'plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email );	

				//get agreement url				
				
				$agreement_url = $this->parent->server->url . '/agreement/?pk='.$plan_key.'&pd='.$this->parent->base64_urlencode($plan_data) . '&_=' . $this->parent->_time;

				//get addon services
				
				$services =[];
				
				$terms = wp_get_post_terms( $plan->ID, 'addon-service');
				
				if( !empty($terms) ){
				
					foreach($terms as $term){
						
						if( $term->parent != '0' ){

							$term_id = $term->parent;
							
							do{
							
								$parent  = get_term_by( 'id', $term_id, 'addon-service');
								
								$term_id = $parent->parent;
							}
							while($parent->parent != '0');
						}
						else{
							
							$parent = $term;
						}

						$services[$parent->name][] = $term;
					}
				}
				
				//get subscription plan
				
				$iframe_height 	= 500;
				
				$this->shortcode = '';
				
				if( !is_null($atts['widget']) && $atts['widget']==='true' ){
					
					if( !empty($services) ){
						
						$this->shortcode .= '<div class="row panel-body" style="background:#fff;">';
						
							$this->shortcode .= '<div class="col-xs-12 col-md-6">';
								
								$this->shortcode .= '<div class="page-header" style="margin-top:10px;">';
								
									$this->shortcode .= '<h2>Addon Services</h2>';
									
								$this->shortcode .= '</div>';
								
								$this->shortcode .= '<form>';
									
									foreach($services as $parent => $terms){
										
										$this->shortcode .= '<div class="panel panel-default">';
											
											$this->shortcode .= '<div class="panel-heading">';
											
												$this->shortcode .= '<b>' . $parent . '</b>';
												
											$this->shortcode .= '</div>';
											
											$this->shortcode .= '<div class="panel-body">';
												
												foreach($terms as $term){
												
													$this->shortcode .= '<span>';
														
														$this->shortcode .= '<input class="" type="checkbox" name="addon-services[]" value="' . $term->term_id . '">';
														
														$this->shortcode .= ' ' . ucfirst($term->name);
													
													$this->shortcode .= '</span>';
												}
												
											$this->shortcode .= '</div>';
											
										$this->shortcode .= '</div>';
									}
									
								$this->shortcode .= '</form>';
								
							$this->shortcode .= '</div>';
							
						$this->shortcode .= '</div>';							
					}
					elseif( !empty($plan_form) ){
						
						$this->shortcode .= '<div class="row panel-body" style="background:#fff;">';
						
							$this->shortcode .= '<div class="col-xs-12 col-md-6">';

								$this->shortcode .= $plan_form;

							$this->shortcode .= '</div>';
							
						$this->shortcode .= '</div>';						
					}
					else{

						$this->shortcode .= '<iframe src="'.$agreement_url.'" style="width:100%;bottom: 0;border:0;height:' . ($iframe_height - 10 ) . 'px;overflow: hidden;"></iframe>';													
					}
				}
				else{
										
					$this->shortcode .='<h2 id="plan_title" style="'.$style.'">' . $plan_title . '</h2>';
					
					if(!empty($_SESSION['message'])){ 
					
						//output message
					
						$this->shortcode .= $_SESSION['message'];
						
						$_SESSION['message'] = '';
					}						
					elseif(!empty($this->message)){ 
					
						//output message
					
						$this->shortcode .= $this->message;
					}				
					
					if( $atts['thumb'] ){
					
						if( $plan_thumb = get_the_post_thumbnail_url($plan->ID) ){
							
							$this->shortcode .='<div id="plan_thumb">';
								
								$this->shortcode .= '<img src="'.$plan_thumb.'" style="width:100%;">';
							
							$this->shortcode .='</div>';
						}
						else{

							$this->shortcode .='<div id="plan_thumb" style="background-size:cover;background-repeat: no-repeat;background-position: center center;width:100%;height:200px;background-image:url(\''.$this->parent->assets_url . 'images/plan_background.jpg'.'\');"></div>';
						}
					}					

					$this->shortcode .='<div id="plan_form">';
						
						if( !empty($plan_content) ){
						
							$this->shortcode .='<div class="well text-left">';
							
								$this->shortcode .= $plan_content;
							
							$this->shortcode .='</div>';
						}
						
						$this->shortcode .='<div>'.PHP_EOL;

							// Output iframe
							
							if($atts['attributes']===true){
							
								$this->shortcode .= '<div id="plan_storage" style="display:block;">';				
									
									foreach($total_storage as $storage_unit => $total_storage_amount){
										
										if($total_storage_amount > 0 ){
											
											$this->shortcode .='<span style="display:block;">';
											
												if($storage_unit=='templates' ){
													
													if( $total_storage_amount == 1 ){
														
														$this->shortcode .= '+' . $total_storage_amount.' saved project';
													}
													else{
														
														$this->shortcode .= '+' . $total_storage_amount.' saved projects';
													}
												}
												else{
													
													$this->shortcode .= '+' . $total_storage_amount.' saved '.$storage_unit;
												}
												
											$this->shortcode .='</span>';
										}
									}

								$this->shortcode .= '</div>';
								
								do_action('ltple_plan_shortcode_attributes',$taxonomies,$plan_options);
								
								$this->shortcode .='<hr id="plan_hr" style="display:block;"></hr>';
							}
							
							$this->shortcode .= '<div id="plan_price">';				
								
								if( $total_fee_amount > 0 ){
									
									$this->shortcode .= htmlentities(' ').$total_fee_amount.$total_price_currency.' '. ( $total_fee_period == 'once' ? 'one time fee' : $total_fee_period );
									
									if($total_price_amount > 0 ){
										
										$this->shortcode .= '<br>+';
									}
								}
								
								if($total_price_amount > 0 ){
								
									$this->shortcode .= $total_price_amount.$total_price_currency.' / '.$total_price_period;
								}
								elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
									
									$this->shortcode .= 'Free';
								}
								
							$this->shortcode .= '</div>';
							
							$this->shortcode .= '</br>';
							
							do_action('ltple_plan_shortcode_value',$taxonomies,$plan_options);
							
							$this->shortcode .= '<div id="plan_button" ' . ( !empty($plan_content) ? 'style="padding-bottom:40px;"' : '' ) . '>';				
								
								$this->shortcode .='<span class="payment-errors"></span>'.PHP_EOL;

								if( $plan_status == 'unlocked' ){
									
									$this->shortcode .='<a class="btn btn-info btn-lg" href="' . $this->parent->urls->current . '">'.PHP_EOL;
								
										$this->shortcode .='Unlocked'.PHP_EOL;
								
									$this->shortcode .='</a>'.PHP_EOL;
								}
								else{
									
									// get addon buttons
									
									do_action( 'ltple_plan_shortcode', $plan->ID );
									
									if(!empty($this->buttons[$plan->ID])){
										
										$this->shortcode .= reset($this->buttons[$plan->ID]).PHP_EOL;
									}
									else{
									
										$modal_id='modal_'.md5($agreement_url);
										
										if( $plan_status == 'renew' ){
											
											$this->shortcode .='<button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
											
												$this->shortcode .='Renew'.PHP_EOL;

											$this->shortcode .='</button>'.PHP_EOL;									
										}
										else{
											
											$this->shortcode .='<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
												
												if(!empty($atts['button'])){
													
													$this->shortcode .= ucfirst($atts['button']).PHP_EOL;
												}
												elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
													
													$this->shortcode .='Start'.PHP_EOL;
												}
												elseif($total_price_amount == 0 && $total_fee_amount > 0 ){
													
													$this->shortcode .='Order'.PHP_EOL;
												}
												elseif( $plan_status == 'upgrade' ){
													
													$this->shortcode .='Upgrade'.PHP_EOL;
												}
												else{
													
													$this->shortcode .='Subscribe'.PHP_EOL;
												}

											$this->shortcode .='</button>'.PHP_EOL;									
										}

										$this->shortcode .='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
											
											$this->shortcode .='<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
												
												$this->shortcode .='<div class="modal-content">'.PHP_EOL;
												
													$this->shortcode .='<div class="modal-header">'.PHP_EOL;
														
														$this->shortcode .='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
														
														$this->shortcode .= '<h4 class="modal-title" id="myModalLabel">';
														
															$this->shortcode .= $plan->post_title;
															
															if( $total_price_amount > 0 && $plan_status != 'upgrade' ){
															
																$this->shortcode .= ' (' . $total_price_amount . $total_price_currency.' / '.$total_price_period.')'.PHP_EOL;
															}
														
														$this->shortcode .= '</h4>'.PHP_EOL;
													
													$this->shortcode .='</div>'.PHP_EOL;

													if( $this->parent->user->loggedin ){
														
														$this->shortcode .= '<div class="loadingIframe" style="height: 50px;width: 100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

														$this->shortcode .= '<iframe data-src="' . get_permalink( $plan->ID ) . '?output=widget'.'" style="width: 100%;position:relative;top:-50px;margin-bottom:-60px;bottom: 0;border:0;height:'.$iframe_height.'px;overflow: hidden;"></iframe>';
													}
													else{
														
														$this->shortcode .='<div class="modal-body">'.PHP_EOL;
														
															$this->shortcode .= '<div style="font-size:20px;padding:20px;margin:0px;" class="alert alert-warning">';
																
																$this->shortcode .= 'You need to log in first...';
																
																$this->shortcode .= '<div class="pull-right">';

																	$this->shortcode .= '<a style="margin:0 2px;" class="btn-lg btn-success" href="' . wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) . '">Login</a>';
																	
																	$this->shortcode .= '<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'&action=register">Register</a>';
																
																$this->shortcode .= '</div>';
																
															$this->shortcode .= '</div>';
														
														$this->shortcode .='</div>'.PHP_EOL;
													}

												$this->shortcode .='</div>'.PHP_EOL;
												
											$this->shortcode .='</div>'.PHP_EOL;
											
										$this->shortcode .='</div>'.PHP_EOL;
									}
								}
								
							$this->shortcode .= '</div>'.PHP_EOL;
							
						$this->shortcode .='</div>'.PHP_EOL;
					$this->shortcode .='</div>'.PHP_EOL;						
				}
			}
		}		
		
		return $this->shortcode;
	}
	
	public function get_layer_taxonomies_options(){
		
		if( is_null($this->layerOptions) ){
			
			//get custom taxonomies
			
			$taxonomies = $this->get_plan_taxonomies();

			// get custom taxonomies options
			
			$this->layerOptions = [];
			
			foreach( $taxonomies as $taxonomy ){
			
				//get custom taxonomy terms
				
				$terms = get_terms( array(
						
					'taxonomy' 		=> $taxonomy['taxonomy'],
					'hide_empty' 	=> false
				));

				foreach($terms as $term){
					
					// get term options
					
					$term->options = $this->parent->layer->get_options( $term->taxonomy, $term );				

					// add to array
					
					$this->layerOptions[$taxonomy['name']][] = $term;
				}
			}
		}

		return 	$this->layerOptions;	
	}
	
	public function get_subscription_plan_fields(){
			
		$this->fields = [];
		
		//get options
		
		$options = $this->get_layer_taxonomies_options();
		
		$this->fields[]=array(
		
			"metabox" =>
				array('name'	=> 'plan_options'),
				'type'			=> 'checkbox_multi_plan_options',
				'id'			=> 'plan_options',
				'label'			=> '',
				'options'		=> $options,
				'description'	=> ''
		);
		
		// get email models
		
		$q = get_posts(array(
		
			'post_type'   => 'email-model',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' 	  => 'title',
			'order' 	  => 'ASC'
		));
		
		$email_models=['' => 'no email model selected'];
		
		if(!empty($q)){
			
			foreach( $q as $model ){
				
				$email_models[$model->ID] = $model->post_title;
			}
		}
		
		$this->fields[]=array(
		
			"metabox" =>
				array('name'=> "email_series"),
				'type'				=> 'email_series',
				'id'				=> 'email_series',
				'label'				=> '',
				'email-models' 		=> $email_models,
				'model-selected'	=> '',
				'days-from-sub' 	=> 0,
				'description'		=> ''
		);
		
		do_action("add_subscription_plan_fields");
		
		return $this->fields;
	}
	
	
	public function sum_total_price_amount( &$total_price_amount=0, $options, $period='month'){
		
		if($period == $options['price_period']){
			
			$total_price_amount = $total_price_amount + floatval($options['price_amount']);
		}
		elseif($period == 'month'){
			
			if($options['price_period'] == 'day'){
				
				$total_price_amount = $total_price_amount + ( 30 * floatval($options['price_amount']) );
			}
			elseif($options['price_period'] == 'year'){
				
				$total_price_amount = $total_price_amount + (  floatval($options['price_amount']) / 12 );
			}
		}
		
		return $total_price_amount;
	}
	
	
	public function sum_total_storage( &$total_storage=[], $options){
		
		$storage_unit 	= $options['storage_unit'];
		$storage_amount = round(intval($options['storage_amount']),0);
		
		if(!isset($total_storage[$storage_unit])){
			
			$total_storage[$storage_unit] = $storage_amount;
		}
		else{
			
			$total_storage[$storage_unit]= $total_storage[$storage_unit] + $storage_amount;
		}
		
		return $total_storage;
	}
	
	public function is_parent_in_plan($user_plan_id, $taxonomy, $parent_id){
		
		// check parent
		
		$in_plan = is_object_in_term( $user_plan_id, $taxonomy, $parent_id );
		
		
		if( !$in_plan && $parent_id > 0 ){
			
			// check parent of parent
			
			while( !$in_plan && $parent_id > 0 ){
				
				$parent = get_term($parent_id);
				
				$parent_id = $parent->parent;
				
				if( $parent_id > 0 ){
				
					$in_plan = is_object_in_term( $user_plan_id, $taxonomy, $parent_id );
				}
			}
		}
		
		
		return $in_plan;
	}
	
	public function get_user_plan_and_pricing( $user, $context='admin-dashboard' ) {
		
		if( current_user_can( 'administrator' ) ){
				
			$user_plan_id = $this->get_user_plan_id( $user->ID, true );
			
			$taxonomies = $this->get_layer_taxonomies_options();
			
			$user_plan_options = array();
			
			foreach ( $taxonomies as $taxonomy => $terms ) {	
			
				foreach ( $terms as $term ) {
					
					if( is_object_in_term( $user_plan_id, $term->taxonomy, $term->term_id ) ){
						
						$user_plan_options[] = $term->slug;
					}
				}
			}			

			echo '<div class="postbox">';
				
				echo '<h3 style="margin:10px;">' . __( 'Plan & Pricing', 'live-template-editor-client' ) . '</h3>';
			
				echo $this->parent->admin->display_field( array(
				
					'type'			=> 'checkbox_multi_plan_options',
					'id'			=> $this->parent->_base . 'user_plan_options',
					'options'		=> $taxonomies,
					'data'			=> $user_plan_options,
					'description'	=> ''
				));
				
			echo'</div>';				

			//get list of emails sent to user
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Emails sent', 'live-template-editor-client' ) . '</h3>';
				
				$emails = get_user_meta($user->ID, $this->parent->_base . '_email_sent', true);
				
				if( !empty($emails) ){
					
					$emails = json_decode($emails,true);
					
					echo '<ul style="padding-left:10px;">';
					
						foreach($emails as $slug => $time){
							
							echo '<li>';
							
								echo date( 'd/m/y', $time) . ' - ' . ucfirst(str_replace('-',' ',$slug));
							
							echo '</li>';
						}
					
					echo '</ul>';
				}				
				
			echo'</div>';			
		}	
	}
	
	public function get_plan_taxonomies(){
		
		$taxonomies=[];
		$taxonomies[] = array(
			'name'		=> 'Layer Types',
			'taxonomy'	=> 'layer-type',
			'hierarchical' => false
		);
		$taxonomies[] = array(
			'name'		=> 'Layer Ranges',
			'taxonomy'	=> 'layer-range',
			'hierarchical' => true
		);
		$taxonomies[] = array(
			'name'		=> 'Account Options',
			'taxonomy'	=> 'account-option',
			'hierarchical' => false
		);

		return $taxonomies;
	}
	
	public function get_user_plan_id( $user_id, $create=false ){	
	
		// get user plan id
	
		$q = get_posts(array(
		
			'author'      => $user_id,
			'post_type'   => 'user-plan',
			'post_status' => 'publish',
			'numberposts' => 1
		));
		
		if(!empty($q)){
			
			$user_plan_id = $q[0]->ID;
		}		
		elseif($create===true){
			
			$user_plan_id = wp_insert_post(array(
			
			  'post_title'   	 => wp_strip_all_tags( 'user_' . $user_id ),
			  'post_status'   	=> 'publish',
			  'post_type'  	 	=> 'user-plan',
			  'post_author'   	=> $user_id
			));
		}
		else{
			
			$user_plan_id = 0;
		}
		
		return $user_plan_id;
	}	

	public function update_user(){
		
		// get plan subscription
		
		if( !empty( $this->parent->user->ID ) && isset($_GET['pk'])&&isset($_GET['pd'])&&isset($_GET['pv'])){

			$plan_data = sanitize_text_field($_GET['pd']);
			$plan_data = $this->parent->base64_urldecode($plan_data);

			$this->key 			= sanitize_text_field($_GET['pk']);
			$this->subscribed 	= sanitize_text_field($_GET['pv']);
			
			// subscribed plan data
			
			if( $this->key == md5('plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email ) && $this->subscribed == md5('subscribed'.$_GET['pd'] . $this->parent->_time . $this->parent->user->user_email ) ){
				
				$plan_data = html_entity_decode($plan_data);
				
				$this->data = json_decode($plan_data,true);
					
				if(!empty($this->data['name'])){
					
					do_action('ltple_update_user_plan');
					
					if( !empty($this->data['options']) ){
							
						$taxonomies 			= $this->get_layer_taxonomies_options();
						$user_has_subscription 	= 'false';
						$all_updated_terms 		= [];
						
						foreach( $taxonomies as $taxonomy => $terms ) {
							
							$update_terms 		= [];
							$update_taxonomy 	= '';
							
							foreach($terms as $i => $term){

								if ( in_array( $term->slug, $this->data['options'] ) ) {
									
									$update_terms[]		= $term->term_id;
									$update_taxonomy 	= $term->taxonomy;
									
									if( $this->data["price"] > 0 ){
										
										$user_has_subscription = 'true';
									}
									
									$all_updated_terms[] = $term->slug;
								}
							}

							// update current user custom taxonomy
							
							$user_plan_id = $this->get_user_plan_id( $this->parent->user->ID, true );
							
							$append = false;

							if( $this->data['price'] == 0 || !empty($this->data['upgrade']) ){
								
								// demo, upgrade or donation case
								
								$append = true;
							}

							$response = wp_set_object_terms( $user_plan_id, $update_terms, $update_taxonomy, $append );

							clean_object_term_cache( $user_plan_id, $update_taxonomy );
						}
						
						// hook triggers
						
						if( intval($this->data['price']) > 0 ){
							
							do_action('ltple_paid_plan_subscription');
						}
						else{
							
							do_action('ltple_free_plan_subscription');
						}
						
						do_action('ltple_plan_subscribed');
						
						// send subscription summary email
						
						$this->parent->email->send_subscription_summary( $this->parent->user, $this->data['id'] );

						// schedule email series
						
						$this->parent->email->schedule_campaign( $this->data['id'], $this->parent->user);
						
						if( $this->data['price'] > 0 ){
							
							//send admin notification
								
							wp_mail($this->parent->settings->options->emailSupport, 'Plan edited on checkout - user id ' . $this->parent->user->ID . ' - ip ' . $this->parent->request->ip, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($all_updated_terms,true) . PHP_EOL . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);						
							
							// update user has subscription						
							
							update_user_meta( $this->parent->user->ID , 'has_subscription', $user_has_subscription);
							
							// update period end
							
							//$this->parent->users->update_periods();
							
							wp_remote_request( $this->parent->urls->home . '/?ltple_update=periods', array(
								
								'method' 	=> 'GET',
								'timeout' 	=> 100,
								'blocking' 	=> false
							));
							
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
					}
					
					include( $this->parent->views . '/message.php' );
				}
			}
			else{
				
				echo 'Wrong plan request...';
				Exit;
			}
		}	
	}

	public function bulk_update_user_plan($users,$plan_id){
		
		$option_name='plan_options';
		
		if($data = get_post_meta( $plan_id, $option_name, true )){
			
			$options 				= $this->get_layer_taxonomies_options();
			$all_updated_terms 		= [];
			
			foreach( $options as $taxonomy => $terms ) {
				
				$update_terms=[];
				$update_taxonomy='';
				
				foreach($terms as $i => $term){

					if ( in_array( $term->slug, $data ) ) {
						
						$update_terms[] 	= $term->term_id;
						$update_taxonomy 	= $term->taxonomy;
						
						$all_updated_terms[]= $term->slug;
					}
				}
				
				foreach( $users as $i => $user_id){

					// update current user custom taxonomy
					
					if( $user_plan_id = $this->get_user_plan_id( $user_id, true ) ){
					
						$response = wp_set_object_terms( $user_plan_id, $update_terms, $update_taxonomy, true );

						clean_object_term_cache( $user_plan_id, $update_taxonomy );
					}
				}
			}
			
			foreach( $users as $i => $user_id){
			
				// get user
				
				$user = get_user_by('id',$user_id);
			
				// send subscription summary email
				
				$this->parent->email->send_subscription_summary( $user, $plan_id );

				// schedule email series
			
				$this->parent->email->schedule_campaign( $plan_id, $user );
			
				/*
				
				//	TODO set has_subscription
				
				if( $price > 0 ){

					// update user has subscription		

									
					
					update_user_meta( $user_id , 'has_subscription', 'true');
				}
				*/			
			}
		}
	}
	
	public function bulk_update_user_type($users,$term_id){
			
		if( !empty($users) ){
			
			$taxonomy = 'layer-type';
			
			foreach( $users as $user_id ){
				
				// update current user custom taxonomy
				
				if( $user_plan_id = $this->parent->plan->get_user_plan_id( $user_id, true ) ){
				
					$response = wp_set_object_terms( $user_plan_id, array($term_id), $taxonomy, true );

					clean_object_term_cache( $user_plan_id, $taxonomy );
				}
			}
		}
	}

	public function bulk_update_user_range($users,$term_id){
			
		if( !empty($users) ){
			
			$taxonomy = 'layer-range';
			
			foreach( $users as $user_id ){
				
				// update current user custom taxonomy
				
				if( $user_plan_id = $this->parent->plan->get_user_plan_id( $user_id, true ) ){
				
					$response = wp_set_object_terms( $user_plan_id, array($term_id), $taxonomy, true );

					clean_object_term_cache( $user_plan_id, $taxonomy );
				}
			}
		}
	}
	
	public function bulk_update_user_option($users,$term_id){
			
		if( !empty($users) ){
			
			$taxonomy = 'account-option';
			
			foreach( $users as $user_id ){
				
				// update current user custom taxonomy
				
				if( $user_plan_id = $this->parent->plan->get_user_plan_id( $user_id, true ) ){
				
					$response = wp_set_object_terms( $user_plan_id, array($term_id), $taxonomy, true );

					clean_object_term_cache( $user_plan_id, $taxonomy );
				}
			}
		}
	}
	
	public function get_subscription_plans(){	
		
		if( is_null($this->subscription_plans) ){
			
			$this->subscription_plans = array();
			
			if( $plans = get_posts(array(
			
				'post_type' 	=> 'subscription-plan',
				'post_status' 	=> 'publish',
				'numberposts' 	=> -1,
			)) ){
				
				$taxonomies = $this->get_layer_taxonomies_options();
				
				foreach( $plans as $plan ){
					
					$plan_id = $plan->ID;
					$options = get_post_meta( $plan_id, 'plan_options', true );
					
					$this->subscription_plans[$plan_id]['id'] 		= $plan_id;
					$this->subscription_plans[$plan_id]['options'] 	= $options;
				
					$this->subscription_plans[$plan_id]['info']['total_price_amount'] 	= 0;
					$this->subscription_plans[$plan_id]['info']['total_fee_amount'] 	= 0;
					$this->subscription_plans[$plan_id]['info']['total_price_period'] 	= 'month';
					$this->subscription_plans[$plan_id]['info']['total_fee_period'] 	= 'once';
					$this->subscription_plans[$plan_id]['info']['total_price_currency']	= '$';
					
					foreach( $taxonomies as $taxonomy => $terms ){
						
						foreach( $terms as $term ){
							
							if( in_array($term->slug,$options) ){
							
								// add children terms
								
								if( $children = get_term_children( $term->term_id, $term->taxonomy) ){
							
									foreach( $children as $child_id ){
										
										$child = get_term_by( 'id', $child_id, $term->taxonomy );
										
										$this->subscription_plans[$plan_id]['options'][] = $child->slug;
									}
								}
							
								// sum values
							
								$this->subscription_plans[$plan_id]['info']['total_fee_amount']		= $this->sum_total_price_amount( $this->subscription_plans[$plan_id]['info']['total_fee_amount'], $term->options, $this->subscription_plans[$plan_id]['info']['total_fee_period'] );
								$this->subscription_plans[$plan_id]['info']['total_price_amount'] 	= $this->sum_total_price_amount( $this->subscription_plans[$plan_id]['info']['total_price_amount'], $term->options, $this->subscription_plans[$plan_id]['info']['total_price_period'] );
								$this->subscription_plans[$plan_id]['info']['total_storage'] 	 	= $this->sum_total_storage( $this->subscription_plans[$plan_id]['info']['total_storage'], $term->options);
							}
						}
					}
				}
			}
		}
		
		return $this->subscription_plans;
	}
	
	public function get_layer_options( $item_id ){	
		
		if( !isset($this->layer_options[$item_id]) ){
			
			$plan_taxonomies = $this->get_plan_taxonomies();

			$this->layer_options[$item_id] = [];
			
			$this->layer_options[$item_id]['id'] = $item_id;
			
			foreach($plan_taxonomies as $i => $t){
				
				$taxonomy 		 = $t['taxonomy'];
				$taxonomy_name 	 = $t['name'];
				$is_hierarchical = $t['hierarchical'];
				
				$this->layer_options[$item_id]['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
				$this->layer_options[$item_id]['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
				$this->layer_options[$item_id]['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
				$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms']			= [];
				
				$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
				
				if ( !empty($terms) ) {
					
					foreach ( $terms as $term ) {						

						$term_slug = $term->slug;
						
						$has_term = $in_term = is_object_in_term( $item_id, $taxonomy, $term->term_id );

						if($is_hierarchical === true && $term->parent > 0 && $has_term === false ){
							
							$parent_id = $term->parent;
							
							while( $parent_id > 0 ){
								
								if($has_term === false){
									
									foreach($terms as $parent){
										
										if( $parent->term_id == $parent_id ){
											
											$has_term = is_object_in_term( $item_id, $taxonomy, $parent->term_id );
											
											$parent_id = $parent->parent;
											
											break;
										}
									}								
								}
								else{
									
									break;
								}
							}					
						}
						
						// push terms
					
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]			= $term_slug;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]			= $term->term_id;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]= $term->term_taxonomy_id;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		= $term->taxonomy;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	 	= $term->description;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			= $term->count;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
						$this->layer_options[$item_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]		= $has_term;					
					}
				}
			}
		}
		
		return $this->layer_options[$item_id];	
	}
	
	public function get_user_plan_info( $user_id ){	
		
		if( !isset($this->user_plans[$user_id]) ){
		
			$user_plan_id 	= $this->get_user_plan_id( $user_id );
			$taxonomies 	= $this->get_plan_taxonomies();

			$this->user_plans[$user_id] = [];
			
			$this->user_plans[$user_id]['id'] = $user_plan_id;
			
			$this->user_plans[$user_id]['info']['total_price_amount'] 	= 0;
			$this->user_plans[$user_id]['info']['total_fee_amount'] 	= 0;
			$this->user_plans[$user_id]['info']['total_price_period'] 	= 'month';
			$this->user_plans[$user_id]['info']['total_fee_period'] 	= 'once';
			$this->user_plans[$user_id]['info']['total_price_currency'] = '$';
			
			foreach($taxonomies as $i => $t){
				
				$taxonomy 		 = $t['taxonomy'];
				$taxonomy_name 	 = $t['name'];
				$is_hierarchical = $t['hierarchical'];
				
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms']			= [];
				
				$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
				
				if ( !empty($terms) ) {
					
					foreach ( $terms as $term ) {						

						$term_slug = $term->slug;
						
						$has_term = $in_term = false;
						
						if($user_plan_id > 0 ){
							
							$has_term = $in_term = is_object_in_term( $user_plan_id, $taxonomy, $term->term_id );
						}
						
						if($is_hierarchical === true && $term->parent > 0 && $has_term === false ){
							
							$parent_id = $term->parent;
							
							while( $parent_id > 0 ){
								
								if($has_term === false){
									
									foreach($terms as $parent){
										
										if( $parent->term_id == $parent_id ){
											
											if($user_plan_id > 0 ){
												
												$has_term = is_object_in_term( $user_plan_id, $taxonomy, $parent->term_id );
											}
											
											$parent_id = $parent->parent;
											
											break;
										}
									}								
								}
								else{
									
									break;
								}
							}					
						}
						
						// push terms
					
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]			= $term_slug;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]		= $term->term_id;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]= $term->term_taxonomy_id;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		= $term->taxonomy;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	= $term->description;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			= $term->count;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
						$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]		= $has_term;
						
						if( $in_term === true ){
							
							if( empty($term->parent) || !$this->is_parent_in_plan( $user_plan_id, $taxonomy, $term->parent ) ){
							
								$options = $this->parent->layer->get_options( $taxonomy, $term );

								$this->user_plans[$user_id]['info']['total_fee_amount']	 = $this->sum_total_price_amount( $this->user_plans[$user_id]['info']['total_fee_amount'], $options, $this->user_plans[$user_id]['info']['total_fee_period'] );
								$this->user_plans[$user_id]['info']['total_price_amount'] = $this->sum_total_price_amount( $this->user_plans[$user_id]['info']['total_price_amount'], $options, $this->user_plans[$user_id]['info']['total_price_period'] );
								$this->user_plans[$user_id]['info']['total_storage'] 	 = $this->sum_total_storage( $this->user_plans[$user_id]['info']['total_storage'], $options);
							
								do_action('ltple_user_plan_option_total',$user_id,$options);
							}
						}					
					}
				}
			}
			
			// get stored user plan value
			
			$user_plan_value = get_post_meta( $user_plan_id, 'userPlanValue',true );
			
			// compare it with current value
			
			if( $user_plan_value=='' || $this->user_plans[$user_id]['info']['total_price_amount'] != intval($user_plan_value) ){

				update_post_meta( $user_plan_id, 'userPlanValue', $this->user_plans[$user_id]['info']['total_price_amount'] );
			}

			do_action('ltple_user_plan_info',$user_id);
		}

		return $this->user_plans[$user_id];	
	}
	
	public function user_has_layer( $item_id, $layer_type = 'cb-default-layer' ){
		
		$user_has_layer = false;
		
		if( $this->parent->user->is_admin ){
			
			$user_has_layer = true;
		}
		elseif( $layer_type == 'cb-default-layer' ){
			
			if( !$this->parent->user->is_editor ){
				
				// get tailored user id

				$layer_user_id = intval(get_post_meta( $item_id, 'layerUserId',true ));

				if( $layer_user_id == 0 || $layer_user_id == $this->parent->user->ID ){

					// get layer plan
					
					$layer_plan = $this->get_layer_options( $item_id );
					
					foreach($layer_plan['taxonomies'] as $taxonomy => $tax){

						foreach($tax['terms'] as $term_slug => $term){
							
							if( $term['has_term']===true ){
								
								$user_has_layer = true;
								
								if( !isset( $this->parent->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug] ) ){
									
									$user_has_layer = false;
									break 2;
								}
								elseif( $this->parent->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug]['has_term'] !== $term['has_term'] ){
									
									$user_has_layer = false;
									break 2;
								}				
							}
						}		
					}	
				}
			}
			else{
				
				$user_has_layer = true;
			}
		}
		elseif( $layer_type == 'user-layer' ){
			
			$user_has_layer = true;
		}
		
		return $user_has_layer;
	}	
	
	public function user_has_plan( $plan_id ){
		
		$user_has_plan = false;
	
		if( !empty($this->parent->user->plan['taxonomies']) ){
			
			$plan_options = get_post_meta( $plan_id, 'plan_options', true );
						
			if(!empty($plan_options)){
				
				$plan_options = array_flip($plan_options);
			
				foreach($this->parent->user->plan['taxonomies'] as $taxonomy => $tax){
					
					foreach($tax['terms'] as $term_slug => $term){

						if(isset($plan_options[$term_slug])){
							
							$user_has_plan = true;

							if( $term['has_term']!==true ){
								
								$user_has_plan = false;
								break 2;
							}
						}
					}			
				}
			}
		}

		return $user_has_plan;
	}

	public function user_plan_upgrade( $plan_id ){
		
		$plan_upgrade = [];
		
		if( $this->parent->user->plan['info']['total_price_amount'] > 0 && !empty($this->parent->user->plan['taxonomies'])){
			
			$total_price_amount = $this->parent->user->plan['info']['total_price_amount'];
			
			$plan_options = get_post_meta( $plan_id, 'plan_options', true );	
				
			if(!empty($plan_options)){
				
				// get new_plan_options

				$plan_options = array_flip($plan_options);
				
				$is_ancestor_upgrade = false;
				
				foreach($this->parent->user->plan['taxonomies'] as $taxonomy => $tax){

					foreach($tax['terms'] as $term_slug => $new_term){

						if( isset($plan_options[$term_slug]) && $new_term['has_term'] !== true ){

							// get new term value
							
							$new_term_value = 0;
							
							$new_term_options = $this->parent->layer->get_options( $taxonomy, $new_term );
							
							if( $new_term_options['price_amount'] > 0 ){
								
								// get  term value
								
								foreach($this->parent->user->plan['taxonomies'][$taxonomy]['terms'] as $curr_term){
									
									if( $curr_term["has_term"] === true ){
										
										if(term_is_ancestor_of( $new_term['term_id'], $curr_term['term_id'], $taxonomy)){
											
											$is_ancestor_upgrade = true;
											
											break;
										}
									}
								}

								if( $is_ancestor_upgrade ){
								
									$new_term_value = $new_term_options['price_amount'] - $this->parent->user->plan['info']['total_price_amount'];
									
									if( $new_term_value == 0 ){
										
										$new_term_value = $new_term_options['price_amount'];
									}
								}
								else{
									
									$new_term_value = $new_term_options['price_amount'];
								}
								
								$total_price_amount += $new_term_value;
							}
							elseif( $new_term_options['price_amount'] < 0 ){
								
								$new_term_value = $new_term_options['price_amount'];
							}
	
							$plan_upgrade['now'][$new_term['slug']] = $new_term_value;
							$plan_upgrade['total'] = $total_price_amount;
						}
					}			
				}
			}		
		}
		
		return $plan_upgrade;
	}
	
	public function get_price_periods(){
		
		$periods=[];
		$periods['day']		='day';
		$periods['month']	='month';
		$periods['year']	='year';
		$periods['once']	='once';
		
		return $periods;
	}
	
	public function get_storage_units(){
		
		$units=[];
		$units['templates']='templates';
		//$units['octet']='octet';
		
		return $units;
	}
	
	public function get_layer_price_fields( $taxonomy_name, $args = [] ){
		
		//get periods
		
		$periods = $this->get_price_periods();
		
		//get price_amount
		
		$price_amount = 0;
		
		if(isset($args['price_amount'])){
			
			$price_amount=$args['price_amount'];
		}

		//get price_period
		
		$price_period = '';
		
		if(isset($args['price_period'])&&is_string($args['price_period'])){
			
			$price_period=$args['price_period'];
		}
		
		//get price_fields
		
		$price_fields='';

		$price_fields.='<div class="input-group">';

			$price_fields.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">$</span>';
			
			$price_fields.='<input type="number" step="0.1" min="-1000" max="1000" placeholder="0" name="'.$taxonomy_name.'-price-amount" id="'.$taxonomy_name.'-price-amount" style="width: 60px;" value="'.$price_amount.'"/>';
			
			$price_fields.='<span> / </span>';
			
			$price_fields.='<select name="'.$taxonomy_name.'-price-period" id="'.$taxonomy_name.'-price-period">';
				
				foreach($periods as $k => $v){
					
					$selected='';
					
					if($k == $price_period){
						
						$selected='selected';
					}
					elseif($price_period=='' && $k=='month'){
						
						$selected='selected';
					}
					
					$price_fields.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$price_fields.='</select>';					
			
		$price_fields.='</div>';
		
		$price_fields.='<p class="description">The '.str_replace(array('-','_'),' ',$taxonomy_name).' price used in table pricing & plans </p>';
		
		return $price_fields;
	}
	
	public function get_layer_storage_fields( $taxonomy_name, $args = [] ){

		//get storage units
		
		$storage_units = $this->get_storage_units();	
	
		//get storage_amount
		
		$storage_amount = 0;
		
		if(isset($args['storage_amount'])){
			
			$storage_amount=$args['storage_amount'];
		}

		//get storage_unit
		
		$storage_unit = '';
		
		if(isset($args['storage_unit'])&&is_string($args['storage_unit'])){
			
			$storage_unit=$args['storage_unit'];
		}
	
		$storage_field = '';
		
		$storage_field.='<div class="input-group">';

			$storage_field.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">+</span>';
			
			$storage_field.='<input type="number" step="1" min="-10" max="10" placeholder="0" name="'.$taxonomy_name.'-storage-amount" id="'.$taxonomy_name.'-storage-amount" style="width: 50px;" value="'.$storage_amount.'"/>';
			
			$storage_field.='<select name="'.$taxonomy_name.'-storage-unit" id="'.$taxonomy_name.'-storage-unit">';
				
				foreach($storage_units as $k => $v){
					
					$selected='';
					
					if($k == $storage_unit){
						
						$selected='selected';
					}
					elseif($storage_unit=='' && $k=='templates'){
						
						$selected='selected';
					}
					
					$storage_field.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$storage_field.='</select>';	
			
		$storage_field.='</div>';
		
		$storage_field.='<p class="description">The amount of additional user account storage</p>';
		
		return $storage_field;		
	}
	
	public function save_custom_user_taxonomy_fields( $user_id ) {
		
		if ( !current_user_can( 'administrator', $user_id ) )
			return false;		
		
		if( isset($_POST[$this->parent->_base . 'user_plan_options']) ){
			
			$user_has_subscription = 'false';
			
			$all_updated_terms = [];
			
			if( !empty($_POST[$this->parent->_base . 'user_plan_options']) ){
				
				$taxonomies = $this->get_layer_taxonomies_options();
				
				if( !empty($taxonomies) ){
					
					$user_plan_id = $this->get_user_plan_id( $user_id );
					
					foreach($taxonomies as $taxonomy => $terms ){

						$term_ids = [];
						
						foreach( $terms as $term ){
							
							if( in_array($term->slug,$_POST[$this->parent->_base . 'user_plan_options'])){
								
								$term_ids[] = $term->term_id;
								
								$all_updated_terms[] = $term->name;

								$user_has_subscription = 'true';					
							}
						}
						
						wp_set_object_terms( $user_plan_id, $term_ids, $term->taxonomy );

						clean_object_term_cache( $user_plan_id, $term->taxonomy );				
					}
				}
			}
			
			update_user_meta( $user_id , 'has_subscription', $user_has_subscription);
			
			//send admin notification
								
			//wp_mail($this->parent->settings->options->emailSupport, 'Plan edited from dashboard - user id ' . $user_id . ' - ip ' . $this->parent->request->ip, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($all_updated_terms,true) . PHP_EOL  . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);
		}
	}
	
	public function unlock_output_request( $for = '+1 hour' ){

		//get plan_data
		
		$plan_data 				 = [];
		$plan_data['name'] 		 = 'unlock output';
		$plan_data['for'] 		 = $for;
		$plan_data['subscriber'] = $this->parent->user->user_email;
		$plan_data['client']	 = $this->parent->client->url;

		$plan_data=esc_attr( json_encode( $plan_data ) );
		
		$plan_key = md5( 'plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email );	

		//get agreement url				
		
		$agreement_url = $this->parent->server->url . '/agreement/?pk='.$plan_key.'&pd='.$this->parent->base64_urlencode($plan_data) . '&_=' . $this->parent->_time;

		$reponse = wp_remote_post($agreement_url);

		if( !empty($reponse['body']) ){

			$_SESSION['message'] = '<div class="alert alert-success"><b>Congratulations</b> you have successfully unlocked the output for '.$for.'</div>';
		}
		else{
			
			$_SESSION['message'] = '<div class="alert alert-warning">Error unlocking the output...</div>';
		}
		
		if( !empty($_GET['ref']) ){
			
			wp_redirect( $this->parent->request->proto . $_GET['ref'] );
			exit;
		}
	}
	
	/**
	 * Main LTPLE_Client_Plan Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Plan is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Plan instance
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

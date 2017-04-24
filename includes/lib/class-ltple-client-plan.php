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
			'hierarchical' 			=> true,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail','page-attributes'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_post_type( 'user-plan', __( 'User Plans', 'live-template-editor-client' ), __( 'User Plans', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-plan',
			'show_in_nav_menus' 	=> true,
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
		
		add_filter("user-plan_custom_fields", array( $this, 'add_user_plan_custom_fields' ));		
		
		add_action( 'init', array( $this, 'init_plan' ));
	}

	public function init_plan(){
		
		if( !is_admin() ){
		
			add_shortcode('subscription-plan', array( $this, 'get_subscription_plan_shortcode' ) );
		}
		else{

			// add user taxonomy custom fields
			
			add_action( 'show_user_profile', array( $this, 'get_user_plan_and_pricing' ) );
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
	
	
	// Add user plan data custom fields

	public function add_user_plan_custom_fields(){
		
		$user_plan  = get_post();
		
		if( !empty($user_plan->post_author) ){
		
			$layer_plan = $this->get_user_plan_info( intval($user_plan->post_author) );

			$fields=[];
			
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
		
		$columns['cb'] = '<input type="checkbox" />';
		$columns['title'] = 'Title';
		$columns['shortcode'] = 'Shortcode';
		$columns['date'] = 'Date';

		return $columns;		
	}
	
	public function add_subscription_plan_column_content($column_name, $post_id){

		if($column_name === 'shortcode') {
			
			echo '<input style="width:200px;" type="text" name="shortcode" value="[subscription-plan id=\'' . $post_id . '\']" ' . disabled( true, true, false ) . ' />';
		}		
	}

	public function get_subscription_plan_shortcode( $atts ){
		
		$atts = shortcode_atts( array(
		
			'id'		 	=> NULL,
			'widget' 		=> 'false',
			'title' 		=> NULL,
			'content' 		=> NULL,
			'button' 		=> NULL,
			'show-storage' 	=> true
			
		), $atts, 'subscription-plan' );		
		
		$subscription_plan = '';
		
		if(!is_null($atts['id'])&&is_numeric($atts['id'])){
			
			$id=intval($atts['id']);
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		='month';
			$total_fee_period		='once';
			$total_price_currency	='$';
			
			$option_name='plan_options';
			
			$options = $this->get_layer_custom_taxonomies_options();
			
			if($data = get_post_meta( $id, $option_name, true )){
				
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
					$style='font-weight: bold;color: rgb(138, 206, 236);';
				}
				else{
					
					$plan_form 		= '';
					$plan_content 	= $plan->post_content;
					$style='padding: 30px 30px;font-weight: bold;background: rgba(158, 158, 158, 0.24);color: rgb(138, 206, 236);';
				}

				// get total_price_amount & total_storage
				
				foreach( $options as $taxonomy => $terms ) {
					
					$taxonomy_options = [];
					
					foreach($terms as $i => $term){

						$taxonomy_options[$i] = $this->parent->layer->get_options( $taxonomy, $term );

						if ( in_array( $term->slug, $data ) ) {						
							
							$total_price_amount = $this->sum_custom_taxonomy_total_price_amount( $total_price_amount, $taxonomy_options[$i], $total_price_period);	
							$total_fee_amount 	= $this->sum_custom_taxonomy_total_price_amount( $total_fee_amount, $taxonomy_options[$i], $total_fee_period);				
							$total_storage 		= $this->sum_custom_taxonomy_total_storage( $total_storage, $taxonomy_options[$i]);

							if( !empty($taxonomy_options[$i]['form']) && ( count($taxonomy_options[$i]['form']['input'])>1 || !empty($taxonomy_options[$i]['form']['name'][0]) ) ){

								if( !empty($_POST['meta_'.$term->slug]) ){
									
									// store data in session
									
									$_SESSION['pm_' . $plan->ID]['meta_'.$term->slug] = $_POST['meta_'.$term->slug];
								}
								else{
									
									$plan_form .= $this->parent->admin->display_field( array(
							
										'type'				=> 'form',
										'id'				=> 'meta_'.$term->slug,
										'name'				=> $term->taxonomy . '-meta',
										'array' 			=> $taxonomy_options[$i],
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
					
					foreach($plan_upgrade as $option => $value){
						
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
				
				sort($data);
				ksort($total_storage);
				
				$plan_data=[];
				$plan_data['id'] 		= $plan->ID;
				$plan_data['name'] 		= $plan->post_title;
				$plan_data['options'] 	= $data;
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
				}
				
				$plan_data=esc_attr( json_encode( $plan_data ) );
				
				//var_dump($plan_data);exit;

				$plan_key=md5( 'plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email );
				
				$agreement_url = $this->parent->server->url . '/agreement/?pk='.$plan_key.'&pd='.$this->parent->base64_urlencode($plan_data) . '&_=' . $this->parent->_time;

				$iframe_height 	= 500;
				
				if( !is_null($atts['widget']) && $atts['widget']==='true' ){

					if( !empty($plan_form) ){
						
						$subscription_plan.= '<div class="row panel-body" style="background:#fff;">';
						
							$subscription_plan.= '<div class="col-xs-12 col-md-6">';

									$subscription_plan.= $plan_form;

							$subscription_plan.= '</div>';
							
						$subscription_plan.= '</div>';						
					}
					else{

						$subscription_plan.= '<iframe src="'.$agreement_url.'" style="width:100%;bottom: 0;border:0;height:' . ($iframe_height - 10 ) . 'px;overflow: hidden;"></iframe>';													
					}
				}
				else{
										
					$subscription_plan.='<h2 id="plan_title" style="'.$style.'">' . $plan_title . '</h2>';
					
					$subscription_plan.=$plan_content;
					
					$subscription_plan.='<div id="plan_form">';
					//$subscription_plan.='<form id="plan_form" action="" method="post">'.PHP_EOL;

						// Output iframe
						
						if($atts['show-storage']===true){
						
							$subscription_plan.= '<div id="plan_storage" style="display:block;">';				
								
								foreach($total_storage as $storage_unit => $total_storage_amount){
									
									if($total_storage_amount > 0 ){
										
										$subscription_plan.='<span style="display:block;">';
										
											if($storage_unit=='templates' && $total_storage_amount==1 ){
												
												$subscription_plan.= $total_storage_amount.' template';
											}
											else{
												
												$subscription_plan.= $total_storage_amount.' '.$storage_unit;
											}
											
										$subscription_plan.='</span>';
									}
								}

							$subscription_plan.= '</div>';
							
							$subscription_plan.='<hr id="plan_hr" style="display:block;"></hr>';
						}
						
						$subscription_plan.= '<div id="plan_price">';				
							
							if( $total_fee_amount > 0 ){
								
								$subscription_plan.= htmlentities(' ').$total_fee_amount.$total_price_currency.' '. ( $total_fee_period == 'once' ? 'one time fee' : $total_fee_period );
								
								if($total_price_amount > 0 ){
									
									$subscription_plan.= '<br>+';
								}
							}
							
							if($total_price_amount > 0 ){
							
								$subscription_plan.= $total_price_amount.$total_price_currency.' / '.$total_price_period;
							}
							elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
								
								$subscription_plan.= 'Free';
							}
							
						$subscription_plan.= '</div>';
						
						$subscription_plan.= '</br>';
						
						$subscription_plan.= '<div id="plan_button">';				
							
							$subscription_plan.='<span class="payment-errors"></span>'.PHP_EOL;

							if( $plan_status == 'unlocked' ){
								
								$subscription_plan.='<a class="btn btn-info btn-lg" href="' . $this->parent->urls->editor . '">'.PHP_EOL;
							
									$subscription_plan.='Unlocked'.PHP_EOL;
							
								$subscription_plan.='</a>'.PHP_EOL;
							}
							else{
								
								$modal_id='modal_'.md5($agreement_url);
								
								if( $plan_status == 'renew' ){
									
									$subscription_plan.='<button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
									
										$subscription_plan.='Renew'.PHP_EOL;

									$subscription_plan.='</button>'.PHP_EOL;									
								}
								else{
									
									$subscription_plan.='<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
										
										if(!empty($atts['button'])){
											
											$subscription_plan.= ucfirst($atts['button']).PHP_EOL;
										}
										elseif($total_price_amount == 0 && $total_fee_amount == 0 ){
											
											$subscription_plan.='Start'.PHP_EOL;
										}
										elseif($total_price_amount == 0 && $total_fee_amount > 0 ){
											
											$subscription_plan.='Order'.PHP_EOL;
										}
										elseif( $plan_status == 'upgrade' ){
											
											$subscription_plan.='Upgrade'.PHP_EOL;
										}
										else{
											
											$subscription_plan.='Subscribe'.PHP_EOL;
										}

									$subscription_plan.='</button>'.PHP_EOL;									
								}

								$subscription_plan.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
									
									$subscription_plan.='<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
										
										$subscription_plan.='<div class="modal-content">'.PHP_EOL;
										
											$subscription_plan.='<div class="modal-header">'.PHP_EOL;
												
												$subscription_plan.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
												
												$subscription_plan.= '<h4 class="modal-title" id="myModalLabel">';
												
													$subscription_plan.= $plan->post_title;
													
													if( $total_price_amount > 0 ){
													
														$subscription_plan.= ' ('.$total_price_amount.$total_price_currency.' / '.$total_price_period.')'.PHP_EOL;
												
													}
												
												$subscription_plan.= '</h4>'.PHP_EOL;
											
											$subscription_plan.='</div>'.PHP_EOL;

												if( $this->parent->user->loggedin ){
													
													//echo '<pre>';
													//var_dump($this->parent->user->has_subscription);exit;
													//var_dump($this->user_has_layer( $plan->ID ));exit;

													$subscription_plan.= '<div class="loadingIframe" style="height: 50px;width: 100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

													$subscription_plan.= '<iframe data-src="' . get_permalink( $plan->ID ) . '?output=widget'.'" style="width: 100%;position:relative;top:-50px;margin-bottom:-60px;bottom: 0;border:0;height:'.$iframe_height.'px;overflow: hidden;"></iframe>';
												}
												else{
													
													$subscription_plan.='<div class="modal-body">'.PHP_EOL;
													
														$subscription_plan.= '<div style="font-size:20px;padding:20px;margin:0px;" class="alert alert-warning">';
															
															$subscription_plan.= 'You need to log in first...';
															
															$subscription_plan.= '<div class="pull-right">';

																$subscription_plan.= '<a style="margin:0 2px;" class="btn-lg btn-success" href="' . wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) . '">Login</a>';
																
																$subscription_plan.= '<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'&action=register">Register</a>';
															
															$subscription_plan.= '</div>';
															
														$subscription_plan.= '</div>';
													
													$subscription_plan.='</div>'.PHP_EOL;
												}

										$subscription_plan.='</div>'.PHP_EOL;
										
									$subscription_plan.='</div>'.PHP_EOL;
									
								$subscription_plan.='</div>'.PHP_EOL;								
							}
							
						$subscription_plan.= '</div>'.PHP_EOL;
						
					//$subscription_plan.='</form>'.PHP_EOL;
					$subscription_plan.='</div>'.PHP_EOL;						
				}
			}
		}		
		
		return $subscription_plan;
	}
	
	public function get_layer_custom_taxonomies_options(){
		
		//get custom taxonomies
		
		$taxonomies= $this->get_user_plan_custom_taxonomies();

		// get custom taxonomies options
		
		$options=[];
		
		foreach($taxonomies as $t){
		
			$taxonomy = $t['taxonomy'];
			$taxonomy_name = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			//get custom taxonomy terms
			
			$terms = get_terms( array(
					
				'taxonomy' => $taxonomy,
				'hide_empty' => false
			));

			//var_dump($terms);exit;
			
			foreach($terms as $term){

				$options[$taxonomy_name][]=$term;
			}
		}

		return 	$options;	
	}
	
	public function get_subscription_plan_fields(){
			
		$fields = [];
		
		//get options
		
		$options = $this->get_layer_custom_taxonomies_options();
		
		//var_dump($options);exit;
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "plan_options"),
				'type'		=> 'checkbox_multi_plan_options',
				'id'		=> 'plan_options',
				'label'		=> '',
				'options'	=> $options,
				'description'=> ''
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
		
		$fields[]=array(
		
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
		
		return $fields;
	}
	
	
	public function sum_custom_taxonomy_total_price_amount( &$total_price_amount=0, $options, $period='month'){
		
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
	
	
	public function sum_custom_taxonomy_total_storage( &$total_storage=[], $options){
		
		$storage_unit=$options['storage_unit'];
		$storage_amount=round(intval($options['storage_amount']),0);
		
		if(!isset($total_storage[$storage_unit])){
			
			$total_storage[$storage_unit] = $storage_amount;
		}
		else{
			
			$total_storage[$storage_unit]= $total_storage[$storage_unit] + $storage_amount;
		}
		
		return $total_storage;
	}
	
	
	public function get_user_plan_and_pricing( $user, $context='admin-dashboard' ) {
		
		if( current_user_can( 'administrator' ) ){
				
			$user_plan_id = $this->get_user_plan_id( $user->ID, true );
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		='month';
			$total_fee_period		='once';
			$total_price_currency	='$';
			
			$taxonomies = $this->get_user_plan_custom_taxonomies();

			echo '<div class="postbox">';
				
				echo '<h3 style="margin:10px;">' . __( 'Plan & Pricing', 'live-template-editor-client' ) . '</h3>';
			
				echo '<table class="widefat fixed striped" style="border:none;">';
					
					foreach($taxonomies as $t){
					
						$taxonomy = $t['taxonomy'];
						$taxonomy_name = $t['name'];
						$is_hierarchical = $t['hierarchical'];
					
						$tax = get_taxonomy( $taxonomy );

						/* Make sure the user can assign terms of the user taxonomy before proceeding. */
						if ( !current_user_can( $tax->cap->assign_terms ) )
							return;

						/* Get the terms of the user taxonomy. */
						$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

						echo '<tr>';
						
							echo '<th style="width:300px;">';
								
								echo '<label for="'.$taxonomy.'">'. __( $taxonomy_name, 'live-template-editor-client' ) . '</label>';
							
							echo '</th>';

							/* If there are any layer-type terms, loop through them and display checkboxes. */
							if ( !empty( $terms ) ) {
								
								echo '<td style="width:250px;">';
								
									foreach ( $terms as $term ) {							
										
										$input_name = $taxonomy.'[]';
										$input_value = esc_attr( $term->slug );
										$input_label =  $term->name;
										
										$checked = checked( true, is_object_in_term( $user_plan_id, $taxonomy, $term->term_id ), false );
										
										if( 1==1 ){
											
											$disabled = '';
										}
										else{
											
											$disabled = disabled( true, true, false ); // untill subscription edition implemented	
										}

										echo '<input type="checkbox" name="'.$input_name.'" id="'.$taxonomy.'-'. $input_value .'" value="'. $input_value .'" '.$disabled.' '. $checked .' />'; 
										echo '<label for="'.$taxonomy.'-'. $input_value .'">'. $input_label .'</label> ';
										echo '<br />';
									
									}
								
								echo'</td>';
								
								echo '<td style="width:120px;">';
								
								$options=[];
								
								foreach ( $terms as $i => $term ) {
									
									$options[$i] = $this->parent->layer->get_options( $taxonomy, $term );
									
									if( is_object_in_term( $user_plan_id, $taxonomy, $term->term_id ) ){
										
										$total_fee_amount 	= $this->sum_custom_taxonomy_total_price_amount( $total_fee_amount, $options[$i], $total_fee_period);
										$total_price_amount = $this->sum_custom_taxonomy_total_price_amount( $total_price_amount, $options[$i], $total_price_period);
										$total_storage 		= $this->sum_custom_taxonomy_total_storage( $total_storage, $options[$i]);
									}
									
									echo '<span style="display:block;padding:1px 0;margin:0;">';
										
										if($options[$i]['storage_unit']=='templates'&&$options[$i]['storage_amount']==1){
											
											echo '+'.$options[$i]['storage_amount'].' template';
										}
										elseif($options[$i]['storage_amount']>0){
											
											echo '+'.$options[$i]['storage_amount'].' '.$options[$i]['storage_unit'];
										}
										else{
											
											echo $options[$i]['storage_amount'].' '.$options[$i]['storage_unit'];
										}
								
									echo '</span>';	
										
								}

								echo'</td>';
								
								echo '<td>';
								
									foreach ( $terms as $i => $term ) {
								
										echo '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
										
											echo $options[$i]['price_amount'].$options[$i]['price_currency'].' / '.$options[$i]['price_period'];
										
										echo '</span>';
									}
									
								echo'</td>';
								
							}
							else {
								
								echo '<td>';
								
									echo __( 'There are no layer-types available.', 'live-template-editor-client' );
								
								echo'</td>';
							}

						echo'</tr>';
					}
					
					echo '<tr style="font-weight:bold;">';
					
						echo '<th style="font-weight:bold;"><label for="price">'. __( 'TOTALS', 'live-template-editor-client' ) . '</label></th>';

						echo '<td style="width:120px;">';

						echo'</td>';
						
						echo '<td>';
							
							if(isset($total_storage)){
								
								foreach($total_storage as $storage_unit => $total_storage_amount){
									
									echo '<span style="display:block;">';
									
										if($storage_unit=='templates'&&$total_storage_amount==1){
											
											echo '+'.$total_storage_amount.' template';
										}
										elseif($total_storage_amount>0){
											
											echo '+'.$total_storage_amount.' '.$storage_unit;
										}
										else{
											
											echo $total_storage_amount.' '.$storage_unit;
										}
										
									echo '</span>';
								}							
							}
							else{
								
								echo '<span style="display:block;">';
									
									echo '+0 templates';
									
								echo '</span>';
							}
							
						echo'</td>';
						
						echo '<td>';
						
							echo '<span style="font-size:16px;">';
							
								if( $total_fee_amount > 0 ){
									
									echo htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
									echo '<br>+';
								}
								
								echo round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;
							
							echo '</span>';
							
						echo'</td>';
		
					echo'</tr>';
						
				echo'</table>';
				
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
	
	public function get_user_plan_custom_taxonomies(){
		
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

			$this->key = sanitize_text_field($_GET['pk']);
			$this->subscribed = sanitize_text_field($_GET['pv']);
			
			// subscribed plan data
			
			if( $this->key == md5('plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email ) && $this->subscribed == md5('subscribed'.$_GET['pd'] . $this->parent->_time . $this->parent->user->user_email ) ){
				
				$plan_data = html_entity_decode($plan_data);
				
				$this->data = json_decode($plan_data,true);
					
				//var_dump($this->data);exit;
				
				if(!empty($this->data['name'])){
					
					//var_dump($plan);exit;
							
					$options 				= $this->get_layer_custom_taxonomies_options();
					$user_has_subscription 	= 'false';
					$all_updated_terms 		= [];
					
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
						
						$user_plan_id = $this->get_user_plan_id( $this->parent->user->ID, true );
						
						$append = false;

						if( $this->data['price'] == 0 || !empty($this->data['upgrade']) ){
							
							// demo, upgrade or donation case
							
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
					
					// handle affiliate commission
					
					if(!empty($_GET['pk'])){
					
						$this->parent->programs->set_affiliate_commission($this->parent->user->ID, $this->data, $_GET['pk'] );
					}

					// schedule email series
					
					$this->parent->email->schedule_campaign( $this->data['id'], $this->parent->user);
					
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
	
	
	public function get_layer_plan_info( $item_id ){	

		$taxonomies = $this->get_user_plan_custom_taxonomies();
		//var_dump($taxonomies);exit;
		
		$user_plan = [];
		
		$user_plan['id'] = $item_id;
		
		$user_plan['info']['total_price_amount'] 	= 0;
		$user_plan['info']['total_fee_amount'] 		= 0;
		$user_plan['info']['total_price_period'] 	= 'month';
		$user_plan['info']['total_fee_period'] 		= 'once';
		$user_plan['info']['total_price_currency'] 	= '$';
		
		foreach($taxonomies as $i => $t){
			
			$taxonomy 		 = $t['taxonomy'];
			$taxonomy_name 	 = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$user_plan['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
			$user_plan['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
			$user_plan['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
			$user_plan['taxonomies'][$taxonomy]['terms']			= [];
			
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
				
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]				= $term_slug;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]				= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]			= $term->term_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			 	= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]	= $term->term_taxonomy_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		 	= $term->taxonomy;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	 	= $term->description;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			 	= $term->count;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]			= $has_term;
					
					if( $in_term === true ){
						
						$options = $this->parent->layer->get_options( $taxonomy, $term );
						
						$user_plan['info']['total_fee_amount']	 = $this->sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_fee_amount'], $options, $user_plan['info']['total_fee_period'] );
						$user_plan['info']['total_price_amount'] = $this->sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_price_amount'], $options, $user_plan['info']['total_price_period'] );
						$user_plan['info']['total_storage'] 	 = $this->sum_custom_taxonomy_total_storage( $user_plan['info']['total_storage'], $options);
					}					
				}
			}
		}
		
		//echo'<pre>';
		//var_dump($user_plan);exit;
		
		return $user_plan;	
	}
	
	public function get_user_plan_info( $user_id ){	

		$user_plan_id 	 = $this->get_user_plan_id( $user_id );

		$taxonomies = $this->get_user_plan_custom_taxonomies();
		//var_dump($taxonomies);exit;
		
		$user_plan = [];
		
		$user_plan['id'] = $user_plan_id;
		
		$user_plan['info']['total_price_amount'] 	= 0;
		$user_plan['info']['total_fee_amount'] 		= 0;
		$user_plan['info']['total_price_period'] 	= 'month';
		$user_plan['info']['total_fee_period'] 		= 'once';
		$user_plan['info']['total_price_currency'] 	= '$';
		
		foreach($taxonomies as $i => $t){
			
			$taxonomy 		 = $t['taxonomy'];
			$taxonomy_name 	 = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$user_plan['taxonomies'][$taxonomy]['taxonomy']			= $taxonomy;
			$user_plan['taxonomies'][$taxonomy]['name']				= $taxonomy_name;
			$user_plan['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
			$user_plan['taxonomies'][$taxonomy]['terms']			= [];
			
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
				
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["slug"]			= $term_slug;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_id"]			= $term->term_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["name"]			= $term->name;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_group"]		= $term->term_group;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["term_taxonomy_id"]= $term->term_taxonomy_id;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["taxonomy"]		= $term->taxonomy;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["description"]	 	= $term->description;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["parent"]			= $term->parent;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["count"]			= $term->count;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["filter"]			= $term->filter;
					$user_plan['taxonomies'][$taxonomy]['terms'][$term_slug]["has_term"]		= $has_term;
					
					if( $in_term === true ){
						
						$options = $this->parent->layer->get_options( $taxonomy, $term );

						$user_plan['info']['total_fee_amount']	 = $this->sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_fee_amount'], $options, $user_plan['info']['total_fee_period'] );
						$user_plan['info']['total_price_amount'] = $this->sum_custom_taxonomy_total_price_amount( $user_plan['info']['total_price_amount'], $options, $user_plan['info']['total_price_period'] );
						$user_plan['info']['total_storage'] 	 = $this->sum_custom_taxonomy_total_storage( $user_plan['info']['total_storage'], $options);
					}					
				}
			}
		}
		
		// get stored user plan value
		
		$user_plan_value = get_post_meta( $user_plan_id, 'userPlanValue',true );
		
		// compare it with current value
		
		if( $user_plan_value=='' || $user_plan['info']['total_price_amount'] != intval($user_plan_value) ){

			update_post_meta( $user_plan_id, 'userPlanValue', $user_plan['info']['total_price_amount'] );
		}
		
		//echo'<pre>';
		//var_dump($user_plan);exit;
		
		return $user_plan;	
	}
	
	public function user_has_layer( $item_id, $layer_type = 'default-layer' ){
		
		$user_has_layer = false;
		
		if($layer_type == 'default-layer'){
			
			$user_has_layer = false;
			
			$layer_plan = $this->get_layer_plan_info( $item_id );
			
			foreach($layer_plan['taxonomies'] as $taxonomy => $tax){

				foreach($tax['terms'] as $term_slug => $term){
					
					if(!isset($this->parent->user->plan['taxonomies'][$taxonomy]['terms'][$term_slug])){
						
						//var_dump($this->parent->user->plan['taxonomies'][$taxonomy]);exit;
					}
					
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
		elseif($layer_type == 'user-layer'){
			
			$user_has_layer = true;
		}
		
		return $user_has_layer;
	}	
	
	public function user_has_plan( $plan_id ){
		
		$user_has_plan = false;
		
		if(!empty($this->parent->user->plan['taxonomies'])){
			
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
		
		if(!empty($this->parent->user->plan['taxonomies'])){
			
			$plan_options = get_post_meta( $plan_id, 'plan_options', true );
						
			if(!empty($plan_options)){
				
				// get new_plan_options

				$plan_options = array_flip($plan_options);
				
				$is_ancestor_upgrade = false;

				foreach($this->parent->user->plan['taxonomies'] as $taxonomy => $tax){

					foreach($tax['terms'] as $term_slug => $new_term){

						if( isset($plan_options[$term_slug]) && $new_term['has_term']!==true ){
							
							// get new term value
							
							$new_term_value = 0;
							
							$new_term_options = $this->parent->layer->get_options( $taxonomy, $new_term );

							if( $new_term_options['price_amount'] > 0 && $this->parent->user->plan['info']['total_price_amount'] < $new_term_options['price_amount'] ){
								
								// get  term value
								
								foreach($this->parent->user->plan['taxonomies'][$taxonomy]['terms'] as $curr_term){
									
									if($curr_term["has_term"] === true ){
										
										if(term_is_ancestor_of( $new_term['term_id'], $curr_term['term_id'], $taxonomy)){
											
											$is_ancestor_upgrade = true;
											
											break;
										}
										
										/*
										$curr_term_options = $this->parent->layer->get_options( $taxonomy, $curr_term );
											
										$new_term_value = $new_term_value - $curr_term_options['price_amount'];										
										*/
									}
								}
								
								$new_term_value = $new_term_options['price_amount'] - $this->parent->user->plan['info']['total_price_amount'];
								
								if( $new_term_value == 0 ){
									
									$new_term_value = $new_term_options['price_amount'];
								}
							}
							elseif( $new_term_options['price_amount'] < 0 ){
								
								$new_term_value = $new_term_options['price_amount'];
							}
							
							$plan_upgrade[$new_term['slug']] = $new_term_value;
						}
					}			
				}
			}
		}
		
		if(!$is_ancestor_upgrade){
			
			// restrict upgrade to parent plan for the moment
			
			$plan_upgrade = [];
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
	
	public function get_layer_taxonomy_price_fields($taxonomy_name,$args=[]){
		
		//get periods
		
		$periods = $this->get_price_periods();
		
		//get price_amount
		
		$price_amount=0;
		if(isset($args['price_amount'])){
			
			$price_amount=$args['price_amount'];
		}

		//get price_period
		
		$price_period='';
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
	
	public function get_layer_taxonomy_storage_fields($taxonomy_name,$args=[]){

		//get storage units
		
		$storage_units = $this->get_storage_units();	
	
		//get storage_amount
		
		$storage_amount=0;
		if(isset($args['storage_amount'])){
			
			$storage_amount=$args['storage_amount'];
		}

		//get storage_unit
		
		$storage_unit='';
		if(isset($args['storage_unit'])&&is_string($args['storage_unit'])){
			
			$storage_unit=$args['storage_unit'];
		}
	
		$storage_field='';
		
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
		
		$taxonomies = $this->get_user_plan_custom_taxonomies();
		
		$user_has_subscription = 'false';
		
		$all_updated_terms = [];
		
		foreach($taxonomies as $t){
		
			$taxonomy = $t['taxonomy'];
			$taxonomy_name = $t['name'];
			$is_hierarchical = $t['hierarchical'];
			
			$tax = get_taxonomy( $taxonomy );

			/* Make sure the current user can edit the user and assign terms before proceeding. */
			if ( !current_user_can( 'administrator', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
				return false;
			
			if(isset($_POST)){
			
				$terms = [];
			
				if(isset($_POST[$taxonomy]) && is_array($_POST[$taxonomy])){
					
					$terms = $_POST[$taxonomy];
					
					$all_updated_terms[]=$_POST[$taxonomy];

					if(!empty($terms)){
						
						$user_has_subscription = 'true';
					}						
				}
			
				$user_plan_id = $this->get_user_plan_id( $user_id );
			
				wp_set_object_terms( $user_plan_id, $terms, $taxonomy);

				clean_object_term_cache( $user_plan_id, $taxonomy );
			}
		}
		
		update_user_meta( $user_id , 'has_subscription', $user_has_subscription);
		
		//send admin notification
							
		wp_mail($this->parent->settings->options->emailSupport, 'Plan edited from dashboard - user id ' . $user_id . ' - ip ' . $this->parent->request->ip, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($all_updated_terms,true) . PHP_EOL  . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);
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

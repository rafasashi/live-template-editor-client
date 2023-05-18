<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Plan {
	
	var $parent;
	var $key;
	var $subscribed;
	var $data;
	var $options;
	var $message;
	
	var $subscription_plans	= array();
	
	var $license_holders	= array();
	var $license_users		= array();
	
	var $user_plans			= array();
	var $user_usage			= array();
	
	var $layer_options		= array();
	var $layerOptions 		= NULL;
	
	var $buttons 			= array();
	var $shortcode 			= '';
	var $iframe_height		= 500;
	
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
			'show_in_rest' 			=> false,
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
			'show_in_menu' 			=> false,
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post'
		));
		
		add_action( 'add_meta_boxes', function(){
		
			$this->parent->admin->add_meta_box (
			
				'plan_options',
				__( 'Plan options', 'live-template-editor-client' ), 
				array('subscription-plan'),
				'advanced'
			);
			
			$this->parent->admin->add_meta_box (
				
				'userPlanValue',
				__( 'Plan Info', 'live-template-editor-client' ), 
				array('user-plan'),
				'advanced'
			);
		});
		
		add_action( 'ltple_newsletter_meta_box_email_series',function($post_types){
			
			$post_types[] = 'subscription-plan';
			
			return $post_types;
			
		},10,1);
		
		add_action( 'rest_api_init', function () {
			 
			register_rest_route( 'ltple-plan/v1', '/deliver', array(
				
				'methods' 	=> 'POST',
				'callback' 	=> array($this,'deliver_plans'),
				'permission_callback' => '__return_true',
			) );
		} );
		
		add_action('restrict_manage_posts', array($this, 'add_manage_plan_actions'),10,2 );
		
		add_filter('user-plan_custom_fields', array( $this, 'add_user_plan_fields' ));		
		
		add_action('init', array( $this, 'init_plan' ));
		
		add_action('ltple_plan_delivered', array( $this, 'schedule_plan_emails' ),10,2);
	
		add_action('admin_init', array( $this, 'handle_bulk_actions' ));
	}

	public function init_plan(){
		
		if( !is_admin() ){
		
			add_shortcode('subscription-plan', array( $this, 'get_subscription_plan_shortcode' ) );
		}
		else{

			// add user taxonomy custom fields
			
			add_action( 'show_user_profile', array( $this, 'show_user_plan_and_pricing' ),21,1 );
			add_action( 'edit_user_profile', array( $this, 'show_user_plan_and_pricing' ),21,1 );
			
			// save user taxonomy custom fields
			
			add_action( 'personal_options_update', array( $this, 'save_custom_user_taxonomy_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_taxonomy_fields' ) );
			
			// add subscription-plan
			
			add_filter('subscription-plan_custom_fields', array( $this, 'get_subscription_plan_fields' ));	
							
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
	
	public function add_manage_plan_actions($post_type,$which){
		
		if( $post_type == 'subscription-plan' ){
			
			$options = array( -1 => 'Select a range' );
			
			if( $types = $this->parent->layer->get_layer_types() ){

				foreach( $types as $type ){
					
					if( !empty($type->ranges) ){
						
						foreach( $type->ranges as $range ){
							
							$options[$type->name][$range['slug']] = $range['name'];
						}
					}
				}
				
				$layer_range = isset($_REQUEST['layer_range']) ? sanitize_title($_REQUEST['layer_range']) : '';
				
				echo'<div style="float:right;">';
				
					echo $this->parent->admin->display_field( array(
						
						'id'		=> 'layer_range',
						'type'		=> 'select',
						'options'	=> $options,
						'data'		=> $layer_range,
						
					), false, false ); 
					
					echo '<input type="submit" name="layer_range_action" id="post-query-submit" class="button" value="Add">';
				
				echo '</div>';
			}
		}
		
		return $post_type;
	}	
	
	public function handle_bulk_actions(){
		
		// get the action
		
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		$sendback = '';

		switch($action){
		
			default:
				
				if( !empty($_REQUEST['post']) && !empty($_REQUEST['layer_range_action']) && !empty($_REQUEST['layer_range']) ){
					
					$posts = $_REQUEST['post'];
					
					if( $layer_range = sanitize_title($_REQUEST['layer_range']) ){
						
						$action = sanitize_title($_REQUEST['layer_range_action']);
						
						if( $action == 'add' ){
							
							foreach( $posts as $post_id ){
								
								$post_id = intval($post_id);
								
								if( !$options = get_post_meta($post_id,'plan_options',true) ){
									
									$options = array();
								}
								
								if( is_array($options) && !in_array($layer_range,$options) ){
									
									$options[] = $layer_range;
									
									update_post_meta($post_id,'plan_options',$options);
								}
							}
							
							add_action('admin_notices',function(){

								echo'<div class="notice notice-success">';
								
									echo'<p>';
									
										echo'Range succesfully added';
										
									echo'</p>';
									
								echo'</div>';
							});
						}
						elseif( $action == 'remove' ){
							
							// TODO
						}
					}
				}
				
			return;
		}
	}	
	
	public function set_subscription_plan_columns($columns){

		// Remove description, posts, wpseo columns
		
		$columns = [];
		
		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['title'] 		= 'Title';
		$columns['shortcode'] 	= 'Shortcode';
		$columns['date'] 		= 'Date';
		$columns['thumb'] 		= 'Cover';
		
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
		elseif($column_name == 'thumb') {
			
			$thumb_url = $this->get_thumb_url($post_id);
			
			echo '<div style="width:250px;">';
				
				echo '<img loading="lazy" src="'.$thumb_url.'" style="width:100%;" />';
			
			echo '</div>';
		}		
	}
	
	public function parse_agreement_url( $plan ){
		
		$plan_data = array(

			'name' => $plan['title']
		);
		
		if( !empty($plan['id']) ){
			
			$plan_data['id'] = $plan['id'];
		}		
		
		if( !empty($plan['options']) ){
			
			$plan_data['options'] = $plan['options'];
		}		
		
		if( !empty($plan['info']['total_price_amount']) ){
			
			$plan_data['price'] = $plan['info']['total_price_amount'];
		}
		
		if( !empty($plan['info']['total_fee_amount']) ){
			
			$plan_data['fee'] = $plan['info']['total_fee_amount'];
		}
		
		if( !empty($plan['info']['total_price_currency']) ){
			
			$plan_data['currency'] = $plan['info']['total_price_currency'];
		}
		
		if( !empty($plan['info']['total_price_period']) ){
			
			$plan_data['period'] = $plan['info']['total_price_period'];
		}

		if( !empty($plan['info']['total_fee_period']) ){
			
			$plan_data['fperiod'] = $plan['info']['total_fee_period'];
		}
		
		if( !empty($plan['info']['total_storage']) ){
			
			$plan_data['storage'] = array('templates' => $plan['info']['total_storage'] );
		}

		if( !empty($plan['upgrade']) ){
			
			$plan_data['upgrade'] = $plan['upgrade'];
		}
		
		if( !empty($plan['back_url']) ){
			
			$plan_data['back'] = $plan['back_url'];
		}
		
		if( !empty($plan['items']) ){
			
			$plan_data['items'] = $plan['items'];
		}
		
		$agreement_url 	= $this->get_agreement_url($plan_data);	
		
		return $agreement_url;
	}
	
	public function get_agreement_url( $data ){
		
		//get plan_data
		
		$plan_data =array(
		
			'id' 		=> '',
			'name' 		=> '',
			'options' 	=> array(),
			'price' 	=> 0,
			'fee' 		=> 0,
			'currency' 	=> '$',
			'period' 	=> 'month',
			'fperiod' 	=> 'once',
			'storage' 	=> array('templates' => 0 ),
			'subscriber'=> $this->parent->user->user_email,
			'client'	=> $this->parent->client->url,
			'back'		=> '',
			'meta' 		=> array(),
			'upgrade' 	=> array(),
			'items'		=> array(),
			'sponsored'	=> '',
		);
		
		if( !empty($data) ){
			
			foreach( $plan_data as $k => $v ){
				
				if( isset($data[$k]) ){
					
					$value = $data[$k];
					
					if( $k == 'options' ){
						
						sort($value);
					}
					/*
					elseif( $k == 'storage' ){
						
						ksort($value);
					}
					*/
					
					$plan_data[$k] = $value;
				}
			}
		}
		
		$plan_data = esc_attr( json_encode( $plan_data ) );
		
		$plan_key = md5( 'plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email );	

		//get agreement url				
		
		$agreement_url = add_query_arg(array(
		
			'pk' => $plan_key,
			'pd' => $this->parent->base64_urlencode($plan_data),
			'du' => parse_url($this->parent->urls->primary,PHP_URL_HOST),
			'_'	 => $this->parent->_time,
		
		),$this->parent->server->url . '/agreement/');

		return $agreement_url;
	}
	
	public function get_plan_table( $plan, $usage = null ){
		
		// get layer types
		
		$layer_types = $this->parent->layer->get_layer_types();
		
		// get headers
		
		$headers = array(
		
			'resources'	=> '<i class="fas fa-cubes"></i> Resources',
		
		);
		
		// get sections
		
		$services = array();
		
		if( !empty($plan['info']['total_storage']) ){
			
			foreach( $layer_types as $type ){

				$row ='';

				foreach( $plan['info']['total_storage'] as $storage_unit => $total_storage_amount ){
					
					$ranges = array();
					
					if( !empty($type->ranges) ){
						
						foreach( $type->ranges as $range ){
							
							if( $range['count'] > 0 ){
								
								if( empty($type->addon) || $type->addon->term_id != $range['term_id'] ){
								
									$ranges[] = $range;
								}
							}
						}
					}
					
					if( $type->name == $storage_unit && !empty($ranges) ){
						
						// get section
						
						$section = $type->gallery_section->name;
						
						// get header
						
						$row .='<tr>';
						
							$row .='<th style="width:40%;">';
							
								$row .= $storage_unit;

							$row .='</th>';
							
							$row .='<th style="width:20%;text-align:center;">';
							
								$row .= '<div data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Access" data-content="Access to the range of resources within the ' . $storage_unit . ' gallery">';
									
									$row .= 'Access';
								
									$row .= ' <i class="fas fa-question-circle" style="font-size:13px;"></i>';
								
								$row .='</div>';

							$row .='</th>';	
							
							$row .='<th style="width:20%;text-align:center;">';
								
								$row .= '<div data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Resources" data-content="The amount of ' . $this->parent->layer->get_storage_name($type->storage) . 's' . ' included in the range of resources">';
									
									//$row .= $this->parent->layer->get_storage_name($type->storage) . 's';
									
									$row .= 'Resources';
									
									$row .= ' <i class="fas fa-question-circle" style="font-size:13px;"></i>';
								
								$row .='</div>';

							$row .='</th>';

							$row .='<th style="width:20%;text-align:center;">';
								
								$row .= '<div data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Storage" data-content="The amount of saved ' . $this->parent->layer->get_storage_name($type->storage) . 's' . ' allowed by the plan">';
								
									$row .= 'Storage';
									
									$row .= ' <i class="fas fa-question-circle" style="font-size:13px;"></i>';
								
								$row .='</div>';
							
							$row .='</th>';
							
						$row .='</tr>';						
						
						// get ranges
						
						foreach( $ranges as $i => $range ){
							
							$preview_url = add_query_arg(array(
								
								'layer[default_storage]' => $type->storage,
								'gallery' 	=> $type->slug,
								'range' 	=> $range['slug'],
								'output' 	=> 'widget',
							
							),$this->parent->urls->editor);

							$modal_id='modal_'.md5($preview_url);
							
							$row .='<tr>';

								$row .='<td style="cursor:pointer;" type="button" data-toggle="modal" data-target="#'.$modal_id.'">';

									$row .='<b>';
										
										$row .= $range['name'];
										
									$row .='</b>';

									$row.='<i class="fas fa-eye pull-right hidden-xs"></i>'.PHP_EOL;

									$row.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
										
										$row.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
											
											$row.='<div class="modal-content">'.PHP_EOL;
											
												$row.='<div class="modal-header">'.PHP_EOL;
													
													$row.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
													
													$row.='<h4 class="modal-title text-left" id="myModalLabel">Gallery</h4>'.PHP_EOL;
												
												$row.='</div>'.PHP_EOL;
												
												$row.= '<iframe data-src="'.$preview_url.'" style="width:100%;position:relative;bottom:0;border:0;height:calc( 100vh - 40px);overflow:hidden;"></iframe>';											
												
											$row.='</div>'.PHP_EOL;
											
										$row.='</div>'.PHP_EOL;
										
									$row.='</div>'.PHP_EOL;
									
								$row .='</td>';
								
								$row .='<td style="text-align:center;">';
									
									if( isset($plan['options'][0]) && in_array( $range['slug'], $plan['options'] ) ){
										
										// plan view
										
										$row .= '<span class="glyphicon glyphicon-ok-circle" style="font-size:30px;color:#3dd643;" aria-hidden="true"></span>';
									}
									elseif( isset( $plan['taxonomies'][$range['taxonomy']]['terms'][$range['slug']]['has_term'] ) && $plan['taxonomies'][$range['taxonomy']]['terms'][$range['slug']]['has_term'] === true ){
										
										// billing info view
										
										$row .= '<span class="glyphicon glyphicon-ok-circle" style="font-size:30px;color:#3dd643;" aria-hidden="true"></span>';
									}											
									else{
										
										$row .= '<span class="glyphicon glyphicon-remove-circle" style="font-size:30px;color:#ec3344;" aria-hidden="true"></span>';
									}

								$row .='</td>';
								
								$row .='<td style="text-align:center;">';

									$row .='<span class="badge">';
									
										$row .= $this->parent->gallery->get_badge_count($range['count']);
									
									$row .= '</span>';
									
								$row .='</td>';
								
								if( $i == 0 ){
									
									$row .='<td rowspan="' . count($ranges) . '" style="text-align:center;background:#efefef;">';
											
										$row .= '<span class="badge">';
											
											if( $total_storage_amount > 0 ){
												
												if( is_array($usage) ){
													
													$storage_usage = isset($usage[$storage_unit]) ? $usage[$storage_unit] : 0;
													
													$row .= $storage_usage .' / ' . $total_storage_amount;
												}
												else{
												
													$row .= $total_storage_amount;
												}
											}
											else{
												
												$row .= '<i class="fas fa-infinity" style="font-size:14px;"></i>';
											}
											
										$row .= '</span>';
									
									$row .='</td>';
								}
							
							$row .='</tr>';
						}
					}
				}
				
				if( !empty($section) && !empty($row) ){
				
					$services['resources'][$section][] = $row;
				}
			}
		}
		
		// get table
		
		$table = '<div id="plan_table">'.PHP_EOL;
			
			$table .= '<div id="plan_storage" style="display:block;">';				
				
				foreach( $services as $service => $sections ){
					
					$table .= '<h2>' . $headers[$service] . '</h2>';
				
					foreach( $sections as $section => $rows ){
						
						$md5 = md5($section);
						
						$table .= '<a data-toggle="collapse" data-target="#section_'.$md5.'" class="plan_section">';
						
							$table .= $section . ' <i class="fas fa-angle-down pull-right" style="font-size:25px;"></i>';
						
						$table .= '</a>';
						
						$table .= '<div id="section_'.$md5.'" class="panel-collapse collapse">';
				
							$table .='<table class="table-striped">';
							
							foreach( $rows as $row ){
			
								$table .= $row;
							}
							
							$table .='</table>';
							
						$table .= '</div>';
					}
				}

			$table .= '</div>';
			
			if( $extra = apply_filters('ltple_plan_table','',$plan) ){
				
				$table .= '<h2><i class="far fa-gem"></i> Extra Features</h2>';

				$table .= $extra;
			}

		$table .= '</div>'.PHP_EOL;

		return $table;
	}
	
	public function get_subscription_plan_shortcode( $atts ){
		
		$atts = shortcode_atts( array(
		
			'id'		 	=> NULL,
			'widget' 		=> 'false',
			'button' 		=> NULL,
			'attributes' 	=> true
			
		), $atts, 'subscription-plan' );		
		
		if( !is_null($atts['id']) && is_numeric($atts['id']) ){
			
			$plan_id = intval($atts['id']);
			
			$total_price_amount 	= 0;
			$total_fee_amount 		= 0;
			$total_price_period		= 'month';
			$total_fee_period		= 'once';
			$total_price_currency	= '$';
			
			$option_name = 'plan_options';
			
			if( $plan = $this->get_plan_info($plan_id) ){
				
				//get plan options
				
				$plan_options = $plan['options'];

				// get total_price_amount & total_storage
				
				$total_price_amount = $plan['info']['total_price_amount'];	
				$total_fee_amount 	= $plan['info']['total_fee_amount'];	
				$total_storage 		= $plan['info']['total_storage'];
				
				// user has plan

				$user_has_plan = isset($plan['user_has_plan']) ? $plan['user_has_plan'] : false;
				
				// user plan upgrade

				$plan_upgrade = $plan['upgrade'];
				
				$total_upgrade = 0;
				
				if(!empty($plan_upgrade)){
					
					foreach($plan_upgrade['now'] as $option => $value){
						
						$total_upgrade += $value;
					}
				}
				
				// get action

				$action = isset($plan['action']) ? $plan['action'] : 'subscribe';				
				
				//get agreement url	

				$agreement_url = $plan['agreement_url'];
				
				//get subscription plan

				$this->shortcode = '';
				
				if( $message = $this->parent->session->get_user_data('message') ){ 
				
					//output message
				
					$this->shortcode .= $message;
					
				}						
				elseif( !empty($this->message) ){ 
				
					//output message
				
					$this->shortcode .= $this->message;
				}	
				
				if( !is_null($atts['widget']) && $atts['widget']==='true' ){
					
					$this->shortcode .= '<div class="modal-body" style="padding:0px;">'.PHP_EOL;
					
						$this->shortcode .= '<iframe sandbox="allow-scripts allow-top-navigation allow-forms allow-same-origin" src="'.$agreement_url.'" style="position:relative;width:100%;bottom: 0;border:0;height:' . ($this->iframe_height - 10 ) . 'px;overflow: hidden;"></iframe>';													
				
					$this->shortcode .= '</div>';
				}
				else{		

					$this->shortcode .='<div id="plan_form">';
						
						// Output iframe
						
						if( $atts['attributes'] === true ){
							
							$this->shortcode .= $this->get_plan_table($plan);
						
							$this->shortcode .='<hr style="display:block;"></hr>';
						}
						
						$this->shortcode .= '<div id="plan_price">';				
							
							$this->shortcode .= $plan['price_tag'];
							
						$this->shortcode .= '</div>';
						
						$this->shortcode .= '</br>';
						
						do_action('ltple_plan_shortcode_value',$plan);
						
						$this->shortcode .= '<div id="plan_button" ' . ( !empty($plan_content) ? 'style="padding-bottom:40px;"' : '' ) . '>';				
							
							$this->shortcode .='<span class="payment-errors"></span>'.PHP_EOL;

							if( $action == 'unlocked' ){
								
								$this->shortcode .='<a class="btn btn-info btn-lg" href="' . $this->parent->urls->current . '">'.PHP_EOL;
							
									$this->shortcode .='Unlocked'.PHP_EOL;
							
								$this->shortcode .='</a>'.PHP_EOL;
							}
							elseif( empty($this->parent->user->ID) || $this->parent->user->plan['holder'] == $this->parent->user->ID ){
								
								// get addon buttons
								
								do_action( 'ltple_plan_shortcode', $plan_id );
								
								if(!empty($this->buttons[$plan_id])){
									
									$this->shortcode .= reset($this->buttons[$plan_id]).PHP_EOL;
								}
								else{
								
									$modal_id='modal_'.md5($agreement_url);
									
									if( $action == 'renew' ){
										
										$this->shortcode .='<button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
										
											$this->shortcode .= ucfirst($action).PHP_EOL;

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
											elseif( $action == 'upgrade' ){
												
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
													
														$this->shortcode .= $plan['title'];
														
														if( $total_price_amount > 0 && $action != 'upgrade' ){
														
															$this->shortcode .= ' (' . $total_price_amount . $total_price_currency.' / '.$total_price_period.')'.PHP_EOL;
														}
													
													$this->shortcode .= '</h4>'.PHP_EOL;
												
												$this->shortcode .='</div>'.PHP_EOL;

												if( $this->parent->user->loggedin ){
													
													$this->shortcode .= '<iframe sandbox="allow-scripts allow-top-navigation allow-forms allow-same-origin" data-src="' . get_permalink( $plan_id ) . '?output=widget'.'" style="width: 100%;position:relative;top:0px;margin-bottom:-20px;bottom: 0;border:0;height:'.$this->iframe_height . 'px;overflow: hidden;"></iframe>';
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
							else{
								
								$this->shortcode .='via manager account'.PHP_EOL;
							}
							
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
	
	public function get_subscription_plan_fields($fields){

		$fields[] = array(

			'metabox' =>
				array('name'	=> 'plan_options'),
				'type'			=> 'checkbox_multi_plan_options',
				'id'			=> 'plan_options',
				'label'			=> '',
				'options'		=> $this->get_layer_taxonomies_options(),
				'description'	=> ''
		);
	
		return $fields;
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
		
		if( !empty($options['storage']) ){
			
			foreach( $options['storage'] as $storage_unit => $storage_amount ){
			
				$storage_amount = round(intval($storage_amount),0);
				
				if(!isset($total_storage[$storage_unit])){
					
					$total_storage[$storage_unit] = $storage_amount;
				}
				else{
					
					$total_storage[$storage_unit] = $total_storage[$storage_unit] + $storage_amount;
				}
				
				//dump($total_storage);
			}
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
	
	public function show_user_plan_and_pricing( $user, $context='admin-dashboard' ) {
		
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

			echo '<h2>' . __( 'Plan & Pricing', 'live-template-editor-client' ) . '</h2>';
			
			echo $this->parent->admin->display_field( array(
			
				'type'			=> 'checkbox_multi_plan_options',
				'id'			=> $this->parent->_base . 'user_plan_options',
				'options'		=> $taxonomies,
				'data'			=> $user_plan_options,
				'description'	=> ''
			));	
		}	
	}
	
	public function get_plan_taxonomies(){
		
		$taxonomies=[];
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
	
	public function get_plans_by_options( $options = array() ){
		
		$plans = array();
		
		if( !empty($options) ){
		
			$subscription_plans = $this->get_subscription_plans();
			
			foreach( $subscription_plans as $plan ){
				
				$in_plan = true;
				
				foreach( $options as $option ){
					
					if( !in_array($option,$plan['options']) ){
						
						$in_plan = false;
						break;
					}
				}
				
				if($in_plan){
				
					$plans[] = $plan;
				}
			}
		}
		
		if( !empty($plans) ){
		
			// order by count
			
			$counts = array();
			
			foreach( $plans as $key => $plan ){
				
				$counts[$key] = $plan['info']['total_price_amount'] + $plan['info']['total_fee_amount'];
			}
			
			array_multisort($counts, SORT_ASC, $plans);
		}

		return $plans;		
	}
	
	public function get_plans_by_id( $layer_id ){
		
		$plans = array();
		
		if( !empty($layer_id) ){

			// get layer type
			
			$layer_type = null;
			
			if( $terms = wp_get_post_terms($layer_id,'layer-type') ){
				
				$layer_type = $terms[0]->slug;
			}

			if( !empty($layer_type) ){
				
				// get layer range
				
				$layer_range = null;
				
				if( $terms = wp_get_post_terms($layer_id,'layer-range') ){
					
					$layer_range = $terms[0]->slug;
				}
				
				// get layer price
				
				$layer_price = intval(get_post_meta($layer_id,'layerPrice',true));
						
				if( $layer_price > 0 ){
					
					$plan = array();
					
					$plan['title'] = 'Template (without editor)';
					
					$plan['info']['total_price_currency'] 	= '$';
					
					$plan['info']['total_price_amount'] 	= 0;
					
					$plan['info']['total_fee_amount'] 	= $layer_price;
					
					$plan['info']['total_fee_period'] 	= 'once';
					
					$plan['price_tag'] = $this->get_price_tag($plan);
					
					$plan['items'] = array($layer_id);
					
					$plan['back_url'] = $this->parent->urls->current;
					
					$plan['info_url'] = get_permalink($layer_id);
							
					$plan['agreement_url'] = $this->parse_agreement_url($plan);
					
					$plan['action'] = 'buy';
					
					$plans[] = $plan;				
				}
						
				if( !empty($layer_range) ){
					
					$plans = array_merge($plans,$this->get_plans_by_options(array($layer_range)));
				}
			}
		}
		
		if( !empty($plans) ){
		
			// order by count
			
			$counts = array();
			
			foreach( $plans as $key => $plan ){
				
				$counts[$key] = $plan['info']['total_price_amount'] + $plan['info']['total_fee_amount'] + ( $plan['info']['total_fee_amount'] * 0.0000000001 );
			}
			
			array_multisort($counts, SORT_ASC, $plans);
		}

		return $plans;		
	}
	
	public function flush_user_plan($user_id){
		
		if( $plan_id = $this->get_user_plan_id( $user_id, true ) ){
			
			wp_delete_post( $plan_id, true );
		}
		
		delete_user_meta( $user_id , $this->parent->_base . 'user_plan_options');
		delete_user_meta( $user_id , $this->parent->_base . 'period_end');

		update_user_meta( $user_id , 'has_subscription', 'false');
	
		return true;
	}
	
	public function get_user_plan_id( $user_id, $create=false, $tax_query = array() ){	
	
		// get user plan id
	
		$q = get_posts(array(
		
			'author'      	=> $user_id,
			'post_type'  	=> 'user-plan',
			'post_status' 	=> 'publish',
			'numberposts' 	=> 1,
			'tax_query' 	=> $tax_query
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
	
	public function remote_get_periods($user_email=''){
		
		if( !is_plugin_active( 'live-template-editor-server/live-template-editor-server.php' ) ){
			
			$api_url = $this->parent->server->url . '/' . rest_get_url_prefix() . '/ltple-subscription/v1/periods?_=' . time();
			
			if(!empty($user_email)){
				
				$api_url .= '&user=' . $this->parent->ltple_encrypt_uri($user_email);
			}
			
			$response = wp_remote_get( $api_url, array(
			
				'headers' => array(
					
					'X-Forwarded-Server' => $_SERVER['HTTP_HOST']
				)
			));
			
			if( is_array($response) && !empty($response['body']) ){
				
				$body = json_decode($response['body'],true);
				
				if( !empty($body['data']) ){
					
					$periods = $this->parent->ltple_decrypt_str($body['data']);
					
					if( !empty($periods) ){
						
						$periods = json_decode($periods,true);
						
						if( !empty($periods) && is_array($periods) ){
							
							return $periods;							
						}
					}
				}
			}
			else{
				
				//dump($response);
			}
		}
		
		return false;
	}
	
	public function remote_update_image_urls(){
		
		if( $ids = $this->remote_get_ids() ){
			
			$updated = array();
			
			foreach( $ids as $email => $id ){
				
				//if( $email != 'abc' ) continue;
				
				if( $user = get_user_by('email',$email) ){
					
					if( $projects = get_posts(array(
					
						'author' 		=> $user->ID,
						'post_type' 	=> 'user-layer',
						/*
						'meta_query' 	=> array(array(
						   
						   'key' 		=> 'layerContent',
						   'value' 		=> 'd3gsv4xtxd08bd.cloudfront.net',
						   'compare' 	=> 'LIKE',
						)),
						*/
						
					))){
						
						foreach( $projects as $project ){
						
							$content = get_post_meta($project->ID,'layerContent',true);
							
							$new_content = str_replace('d3gsv4xtxd08bd.cloudfront.net',$id.'.my.domain',$content);
							
							if( $content != $new_content ){
								
								update_post_meta($project->ID,'layerContent',$new_content);
							
								$updated[$project->ID]=$id.'.my.domain';
							}
						}
					}
				}
			}
		}
		dump($updated);
		exit;		
	}
	
	public function remote_update_periods( $blocking = false ){
		
		wp_remote_request( $this->parent->urls->home . '/?ltple_update=periods', array(
									
			'method' 	=> 'GET',
			'timeout' 	=> 100,
			'blocking' 	=> $blocking
		));		
	}
	
	public function remote_get_ids($user_email=''){
		
		if( !is_plugin_active( 'live-template-editor-server/live-template-editor-server.php' ) ){
			
			$api_url = $this->parent->server->url . '/' . rest_get_url_prefix() . '/ltple-subscription/v1/ids?_=' . time();
			
			if(!empty($user_email)){
				
				$api_url .= '&user=' . $this->parent->ltple_encrypt_uri($user_email);
			}
			
			$response = wp_remote_get( $api_url, array(
			
				'headers' => array(
					
					'X-Forwarded-Server' => $_SERVER['HTTP_HOST']
				)
			) );
			
			if( is_array($response) && !empty($response['body']) ){
				
				$body = json_decode($response['body'],true);
				
				if( !empty($body['data']) ){
					
					$ids = $this->parent->ltple_decrypt_str($body['data']);
					
					if( !empty($ids) ){
						
						$ids = json_decode($ids,true);
						
						if( !empty($ids) && is_array($ids) ){
							
							return $ids;							
						}
					}
				}
			}
			else{
				
				//dump($response);
			}
		}
		
		return false;
	}

	public function update_user(){
		
		// get plan subscription
		
		if( !empty( $this->parent->user->ID ) && isset($_GET['pk'])&&isset($_GET['pd'])&&isset($_GET['pv'])){

			$plan_data = sanitize_text_field($_GET['pd']);
			$plan_data = $this->parent->base64_urldecode($plan_data);

			$this->key 			= sanitize_text_field($_GET['pk']);
			$this->subscribed 	= sanitize_text_field($_GET['pv']);	
			
			$message = '';
			
			// subscribed plan data
			
			if( $this->key == md5('plan' . $plan_data . $this->parent->_time . $this->parent->user->user_email ) && $this->subscribed == md5('subscribed'.$_GET['pd'] . $this->parent->_time . $this->parent->user->user_email ) ){
				
				$plan_data = html_entity_decode($plan_data);
				
				$this->data = json_decode($plan_data,true);
					
				if(!empty($this->data['name'])){
					
					// TODO deliver to sponsored
					
					if( !empty($this->data['subscriber']) ){
						
						$user = get_user_by('email',$this->data['subscriber']);
					}
					
					if( !empty($user) ){
						
						do_action('ltple_update_user_plan',$user);
					
						if( $this->deliver_plan($this->data,$user) ){
														
							if( $this->data['price'] > 0 ){
								
								// update period end
								
								$this->remote_update_periods(false);
								
								// store message
								
								$message .= '<div class="alert alert-success">';
									
									$message .= 'Congratulations, you have successfully subscribed to <b>'.$this->data['name'].'</b>!';

								$message .= '</div>';
							}
							else{
								
								$message .= '<div class="alert alert-success">';
									
									$message .= 'Congratulations, you have successfully upgraded to <b>'.$this->data['name'].'</b>!';

								$message .= '</div>';						
							}
						}
					}
					elseif( $this->data['fee'] > 0 ){
						
						$message .= '<div class="alert alert-success">';
							
							$message .= 'Thanks for your contribution to <b>'.$this->data['name'].'</b>!';

						$message .= '</div>';								
						
						do_action('ltple_one_time_payment');			
					}
				}
			}
			else{
				
				$message .= '<div class="alert alert-warning">';
									
					$message .= 'Wrong plan request...';
			
				$message .= '</div>';
			}
			
			$this->parent->session->update_user_data('message',$message);
			
			// bypass iframe redirection issue
			
			echo'<script>top.location="'.$this->parent->urls->dashboard.'";</script>';
			exit;
		}
	}
	
	public function deliver_plan($plan, $user = null ){
		
		if( !empty($plan['subscriber']) ){
			
			if( is_null($user) ){
			
				$user = get_user_by('email',$plan['subscriber']);
			}
			
			if( !empty($user->ID) ){
				
				$user_id = $user->ID;
				
				// add plan options
				
				if( !empty($plan['options']) ){
					
					$taxonomies 			= $this->get_layer_taxonomies_options();
					$all_updated_terms 		= [];
					
					foreach( $taxonomies as $taxonomy => $terms ) {
						
						$update_terms 		= [];
						$update_taxonomy 	= '';
						
						foreach($terms as $i => $term){

							if ( in_array( $term->slug, $plan['options'] ) ) {
								
								$update_terms[]		= $term->term_id;
								$update_taxonomy 	= $term->taxonomy;
								$all_updated_terms[] = $term->slug;
							}
						}

						// update current user custom taxonomy
						
						$user_plan_id = $this->get_user_plan_id( $user_id, true );
						
						$append = false;

						if( $plan['price'] == 0 || !empty($plan['upgrade']) ){
							
							// demo, upgrade or donation case
							
							$append = true;
						}

						$response = wp_set_object_terms( $user_plan_id, $update_terms, $update_taxonomy, $append );

						clean_object_term_cache( $user_plan_id, $update_taxonomy );
					}
				}
				
				// add plan items
				
				if( !empty($plan['items']) ){
				
					foreach( $plan['items'] as $item_id ){
						
						wp_set_object_terms( $item_id, $plan['subscriber'], 'user-contact', true );
					
						clean_object_term_cache( $item_id, 'user-contact' );
					}
				}				
				
				// hook triggers
				
				if( !empty($plan['id']) ){
					
					// trigger stars
					
					if( intval($plan['price']) > 0 ){
						
						do_action('ltple_paid_plan_subscription',$user);
					}
					else{
						
						do_action('ltple_free_plan_subscription',$user);
					}
					
					// trigger plan subscribed
					
					do_action('ltple_plan_subscribed',$plan,$user);
				}
				
				// trigger plan delivered
				
				do_action('ltple_plan_delivered',$plan,$user);
				
				return true;
			}
		}

		return false;		
	}
	
	public function deliver_plans(){
		
		$delivered_plans = array();
		
		if( !empty($_POST['data']) ){
		
			$plans = json_decode($this->parent->ltple_decrypt_str($_POST['data']),true);
		
			if( !empty($plans) ){
				
				foreach( $plans as $id => $plan ){
					
					$delivered_plans[$id] = false;
					
					$plan = json_decode($plan,true);

					if( !empty($plan['subscriber']) ){
					
						$plan['subscriber'] = str_replace(' ','+',$plan['subscriber']);

						if( $user = get_user_by( 'email', $plan['subscriber'] ) ){
					
							if( $this->deliver_plan( $plan, $user ) ){
								
								$delivered_plans[$id] = true;
							}
						}
					}
				}
				
				// update period end
				
				$this->remote_update_periods(false);
			}
		}
		
		return [ 'data' => $this->parent->ltple_encrypt_str(json_encode($delivered_plans)) ];		
	}	
	
	public function schedule_plan_emails($plan,$user){
		
		if( !is_admin() ){
		
			//send admin notification
			
			if( !empty($plan['price']) || !empty($plan['fee']) ){
			
				wp_mail($this->parent->settings->options->emailSupport, 'Plan edited on checkout - user id ' . $user->ID, 'New plan' . PHP_EOL . '--------------' . PHP_EOL . print_r($plan,true) . PHP_EOL . 'Server request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_SERVER,true). PHP_EOL  . 'Data request' . PHP_EOL . '--------------' . PHP_EOL . print_r($_REQUEST,true) . PHP_EOL);
			} 
			
			if( !empty($plan['id']) ){
			
				// schedule email series

				do_action('ltple_schedule_plan_emails',$plan['id'],$user);
			}
		}		
	}

	public function bulk_update_user_plan($users,$plan_id){
		
		if( $plan = $this->get_plan_info($plan_id) ){
			
			$plan_delivred = false;
			
			foreach( $users as $user_id){
			
				// get user
				
				if( $user = get_user_by('id',$user_id) ){
					
					// set plan
					
					$plan['subscriber'] = $user->user_email;
					$plan['upgrade'] 	= true;
					$plan['price'] 		= $plan['info']['total_price_amount'];
					
					// deliver plan
					
					if( $this->deliver_plan($plan,$user) ) {
						
						// update periods
						
						$this->parent->users->update_periods($user_id);
					}
				}
			}
		}
	}
	
	public function bulk_update_user_type($users,$term_id){
			
		if( !empty($users) ){
			
			$taxonomy = 'layer-type';
			
			foreach( $users as $user_id ){
				
				// update current user custom taxonomy
				
				if( $user_plan_id = $this->parent->plan->get_user_plan_id( $user_id, false, array(
					array(
						'taxonomy' 	=> $taxonomy,
						'terms' 	=> $term_id,
						'field' 	=> 'id',
						'operator' 	=> 'NOT IN',
					),				
				))){

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

				if( $user_plan_id = $this->parent->plan->get_user_plan_id( $user_id, true, array(
					
					/*
					array(
						'taxonomy' 	=> $taxonomy,
						'terms' 	=> $term_id,
						'field' 	=> 'id',
						'operator' 	=> 'NOT IN',
					),
					*/ // not working....
					
				))){
					
					if( !is_object_in_term( $user_plan_id, $taxonomy, $term_id ) ){
						
						$response = wp_set_object_terms( $user_plan_id, array($term_id), $taxonomy, true );
						
						clean_object_term_cache( $user_plan_id, $taxonomy );
					}
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
	
	public function get_plan_info($post){

		$plan_id = is_numeric($post) ? $post : $post->ID;
		
		if( !isset($this->subscription_plans[$plan_id]) ){
			
			$plan = false;
			
			if( is_numeric($post) )
				
				$post = get_post($post);
				
			if( $post->post_type == 'subscription-plan' && $post->post_status == 'publish' ){
				
				$plan = array();
						
				$plan_id 		= $post->ID;
				$plan_title		= $post->post_title;
				$plan_content	= $post->post_content;
				$options 		= get_post_meta( $plan_id, 'plan_options', true );
				
				$plan['id'] 		= $plan_id;
				$plan['title'] 		= $plan_title;
				$plan['content']	= $plan_content;
				$plan['options'] 	= $options;
				
				// plan info
				
				$plan['info']['total_price_amount'] 	= 0;
				$plan['info']['total_fee_amount'] 		= 0;
				$plan['info']['total_price_period'] 	= 'month';
				$plan['info']['total_fee_period'] 		= 'once';
				$plan['info']['total_price_currency']	= '$';

				if( $taxonomies = $this->get_layer_taxonomies_options() ){
						
					foreach( $taxonomies as $taxonomy => $terms ){
						
						foreach( $terms as $term ){
							
							if( in_array($term->slug,$options) ){
							
								// sum values
							
								$plan['info']['total_fee_amount']	= $this->sum_total_price_amount( $plan['info']['total_fee_amount'], $term->options, $plan['info']['total_fee_period'] );
								$plan['info']['total_price_amount'] = $this->sum_total_price_amount( $plan['info']['total_price_amount'], $term->options, $plan['info']['total_price_period'] );
								$plan['info']['total_storage'] 		= $this->sum_total_storage( $plan['info']['total_storage'], $term->options);
								
								$plan = apply_filters('ltple_subscription_plan_info',$plan,$term->options);								

								// add children terms
								
								if( $children = get_term_children( $term->term_id, $term->taxonomy) ){
							
									foreach( $children as $child_id ){
										
										$child = get_term_by( 'id', $child_id, $term->taxonomy );
										
										if( !in_array($child->slug,$plan['options']) ){
										
											$plan['options'][] = $child->slug;
										
											$child_options = $this->parent->layer->get_options($term->taxonomy,$child);
									
											$plan['info']['total_storage'] = $this->sum_total_storage( $plan['info']['total_storage'], $child_options);
										
											$plan = apply_filters('ltple_subscription_plan_info',$plan,$child_options);
										}
									}
								}
							}
						}
					}
				}
			
				$plan['upgrade'] = array();
				
				$plan['action'] = 'subscribe';
				
				$plan['user_has_plan'] = false;

				if( $this->parent->user->loggedin ){
					
					// user has plan
					
					$plan['user_has_plan'] = $this->user_has_plan( $plan_id );
					
					// plan upgrade

					$total_upgrade = 0;
					
					if( $plan_upgrade = $this->user_plan_upgrade( $plan_id ) ){
						
						foreach($plan_upgrade['now'] as $option => $value){
							
							$total_upgrade += $value;
						}
					}
					
					if( $total_upgrade > 0 ){
					
						$plan['upgrade'] = $plan_upgrade;
					}

					// plan action
					
					if( $plan['info']['total_price_amount'] == 0 && $plan['info']['total_fee_amount'] == 0 && $plan['user_has_plan'] === true ){
						
						$plan['action'] = 'unlocked';
					}
					elseif( $plan['info']['total_price_amount'] > 0 ){
					
						if( $plan['user_has_plan'] === true ){
							
							$plan['action'] = 'renew';
						}
						elseif( $total_upgrade > 0 ){
							
							$plan['action'] = 'upgrade';
						}
					}
					elseif( $plan['info']['total_fee_amount'] > 0 ){
						
						$plan['action'] = 'order';
					}
				}
				
				//price tag
				
				$plan['price_tag'] = $this->get_price_tag($plan);
				
				// plan urls
				
				$plan['back_url'] 	 	= $this->parent->urls->current;
				
				$plan['info_url'] 	 	= get_post_permalink($plan_id);
				
				$plan['agreement_url'] 	= $this->parse_agreement_url($plan);
			}
		
			$this->subscription_plans[$plan_id] = $plan;
		}
		
		return $this->subscription_plans[$plan_id];
	}
	
	public function get_subscription_plans(){	

		if( $posts = get_posts( array(
		
			'post_type' 	=> 'subscription-plan',
			'post_status' 	=> 'publish',
			'numberposts' 	=> -1,
		)) ){

			foreach( $posts as $post ){
				
				$subscription_plans[$post->ID] = $this->get_plan_info($post);
			}
		}
		
		return $subscription_plans;
	}
	
	public function get_price_tag($plan){
		
		$price_tag = '';
		
		if( $plan['info']['total_price_amount'] == 0 && $plan['info']['total_fee_amount'] == 0 ){
			
			$price_tag .= $plan['info']['total_price_currency'] . '0' . ' / ' . $plan['info']['total_price_period'];
		}
		else{
			
			if( $plan['info']['total_price_amount'] > 0 ){
				
				$price_tag .= $plan['info']['total_price_currency'] . $plan['info']['total_price_amount'] . ' / ' . $plan['info']['total_price_period'];
			
				if( $plan['info']['total_fee_amount'] > 0 ){
					
					$price_tag .= ' + ';
				}
			}
			
			if( $plan['info']['total_fee_amount'] > 0 ){
				
				$price_tag .= $plan['info']['total_price_currency'] . $plan['info']['total_fee_amount'] . ' ' . $plan['info']['total_fee_period'];
			}
		}

		return $price_tag;
	}
	
	public function get_layer_plan( $item_id, $select = 'min' ){
		
		$amount = array(
		
			'amount'	=> null,
			'currency'	=> null,
		);
		
		if( $layer_range = $this->parent->layer->get_layer_range($item_id) ){
			
			if( $plans = $this->get_plans_by_options( array(
				
				$layer_range->slug
				
			))){
			
				$i = $select == 'max' ? array_key_last($plans) : 0;
				
				if( isset($plans[$i]['info']['total_price_amount']) ){
					
					$amount['amount'] 	= $plans[$i]['info']['total_price_amount'];
					$amount['currency'] = $plans[$i]['info']['total_price_currency'];	
				}
			}
		}

		return $amount;
	}
	
	public function get_layer_options( $item_id ){	
		
		if( !isset($this->layer_options[$item_id]) ){
			
			$this->layer_options[$item_id] = array(
			
				'id' => $item_id,
			);
			
			if( $plan_taxonomies = $this->get_plan_taxonomies() ){
				
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
		}
		
		return $this->layer_options[$item_id];	
	}
	
	public function get_license_holder_id($user_id){
		
		if( !isset($this->license_holders[$user_id]) ){
		
			$this->license_holders[$user_id] = apply_filters('ltple_license_holder_id',$user_id); 
		}
		
		return $this->license_holders[$user_id];
	}
	
	public function get_license_period_end($user_id){
		
		$period_end = 0;
		
		if( $user_id = $this->get_license_holder_id($user_id)){

			$period_end = get_user_meta( $user_id, $this->parent->_base . 'period_end', true );
			
			if( !is_numeric($period_end) ){
				
				// remote get period end
				
				$user_email = $this->get_license_holder_email($user_id);
				
				$periods = $this->remote_get_periods($user_email);
				
				if( !empty($periods[$user_email]) ){
					
					$period_end = $periods[$user_email];
					
					$this->parent->users->update_user_period($user_id,$period_end);
				}				
			}
			
			$period_end = intval($period_end);
		}
		
		return $period_end;
	}
	
	public function get_license_key($str){
		
		return implode('-', str_split(strtoupper(md5($str)), 4));
	}
	
	public function get_license_remaining_days($period_end){
		
		$remaining_days = 0;
		
		if( !empty($period_end) ){
			
			$remaining_days = ceil( ($period_end - time()) / (60 * 60 * 24) );	
			
			if( $remaining_days == 0 || $remaining_days == -0 ){
				
				$remaining_days = 0.1; // one day margin for client side services
			}
		}
		
		return $remaining_days;
	}
	
	public function get_license_holder_email($user){
		
		$license_holder_email = null;
		
		if( is_numeric($user) ){
			
			$user = get_user_by('id',$user);
		}
		
		if( !empty($user->ID) ){
			
			$license_holder_id = $this->get_license_holder_id($user->ID);
		
			if( $license_holder_id == $user->ID ){
				
				$license_holder_email = $user->user_email;
			}
			elseif( $license_holder = get_userdata($license_holder_id) ){
				
				$license_holder_email = $license_holder->user_email;
			}
		}
		
		return strtolower($license_holder_email);
	}
	
	public function get_license_users($holder_id){
		
		if( !isset($this->license_users[$holder_id]) ){

			$this->license_users[$holder_id] = apply_filters('ltple_license_users',array($holder_id)); 
		}
		
		return $this->license_users[$holder_id];
	}

	public function get_user_plan_info( $user_id ){	
		
		// get license holder id
		
		$user_id = $this->get_license_holder_id($user_id);
		
		if( !isset($this->user_plans[$user_id]) ){
		
			$user_plan_id 	= $this->get_user_plan_id( $user_id );
			$taxonomies 	= $this->get_plan_taxonomies();

			$this->user_plans[$user_id] = [];
			
			$this->user_plans[$user_id]['id'] = $user_plan_id;
			
			$this->user_plans[$user_id]['holder'] = $user_id;
			
			$this->user_plans[$user_id]['options'] = array();
			
			$this->user_plans[$user_id]['info']['total_price_amount'] 	= 0;
			$this->user_plans[$user_id]['info']['total_fee_amount'] 	= 0;
			$this->user_plans[$user_id]['info']['total_price_period'] 	= 'month';
			$this->user_plans[$user_id]['info']['total_fee_period'] 	= 'once';
			$this->user_plans[$user_id]['info']['total_price_currency'] = '$';
			
			foreach($taxonomies as $i => $t){
				
				$taxonomy 		 = $t['taxonomy'];
				$taxonomy_name 	 = $t['name'];
				$is_hierarchical = $t['hierarchical'];
				
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['taxonomy']		= $taxonomy;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['name']			= $taxonomy_name;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['is_hierarchical']	= $is_hierarchical;
				$this->user_plans[$user_id]['taxonomies'][$taxonomy]['terms']			= [];
				
				$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
				
				if ( !empty($terms) ) {
					
					foreach ( $terms as $term ) {						

						$term_slug = $term->slug;
						
						$has_term = $in_term = false;
						
						if($user_plan_id > 0 ){
							
							$has_term = $in_term = is_object_in_term( $user_plan_id, $taxonomy, $term->term_id );
						
							if($is_hierarchical === true && $term->parent > 0 && $has_term === false ){
								
								$parent_id = $term->parent;
								
								while( $parent_id > 0 ){
									
									if($has_term === false){
										
										foreach($terms as $parent){
											
											if( $parent->term_id == $parent_id ){
												
												$has_term = is_object_in_term( $user_plan_id, $taxonomy, $parent->term_id );

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
						
						if( $has_term === true ){
							
							$options = $this->parent->layer->get_options( $taxonomy, $term );
									
							if( empty($term->parent) || !$this->is_parent_in_plan( $user_plan_id, $taxonomy, $term->parent ) ){
							
								
								$this->user_plans[$user_id]['info']['total_fee_amount']	 	= $this->sum_total_price_amount( $this->user_plans[$user_id]['info']['total_fee_amount'], $options, $this->user_plans[$user_id]['info']['total_fee_period'] );
								$this->user_plans[$user_id]['info']['total_price_amount'] 	= $this->sum_total_price_amount( $this->user_plans[$user_id]['info']['total_price_amount'], $options, $this->user_plans[$user_id]['info']['total_price_period'] );
							}
							
							$this->user_plans[$user_id]['info']['total_storage'] = $this->sum_total_storage( $this->user_plans[$user_id]['info']['total_storage'], $options);
							
							if( $taxonomy == 'account-option' ){
							
								do_action('ltple_user_plan_option_total',$user_id,$options);
							}
							
							$this->user_plans[$user_id]['options'] = array_merge($this->user_plans[$user_id]['options'],$options);
						}					
					}
				}
			}
			
			// get stored user plan value
			
			$user_plan_value = intval( get_post_meta( $user_plan_id, 'userPlanValue',true ) );
			
			// compare it with current value
			
			if( empty($user_plan_value) || $this->user_plans[$user_id]['info']['total_price_amount'] != $user_plan_value ){

				update_post_meta( $user_plan_id, 'userPlanValue', $this->user_plans[$user_id]['info']['total_price_amount'] );
			}
			
			do_action('ltple_user_plan_info',$user_id);
		}
		
		return $this->user_plans[$user_id];	
	}
	
	public function get_user_plan_usage( $user_id ){	
		
		$user_id = $this->get_license_holder_id($user_id);
		
		$users = $this->get_license_users($user_id);
		
		if( !isset($this->user_usage[$user_id]) ){
			
			$storage_types = $this->parent->layer->get_storage_types();
			
			if( $projects = get_posts(array(
				
				'post_type' 	=> array_keys($storage_types),
				'author__in' 	=> $users,
				'numberposts'	=> -1,
				//'fields'		=> 'ids',
				'post_status'	=> array('publish','draft'),
				
			))){
				
				foreach( $projects as $project ){
					
					$project->type = $this->parent->layer->get_layer_type($project);
					
					if( !empty($project->type->name) ){
					
						if( !isset($this->user_usage[$user_id][$project->type->name]) ){
							
							$this->user_usage[$user_id][$project->type->name] = 1;
						}
						else{
							
							++$this->user_usage[$user_id][$project->type->name];
						}
					}
				}
			}
			else{
				
				$this->user_usage[$user_id] = null;
			}
		}

		return $this->user_usage[$user_id];
	}
	
	public function remaining_storage_amount($defaultLayer){
		
		if( $this->parent->user->loggedin){
		
			$user_plan = $this->get_user_plan_info($this->parent->user->ID);
			
			if( !empty($user_plan['info']['total_storage']) ){

				if( is_numeric($defaultLayer) ){
					
					$defaultLayer = get_post($defaultLayer);
				}
				
				if( !empty($defaultLayer) ){
				
					$layer_type = $this->parent->layer->get_layer_type($defaultLayer);
					
					if( !empty($user_plan['info']['total_storage'][$layer_type->name]) ){
						
						$total_storage = $user_plan['info']['total_storage'][$layer_type->name];
						
						$plan_usage = $this->get_user_plan_usage( $this->parent->user->ID );
						
						if( isset($plan_usage[$layer_type->name]) ){
							
							return $total_storage - $plan_usage[$layer_type->name];
						}
						else{
							
							return $total_storage;
						}				
					}
				}
			}
		}
		
		return 0;
	}
		
	public function user_has_layer( $item ){
		
		$user_has_layer = false;
		
		if( $this->parent->user->loggedin ){
			
			$item_id = 0;
			
			if( is_numeric($item) ){
				
				$item_id = intval($item);
				
				$item = get_post($item_id);
			}
			elseif( !empty($item->ID) ){
				
				$item_id = $item->ID;
			}				
			
			if( $item_id > 0 ){
				
				if( $item->post_type == 'cb-default-layer' ){
					
					if( 1==1 || !$this->parent->user->can_edit ){
						
						if( has_term( $this->parent->user->user_email, 'user-contact', $item_id ) ){
								
							$user_has_layer = true;
						}
						else{
							
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
				elseif( $item->post_type == 'default-image' ){
					
					$user_has_layer = true;
				}
				elseif( intval($item->post_author) == $this->parent->user->ID ){
					
					$user_has_layer = true;
				}
				elseif( is_admin() ){
					
					$user_has_layer = true;
				}
			}
		}

		return $user_has_layer;
	}	
	
	public function user_has_options( $options = array() ){
		
		$user_has_options = false;
		
		if( !empty($options) ){
		
			if( !empty($this->parent->user->plan['taxonomies']) && !empty($options) ){

				foreach($this->parent->user->plan['taxonomies'] as $taxonomy => $tax ){
					
					$user_has_options = true;

					foreach( $options as $option ){
						
						if( isset($tax['terms'][$option]['has_term']) && $tax['terms'][$option]['has_term'] !== true ){
							
							$user_has_options = false;
							break 2;
						}
					}
				}
			}
		}
		
		return $user_has_options;
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
							
							if( $new_term_options['price_period'] == 'month' ){
								
								$new_term_value = $new_term_options['price_amount'];
								
								if( $new_term_options['price_amount'] > 0 ){
									
									// get  term value
									
									foreach($this->parent->user->plan['taxonomies'][$taxonomy]['terms'] as $curr_term){
										
										if( $curr_term['has_term'] === true ){
											
											if(term_is_ancestor_of( $new_term['term_id'], $curr_term['term_id'], $taxonomy)){
												
												$is_ancestor_upgrade = true;
												
												break;
											}
										}
									}

									if( $is_ancestor_upgrade ){
									
										$new_term_value = $new_term_options['price_amount'] - $this->parent->user->plan['info']['total_price_amount'];
										
										if( $new_term_value <= 0 ){
											
											$new_term_value = $new_term_options['price_amount'];
										}
									}
								}
							}
							
							$total_price_amount += $new_term_value;
		
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
		//$periods['day']	= 'day';
		$periods['month']	= 'month';
		$periods['year']	= 'year';
		$periods['once']	= 'once';
		
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
			
			$price_fields.='<input type="number" step="0.1" min="-10000" max="10000" placeholder="0" name="'.$taxonomy_name.'-price-amount" id="'.$taxonomy_name.'-price-amount" style="width: 60px;" value="'.$price_amount.'"/>';
			
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
		
		$storage_units = array( '-1' => 'none' );
		
		$types = get_terms( array(
				
			'taxonomy' 		=> 'layer-type',
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'hide_empty'	=> false, 
		));
		
		if( !empty($types) ){
				
			foreach( $types as $type ){
				
				$storage_units[$type->term_id] = $type->name;
			}
		}
		
		//get storage_amount
		
		$storage_amount = 0;
		
		if(isset($args['storage_amount'])){
			
			$storage_amount=$args['storage_amount'];
		}

		//get storage_unit
		
		$storage_unit = -1;
		
		if( isset($args['storage_unit']) && is_numeric($args['storage_unit']) ){
			
			$storage_unit = intval($args['storage_unit']);
		}
		
		// get storage field
	
		$storage_field = '';
		
		$storage_field.='<div class="input-group">';

			$storage_field.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">+</span>';
			
			$storage_field.='<input type="number" step="1" min="-10000" max="10000" placeholder="0" name="'.$taxonomy_name.'-storage-amount" id="'.$taxonomy_name.'-storage-amount" style="width: 50px;" value="'.$storage_amount.'"/>';
			
			$storage_field.='<select name="range_type" id="'.$taxonomy_name.'-range-type">';
				
				foreach($storage_units as $k => $v){
					
					$selected='';
					
					if( $k == $storage_unit ){
						
						$selected='selected';
					}
					
					$storage_field.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$storage_field.='</select>';				
			
		$storage_field.='</div>';
		
		$storage_field.='<p class="description">The amount of template storage</p>';
		
		return $storage_field;		
	}
	
	public function get_account_storage_fields( $taxonomy_name, $args = [] ){
		
		//get storage units
		
		$storage_units = array();
		
		$types = get_terms( array(
				
			'taxonomy' 		=> 'layer-type',
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'hide_empty'	=> false, 
		));
		
		if( !empty($types) ){
				
			foreach( $types as $type ){
				
				$storage_units[$type->term_id] = $type->name;
			}
		}
		
		// get storage field
	
		$storage_field = '';
		
		foreach( $storage_units as $storage_id => $storage_unit ){
			
			$storage_amount = 0;
			
			if( isset($args[$storage_id]) && is_numeric($args[$storage_id]) ){
				
				$storage_amount = $args[$storage_id];
			}
			
			$storage_field.='<div class="input-group" style="margin-bottom:5px;display:-webkit-box;width:fit-content;">';
			
				$storage_field.='<span class="input-group-addon" style="color:#fff;padding:6px 10px;background:#9E9E9E;">+</span>';
				
				$storage_field.='<input type="number" step="1" min="-10000" max="10000" placeholder="0" name="account_storages['.$storage_id.']" style="width:80px;" value="'.$storage_amount.'"/>';
				
				$storage_field.='<span class="input-group-addon" style="padding:6px 10px;">'.$storage_unit.'</span>';
		
			$storage_field.='</div>';
		}			

		$storage_field.='<p class="description">The amount of template storage</p>';
		
		return $storage_field;		
	}
	
	public function save_custom_user_taxonomy_fields( $user_id ) {
		
		if ( !current_user_can( 'administrator', $user_id ) )
			return false;		
		
		if( isset($_POST[$this->parent->_base . 'user_plan_options']) ){
			
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
							}
						}
						
						wp_set_object_terms( $user_plan_id, $term_ids, $term->taxonomy );

						clean_object_term_cache( $user_plan_id, $term->taxonomy );				
					}
				}
			}
		}
	}
	
	public function unlock_output_request( $for = '+1 hour' ){

		//get plan_data
		
		$plan_data 				 = [];
		$plan_data['name'] 		 = 'unlock output';
		$plan_data['for'] 		 = $for;

		//get agreement url				
		
		$agreement_url = $this->get_agreement_url($plan_data);

		$reponse = wp_remote_post($agreement_url);

		if( !empty($reponse['body']) ){

			$message = '<div class="alert alert-success"><b>Congratulations</b> you have successfully unlocked the output for '.$for.'</div>';
		}
		else{
			
			$message = '<div class="alert alert-warning">Error unlocking the output...</div>';
		}
		
		$this->parent->session->update_user_data('message',$message);
		
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

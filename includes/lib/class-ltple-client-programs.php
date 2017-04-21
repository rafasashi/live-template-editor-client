<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Programs extends LTPLE_Client_Object {
	
	var $parent;
	var $list;
	var $status;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->list = array(
		
			'affiliate'	=>'Affiliate',
			//'partner'	=>'Partner',
		);
		
		$this->parent->register_post_type( 'affiliate-commission', __( 'Affiliate commissions', 'live-template-editor-client' ), __( 'Affiliate commission', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu'		 	=> 'affiliate-commission',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title','author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_taxonomy( 'commission-status', __( 'Status', 'live-template-editor-client' ), __( 'Commision status', 'live-template-editor-client' ),  array('affiliate-commission'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> false,
			'sort' 					=> '',
		));		
		
		add_action( 'add_meta_boxes', function(){
		
			$this->parent->admin->add_meta_box (
			
				'commission_amount',
				__( 'Amount', 'live-template-editor-client' ), 
				array("affiliate-commission"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
			
				'tagsdiv-commission-status',
				__( 'Status', 'live-template-editor-client' ), 
				array("affiliate-commission"),
				'side'
			);
			
			$this->parent->admin->add_meta_box (
			
				'commission_details',
				__( 'Commission Details', 'live-template-editor-client' ), 
				array("affiliate-commission"),
				'advanced'
			);
		});		
		
		add_action( 'wp_loaded', array($this,'get_commission_status'));
		
		add_action( 'ltple_loaded', array( $this, 'init_affiliate' ));
		
		add_action( 'user_register', array( $this, 'ref_user_register' ));
	}
	
	public function get_commission_status(){

		$this->status = $this->get_terms( 'commission-status', array(
				
			'pending'  	=> 'Pending',
			'paid'  	=> 'Paid',
		));
	}
	
	public function get_affiliate_commission_fields(){
				
		$fields=[];
		
		// get post id
		
		$post_id=get_the_ID();

		// get commission status
		
		$status = [];
		
		foreach($this->status as $term){
			
			$status[$term->slug] = $term->name;
		}
		
		$terms = wp_get_post_terms( $post_id, 'commission-status' );
		
		$default_status='';

		if( isset($terms[0]->slug) ){
			
			$default_status=$terms[0]->slug;
		}		
		
		$fields[]=array(
			"metabox" =>
				array('name'		=>"tagsdiv-commission-status"),
				'id'				=>"new-tag-commission-status",
				'name'				=>'tax_input[commission-status]',
				'label'				=>"",
				'type'				=>'select',
				'options'			=>$status,
				'selected'			=>$default_status,
				'description'		=>''
		);
		
		// get commission amount
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'	=>"commission_amount"),
				'id'			=>"commission_amount",
				'label'			=>"",
				'type'			=>'number',
				'placeholder'	=>"0",
				'description'	=>''
		);		
		
		// get commission details
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'	=> "commission_details"),
				'id'			=> "commission_details",
				'label'			=> "",
				'type'			=> 'textarea',
				'placeholder'	=> "JSON",
				'description'	=> ''
		);
		
		return $fields;
	}
	
	public function init_affiliate(){
		
		if( is_admin() ){
			
			add_filter('affiliate-commission_custom_fields', array( $this, 'get_affiliate_commission_fields' ));
		

			// add program field
			
			add_action( 'show_user_profile', array( $this, 'get_user_programs' ) );
			add_action( 'edit_user_profile', array( $this, 'get_user_programs' ) );
			
			// add affiliate field
			
			add_action( 'show_user_profile', array( $this, 'get_user_referrals' ) );
			add_action( 'edit_user_profile', array( $this, 'get_user_referrals' ) );		
			
			// save user programs
			
			add_action( 'personal_options_update', array( $this, 'save_user_programs' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user_programs' ) );			
		}
		else{
			
			if(isset($_GET['affiliate'])){
				
				$this->banners = get_option($this->parent->_base . 'affiliate_banners');
			
				if( !empty($_POST['paypal_email']) ){
					
					$email = sanitize_email( $_POST['paypal_email'] );
					
					if(is_email($email)){
						
						update_user_meta($this->parent->user->ID, $this->parent->_base . '_paypal_email', $email);
					}
				}
			}	
		
			if( !empty($this->parent->request->ref_id) && !$this->parent->user->loggedin ){
					
				$this->set_affiliate_counter($this->parent->request->ref_id, 'clicks', $this->parent->request->ip );
			
				do_action( 'ltple_referred_click' );
			}			
		}
	}
	
	public function has_program( $program = 'affiliate', $user_id = 0, $programs = NULL ){
		
		if( is_null($programs) ){
		
			$programs = json_decode( get_user_meta( $user_id, $this->parent->_base . 'user-programs',true) );
		}
		
		return ( !empty($programs) && in_array($program, $programs) );
	}
	
	public function get_affiliate_counter($user_id, $type = 'clicks'){
		
		$counter = get_user_meta( $user_id, $this->parent->_base . 'affiliate_'.$type, true);
		
		if( empty($counter) ){

			$counter = [];
			
			$counter['today'] = [];
			$counter['week']  = [];
			$counter['month'] = [];
			$counter['year']  = [];
			$counter['total'] = 0;
		}
		
		$z 	= date('z'); //day of the year
		$w 	= date('W'); //week of the year
		$m 	= date('m'); //month of the year
		$y 	= date('Y'); //year				
		
		// set today
		
		if(!isset($counter['today'][$y][$z])){
			
			$counter['today'] = [ $y => [ $z => [] ] ]; // reset array
		}		

		// set week
		
		if(!isset($counter['week'][$y][$w])){
			
			$counter['week'] = [ $y => [ $w => 0 ] ]; // reset array
		}
		
		// set month
		
		if(!isset($counter['month'][$y][$m])){
			
			$counter['month'][$y][$m] = 0; // append array
		}			

		// set year
		
		if(!isset($counter['year'][$y])){
			
			$counter['year'][$y] = 0; // append array
		}		
		
		return $counter;
	}
	
	public function set_affiliate_counter($user_id, $type = 'clicks', $id, $counter = null){
		
		$z 	= date('z'); //day of the year
		$w 	= date('W'); //week of the year
		$m 	= date('m'); //month of the year
		$y 	= date('Y'); //year
		
		if(is_null($counter)){
			
			$counter = $this->get_affiliate_counter( $user_id, $type );
		}
		
		if( !isset($counter['today'][$y][$z]) || !in_array($id,$counter['today'][$y][$z]) ){
		
			if($type == 'commission'){
				
				$amount = explode('_',$id);
				
				$amount = floatval($amount[1]);
				
				// set today
				
				$counter['today'][$y][$z][$id] = $amount;
				
				// set week

				$counter['week'][$y][$w] += $amount;
				
				// set month
				
				$counter['month'][$y][$m] += $amount;
				
				// set year
				
				$counter['year'][$y] += $amount;
				
				// set total
				
				$counter['total'] += $amount;				
			}
			else{
				
				// set today
				
				$counter['today'][$y][$z][] = $id;
				
				// set week

				++$counter['week'][$y][$w];
				
				// set month
				
				++$counter['month'][$y][$m];
				
				// set year
				
				++$counter['year'][$y];
				
				// set total
				
				++$counter['total'];				
			}
			
			// update counter
			
			update_user_meta( $user_id, $this->parent->_base . 'affiliate_'.$type, $counter);
		}
		
		return $counter;
	}
	
	public function set_affiliate_commission($user_id, $data, $id, $currency='$'){
	
		$pourcent_price = 50;
		$pourcent_fee 	= 25;
		
		$total = ( $data['price'] + $data['fee'] );

		$amount =  ( ( $data['price'] * ( $pourcent_price / 100 ) ) + ( $data['fee'] * ( $pourcent_fee / 100 ) ) );
		
		if( $amount > 0 ){
			
			$pourcent = ( $total > 0 ? ( ( $amount / $total ) * 100 ) : 0 );
			
			// handle affiliate commission

			if( $referredBy = get_user_meta($user_id, $this->parent->_base . 'referredBy', true) ){

				if( $affiliate = get_userdata(key($referredBy)) ){
					
					// get commission
					
					$q = get_posts(array(
					
						'name'        => $id . '_' . $amount,
						'post_type'   => 'affiliate-commission',
						'post_status' => 'publish',
						'numberposts' => 1
					));
					
					if( empty($q) ){
						
						// get pending term id
						
						$pending_id = false;
						
						foreach($this->status as $status){
							
							if( $status->slug == 'pending' ){
								
								$pending_id = $status->term_id;
								break;
							}
						}
						
						if($pending_id){
					
							// insert commission

							if($commission_id = wp_insert_post(array(
						
								'post_author' 	=> $affiliate->ID,
								'post_title' 	=> $currency . $amount . ' over ' . $currency . $total . ' (' . $pourcent . '%)',
								'post_name' 	=> $id . '_' . $amount,
								'post_type' 	=> 'affiliate-commission',
								'post_status' 	=> 'publish'
							))){

								// update commission details

								wp_set_object_terms($commission_id, $pending_id, 'commission-status' );
								
								update_post_meta( $commission_id, 'commission_details', json_encode($data,JSON_PRETTY_PRINT));	

								update_post_meta( $commission_id, 'commission_amount', $amount);
							
								// set commission counter
							
								$this->set_affiliate_counter($affiliate->ID, 'commission', $id . '_' . $amount);
								
								// send notification
								
								$company	= ucfirst(get_bloginfo('name'));
								
								$dashboard_url = $this->parent->urls->editor . '?affiliate';
								
								$title 		= 'Commission of ' . $currency . $amount . ' from ' . $company;
								
								$content 	= '';
								$content 	.= 'Congratulations ' . ucfirst($affiliate->user_nicename) . '! You have just received a commission of ' . $currency . $amount . '. You can view the full details of this commission in your dashboard:' . PHP_EOL . PHP_EOL;

								$content 	.= '	' . $dashboard_url . '#overview' . PHP_EOL . PHP_EOL;

								$content 	.= 'We\'ll be here to help you with any step along the way. You can find answers to most questions and get in touch with us at '. $dashboard_url . '#rules' . PHP_EOL . PHP_EOL;

								$content 	.= 'Yours,' . PHP_EOL;
								$content 	.= 'The ' . $company . ' team' . PHP_EOL . PHP_EOL;

								$content 	.= '==== Commission Summary ====' . PHP_EOL . PHP_EOL;

								$content 	.= 'Plan purchased: ' . $data['name'] . PHP_EOL;
								$content 	.= 'Total amount: ' . $currency . $total . PHP_EOL;
								$content 	.= 'Pourcentage: ' . $pourcent . '%' . PHP_EOL;
								$content 	.= 'Your commission: ' . $currency . $amount . PHP_EOL;
								$content 	.= 'Customer email: ' . $data['subscriber'] . PHP_EOL;
								
								wp_mail($affiliate->user_email, $title, $content);
								
								if( $this->parent->settings->options->emailSupport != $affiliate->user_email ){
									
									wp_mail($this->parent->settings->options->emailSupport, $title, $content);
								}
							}
						}
						else{
							
							//echo 'Error getting pending term...';
							//exit;
						}
					}
				}
			}
		}
	}
	
	public function get_affiliate_balance($user_id, $currency='$'){
		
		$balance = 0;
		
		$q = get_posts(array(
		
			'author'      	=> $user_id,
			'post_type'   	=> 'affiliate-commission',
			'post_status' 	=> 'publish',
			'numberposts' 	=> -1,
			'tax_query'  	=> array(
			
				array(
				
					'taxonomy' 	=> 'commission-status',
					'field' 	=> 'slug',
					'terms' 	=> 'pending',	
				),
			),
		));
		
		if( !empty($q) ){
			
			foreach( $q as $commission ){
				
				$amount = get_post_meta( $commission->ID, 'commission_amount', true );
				
				$balance += floatval($amount);
			}
		}
		
		return $currency . number_format($balance, 2, '.', '');
	}
	
	public function ref_user_register( $user_id ){
				
		if( is_numeric( $this->parent->request->ref_id ) ){
			
			// get affiliate data
			
			$affiliate = get_userdata( $this->parent->request->ref_id );

			if($affiliate){
				
				// get referral info
				
				$referral = get_userdata($user_id);
			
				if($referral){
					
					//set marketing channel
					
					$this->parent->update_user_channel($user_id,'Friend Recommendation');
			
					//assign affiliate to referral
					
					update_user_meta( $referral->ID, $this->parent->_base . 'referredBy', [ $affiliate->ID => $affiliate->user_login ] );
					
					//assign referral to affiliate
					
					$referrals = get_user_meta($affiliate->ID,$this->parent->_base . 'referrals', true);
					
					if( !is_array($referrals) ) {
						
						$referrals = [];
					}
					else{
						
						foreach( $referrals as $key => $val){
							
							if(!is_string($val)){
								
								unset($referrals[$key]);
							}
						}
					}

					$referrals[$referral->ID] = $referral->user_login;
					
					update_user_meta( $affiliate->ID, $this->parent->_base . 'referrals', $referrals );

					//set referral counter
					
					$this->set_affiliate_counter($affiliate->ID, 'referrals', $referral->ID );
					
					//add referral stars
					
					/** 
						we dont use do_action here
						because all hooks are attached to the current id
						and we want the referral id to be credited
					**/
					
					$this->parent->stars->add_stars( $affiliate->ID, $this->parent->_base . 'ltple_referred_registration_stars' );
				
					// send notification
					
					$company	= ucfirst(get_bloginfo('name'));
					
					$dashboard_url = $this->parent->urls->editor . '?affiliate';
					
					$title 		= 'New referral user registration on ' . $company;
					
					$content 	= '';
					$content 	.= 'Congratulations ' . ucfirst($affiliate->user_nicename) . '! A new user registration has been made using your affiliate ID. You can view the full details of your affiliate program in your dashboard:' . PHP_EOL . PHP_EOL;

					$content 	.= '	' . $dashboard_url . '#overview' . PHP_EOL . PHP_EOL;

					$content 	.= 'We\'ll be here to help you with any step along the way. You can find answers to most questions and get in touch with us at '. $dashboard_url . '#rules' . PHP_EOL . PHP_EOL;

					$content 	.= 'Yours,' . PHP_EOL;
					$content 	.= 'The ' . $company . ' team' . PHP_EOL . PHP_EOL;

					$content 	.= '==== Registration Summary ====' . PHP_EOL . PHP_EOL;

					$content 	.= 'Referral name: ' . ucfirst($referral->user_nicename) . PHP_EOL;
					$content 	.= 'Referral email: ' . $referral->user_email . PHP_EOL;
					
					wp_mail($affiliate->user_email, $title, $content);
					
					if( $this->parent->settings->options->emailSupport != $affiliate->user_email ){
						
						wp_mail($this->parent->settings->options->emailSupport, $title, $content);
					}
				}
			}
		}
	}
	
	public function get_affiliate_overview( $counter, $sum = false, $pre = '', $app = '' ){

		$z 	= date('z'); //day of the year
		$w 	= date('W'); //week of the year
		$m 	= date('m'); //month of the year
		$y 	= date('Y'); //year		
			
		echo'<table class="table table-striped table-hover">';
		
			echo'<tbody>';
				
				// today
				
				echo'<tr style="font-size:18px;font-weight:bold;">';
				
					echo'<td>';
						echo'Today';
					echo'</td>';

					echo'<td>';
					
						if($sum){
							
							$today = 0;
							
							foreach( $counter['today'][$y][$z] as $value){
								
								$today += $value;
							}

							echo $pre . number_format($today, 2, '.', '').$app;
						}
						else{
							
							// count
							
							echo $pre.count($counter['today'][$y][$z]).$app;
						}
						
					echo'</td>';													
				
				echo'</tr>';
				
				// week
				
				echo'<tr>';
				
					echo'<td>';
						echo'Week';
					echo'</td>';

					echo'<td>';
						
						if($sum){
							
							echo $pre.number_format($counter['week'][$y][$w], 2, '.', '').$app;
						}
						else{
							
							echo $pre.$counter['week'][$y][$w].$app;
						}
						
					echo'</td>';													
				
				echo'</tr>';
				
				// month
				
				echo'<tr>';
				
					echo'<td>';
						echo'Month';
					echo'</td>';

					echo'<td>';
					
						if($sum){
							
							echo $pre.number_format($counter['month'][$y][$m], 2, '.', '').$app;
						}
						else{
							
							echo $pre.$counter['month'][$y][$m].$app;
						}
					echo'</td>';													
				
				echo'</tr>';
				
				// Total
				
				echo'<tr>';
				
					echo'<td>';
						echo'All Time';
					echo'</td>';

					echo'<td>';
					
						if($sum){
							
							echo $pre.number_format($counter['total'], 2, '.', '').$app;
						}
						else{
							
							echo $pre.$counter['total'].$app;
						}
						
					echo'</td>';													
				
				echo'</tr>';
				
			echo'</tbody>';
		
		echo'</table>';		
	}	
	
	public function get_user_programs( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			$user_programs = $this->parent->editedUser->programs;
			
			if(!is_array($user_programs)){
				
				$user_programs = [];
			}
			
			if( user_can( $user->ID, 'administrator' ) ){
				
				echo '<div class="postbox" style="min-height:45px;">';
					
					echo '<h3 style="float:left;margin:10px;width:300px;display: inline-block;">' . __( 'Stripe Account', 'live-template-editor-client' ) . '</h3>';
					
					echo '<iframe src="' . $this->parent->server->url . '/endpoint/?connect=' . $this->parent->ltple_encrypt_uri($user->user_email) . '" style="width:250px;height:50px;overflow:hidden;"></iframe>';				
						
				echo'</div>';
			}
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Programs', 'live-template-editor-client' ) . '</h3>';
				
				foreach($this->list as $slug => $name){
					
					echo '<input type="checkbox" name="' . $this->parent->_base . 'user-programs[]" id="user-program-'.$slug.'" value="'.$slug.'"'.( in_array( $slug, $user_programs ) ? ' checked="checked"' : '' ).'>';
					echo '<label for="user-program-'.$slug.'">'.$name.'</label>';
					echo '<br>';
				}				
					
			echo'</div>';
		}	
	}
	
	public function get_user_referrals( $user ) {
		
		if( current_user_can( 'administrator' ) ){
			
			echo '<div class="postbox" style="min-height:45px;">';
				
				//echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Referrals', 'live-template-editor-client' ) . '</h3>';
				
				echo '<table class="widefat fixed striped" style="border:none;">';
					
					echo '<thead>';
					
						echo'<tr>';
						
							echo'<td>';
								echo'<h3 style="margin:0;">Clicks</h3>';
							echo'</td>';
						
							echo'<td>';
								echo'<h3 style="margin:0;">Referrals</h3>';
							echo'</td>';
						
							echo'<td>';
								echo'<h3 style="margin:0;">Commission</h3>';
							echo'</td>';
						
						echo'</tr>';
					
					echo '</thead>';
					
					echo '<tbody>';
					
						echo'<tr>';
						
							echo'<td>';
							
								$this->get_affiliate_overview($this->parent->editedUser->affiliate_clicks);						
								
							echo'</td>';	
							
							echo'<td>';
							
								$this->get_affiliate_overview($this->parent->editedUser->affiliate_referrals);							
								
							echo'</td>';									
							
							echo'<td>';

								$this->get_affiliate_overview($this->parent->editedUser->affiliate_commission,true,'$');																	
								
							echo'</td>';

						echo'</tr>';
						
						echo'<tr>';
						
							echo'<td>';
								echo'<i>';
									echo'* daily unique IPs';	
								echo'</i>';
							echo'</td>';
						
							echo'<td>';
								echo'<i>';
									echo'* new user registrations';	
								echo'</i>';
							echo'</td>';
						
							echo'<td>';
								echo'<i>';
									echo'* new plan subscriptions';	
								echo'</i>';
							echo'</td>';
						
						echo'</tr>';
						
					echo '</tbody>';

				echo '</table>';
					
			echo'</div>';
			
			if(!empty($this->parent->editedUser->referrals)){
				
				echo '<div class="postbox" style="min-height:45px;">';
					
					echo '<h3 style="margin:10px;width:300px;display:inline-block;">' . __( 'All Referrals', 'live-template-editor-client' ) . '</h3>';
							
					echo '<table class="widefat fixed striped" style="border:none;">';
							
						echo '<tbody>';					
						
							$i=0;
							
							foreach($this->parent->editedUser->referrals as $id => $name){
								
								if(is_string($name)){
									
									if($i==0){
										
										echo'<tr>';
									}
									
									echo'<td>';
									
										echo'<a href="'.admin_url( 'user-edit.php' ).'?user_id='.$id.'">'.$name.'</a>';
									
									echo'</td>';

									if( $i < 4 ){
										
										++$i;
									}
									else{
										
										$i=0;
										
										echo'</tr>';
									}	
								}
							}
						
						echo '</tbody>';

					echo '</table>';
					
				echo'</div>';
			}
		}	
	}
	
	public function save_user_programs( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-programs'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-programs', json_encode($_POST[$this->parent->_base . 'user-programs']));	

			if( in_array( 'affiliate', $_POST[$this->parent->_base . 'user-programs']) ){
				
				$this->parent->email->schedule_trigger( 'affiliate-approved',  $user_id);
			}
		}
	}	
}  
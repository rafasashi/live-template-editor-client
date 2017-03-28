<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Programs {
	
	var $parent;
	var $list;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->list = array(
		
			'affiliate'	=>'Affiliate',
			//'partner'	=>'Partner',
		);
		
		if(isset($_GET['affiliate'])){
			
			$this->banners = get_option($this->parent->_base . 'affiliate_banners');

		}
		
		add_action( 'user_register', 	array( $this, 'ref_user_register' ) );
		
		add_action( 'ltple_loaded', array( $this, 'init_affiliate' ));
			
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
	
	public function init_affiliate(){
	
		if( !empty($this->parent->request->ref_id) && !$this->parent->user->loggedin ){
				
			$this->set_affiliate_counter($this->parent->request->ref_id, 'clicks', $this->parent->request->ip );
		
			do_action( 'ltple_referred_click' );
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
		
		if( empty($counter) || !isset($counter['today'][$y][$z]) || !in_array($id,$counter['today'][$y][$z]) ){
		
			if($type == 'commission'){
				
				// set today
				
				$counter['today'][$y][$z][] = $id;
				
				// set week

				$counter['week'][$y][$w] += $id;
				
				// set month
				
				$counter['month'][$y][$m] += $id;
				
				// set year
				
				$counter['year'][$y] += $id;
				
				// set total
				
				$counter['total'] += $id;				
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
	
	public function set_affiliate_commission($user_id, $amount=0, $currency='$'){
		
		$amount = floatval($amount);

		if( $amount > 0 ){
			
			// handle affiliate commission

			$affiliate = get_user_meta($user_id, $this->parent->_base . 'referredBy', true);
		
			if(!empty($affiliate)){
				
				$affiliate_id = key($affiliate);
				
				$commission_pourcent = 25;
				
				$commission =  $amount * ( $commission_pourcent / 100 );
				
				$this->set_affiliate_counter($affiliate_id, 'commission', $commission);
			}
		}
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
							
							$sum = 0;
							
							foreach( $counter['today'][$y][$z] as $value){
								
								$sum += $value;
							}

							echo $pre . number_format($sum, 2, '.', '').$app;
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
					
					foreach($this->parent->editedUser->referrals as $id => $name){
						
						if(is_string($name)){
							
							echo'<a href="'.admin_url( 'user-edit.php' ).'?user_id='.$id.'">'.$name.'</a>';
							echo'<br>';
						}
					}
					
				echo'</div>';
			}
		}	
	}
	
	public function save_user_programs( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-programs'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-programs', json_encode($_POST[$this->parent->_base . 'user-programs']));			
		}
	}	
}  
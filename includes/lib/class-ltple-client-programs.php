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
		
		add_action( 'init', array( $this, 'affiliate_init' ));	
		
		// add program field
		
		add_action( 'show_user_profile', array( $this, 'get_user_programs' ) );
		add_action( 'edit_user_profile', array( $this, 'get_user_programs' ) );
		
		// save user programs
		
		add_action( 'personal_options_update', array( $this, 'save_user_programs' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_programs' ) );
	}
	
	public function affiliate_init(){
	
		if( !empty($this->parent->request->ref_id) ){
				
			$this->set_affiliate_counter($this->parent->request->ref_id, 'clicks', $this->parent->request->ip );
		}
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
			
			// update counter
			
			update_user_meta( $user_id, $this->parent->_base . 'affiliate_'.$type, $counter);
		}
		
		return $counter;
	}
	
	public function get_affiliate_overview( $counter, $sum = false, $pre = '', $app = '' ){

		$z 	= date('z'); //day of the year
		$w 	= date('W'); //week of the year
		$m 	= date('m'); //month of the year
		$y 	= date('Y'); //year		
			
		echo'<table class="table table-striped table-hover">';
		
			echo'<tbody>';
				
				// today
				
				echo'<tr>';
				
					echo'<td>';
						echo'Today';
					echo'</td>';

					echo'<td>';
					
						if($sum){
							
							$sum = 0;
							
							foreach( $counter['today'][$y][$z] as $value){
								
								$sum += $value;
							}
							
							echo $pre.$sum.$app;
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
						echo $pre.$counter['week'][$y][$w].$app;
					echo'</td>';													
				
				echo'</tr>';
				
				// month
				
				echo'<tr>';
				
					echo'<td>';
						echo'Month';
					echo'</td>';

					echo'<td>';
						echo $pre.$counter['month'][$y][$m].$app;
					echo'</td>';													
				
				echo'</tr>';
				
				// Total
				
				echo'<tr>';
				
					echo'<td>';
						echo'All Time';
					echo'</td>';

					echo'<td>';
						echo $pre.$counter['total'].$app;
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
	
	public function save_user_programs( $user_id ) {
		
		if(isset($_POST[$this->parent->_base . 'user-programs'])){
			
			update_user_meta( $user_id, $this->parent->_base . 'user-programs', json_encode($_POST[$this->parent->_base . 'user-programs']));			
		}
	}	
}  